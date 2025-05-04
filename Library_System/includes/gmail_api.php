<?php
class GmailAPI {
    private $accessToken;
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    
    public function __construct() {
        $this->clientId = defined('GMAIL_CLIENT_ID') ? GMAIL_CLIENT_ID : '';
        $this->clientSecret = defined('GMAIL_CLIENT_SECRET') ? GMAIL_CLIENT_SECRET : '';
        $this->redirectUri = defined('GMAIL_REDIRECT_URI') ? GMAIL_REDIRECT_URI : (BASE_URL . '/auth/gmail_callback.php');
        
        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new Exception("Gmail API credentials not configured");
        }
    }

    public function createAuthUrl() {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/gmail.send',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => bin2hex(random_bytes(16))
        ];
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    public function fetchAccessTokenWithAuthCode($code) {
        $url = 'https://oauth2.googleapis.com/token';
        $postData = [
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json']
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("Failed to fetch access token: HTTP $httpCode");
        }
        
        $data = json_decode($response, true);
        if (isset($data['access_token'])) {
            $this->accessToken = $data['access_token'];
            return $data;
        }
        
        throw new Exception("Invalid token response: " . $response);
    }

    public function sendEmail($to, $subject, $message) {
        try {
            if (!$this->accessToken && !$this->refreshAccessToken()) {
                throw new Exception("No valid access token available");
            }

            $boundary = uniqid();
            $rawMessage = $this->createRawMessage($to, $subject, $message, $boundary);
            
            $response = $this->makeGmailRequest($rawMessage);
            
            if ($response['httpCode'] !== 200) {
                error_log("Gmail API Error - Status: {$response['httpCode']}, Response: {$response['body']}");
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    private function refreshAccessToken() {
        if (!defined('GMAIL_REFRESH_TOKEN') || empty(GMAIL_REFRESH_TOKEN)) {
            return false;
        }
        
        $url = 'https://oauth2.googleapis.com/token';
        $postData = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => GMAIL_REFRESH_TOKEN,
            'grant_type' => 'refresh_token'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json']
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $this->accessToken = $data['access_token'] ?? null;
            return $this->accessToken !== null;
        }
        
        error_log("Token refresh failed. HTTP Code: $httpCode, Response: $response");
        return false;
    }
    
    private function makeGmailRequest($rawMessage) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://gmail.googleapis.com/gmail/v1/users/me/messages/send',
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode(['raw' => $rawMessage]),
            CURLOPT_RETURNTRANSFER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ['httpCode' => $httpCode, 'body' => $response];
    }
    
    private function createRawMessage($to, $subject, $message, $boundary) {
        $fromEmail = defined('GMAIL_USER') ? GMAIL_USER : EMAIL_FROM;
        $headers = [
            "From: " . (defined('EMAIL_FROM_NAME') ? EMAIL_FROM_NAME . " " : "") . "<$fromEmail>",
            "To: $to",
            "Subject: $subject",
            "MIME-Version: 1.0",
            "Content-Type: multipart/alternative; boundary=\"$boundary\""
        ];
        
        $textVersion = strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $message));
        
        $body = "--$boundary\r\n" .
                "Content-Type: text/plain; charset=UTF-8\r\n\r\n" .
                $textVersion . "\r\n" .
                "--$boundary\r\n" .
                "Content-Type: text/html; charset=UTF-8\r\n\r\n" .
                $message . "\r\n" .
                "--$boundary--";
        
        return base64_encode(implode("\r\n", $headers) . "\r\n\r\n" . $body);
    }
}
?>