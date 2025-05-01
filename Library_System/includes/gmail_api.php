<?php
require_once __DIR__ . '/../config/constants.php';

class GmailAPI {
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $tokenFile;
    
    public function __construct() {
        $credentialsPath = __DIR__ . '/../config/gmail_credentials.json';
        if (!file_exists($credentialsPath)) {
            throw new Exception("Gmail credentials file not found at $credentialsPath");
        }

        $credentials = json_decode(file_get_contents($credentialsPath), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON in credentials file");
        }

        $this->clientId = $credentials['web']['client_id'] ?? '';
        $this->clientSecret = $credentials['web']['client_secret'] ?? '';
        $this->redirectUri = $credentials['web']['redirect_uris'][0] ?? '';
        $this->tokenFile = __DIR__ . '/../config/gmail_token.json';

        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new Exception("Invalid Gmail API credentials");
        }
    }

    /**
     * Get Google OAuth2 authentication URL
     */
    public function getAuthUrl() {
        $params = [
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'https://www.googleapis.com/auth/gmail.send',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => bin2hex(random_bytes(16))
        ];
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for tokens
     */
    public function getAccessToken($code) {
        $url = 'https://oauth2.googleapis.com/token';
        $data = [
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code'
        ];

        $response = $this->makeHttpRequest($url, $data);
        
        if (empty($response['access_token'])) {
            throw new Exception("Failed to get access token: " . ($response['error'] ?? 'Unknown error'));
        }

        // Add creation timestamp
        $response['created_at'] = time();
        
        $this->saveToken($response);
        return $response;
    }

    /**
     * Refresh expired access token
     */
    public function refreshToken() {
        $token = $this->loadToken();
        
        if (empty($token['refresh_token'])) {
            throw new Exception("No refresh token available");
        }

        $url = 'https://oauth2.googleapis.com/token';
        $data = [
            'refresh_token' => $token['refresh_token'],
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'refresh_token'
        ];

        $response = $this->makeHttpRequest($url, $data);
        
        if (empty($response['access_token'])) {
            throw new Exception("Failed to refresh token: " . ($response['error'] ?? 'Unknown error'));
        }

        // Update token data
        $token['access_token'] = $response['access_token'];
        $token['created_at'] = time();
        
        $this->saveToken($token);
        return $response;
    }

    /**
     * Send email using Gmail API
     */
    public function sendEmail($to, $subject, $message) {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid recipient email address");
        }

        $token = $this->loadToken();
        
        if ($this->isTokenExpired($token)) {
            $token = $this->refreshToken();
        }

        $rawMessage = $this->createRawMessage($to, $subject, $message);
        
        $url = 'https://gmail.googleapis.com/gmail/v1/users/me/messages/send';
        $headers = [
            'Authorization: Bearer ' . $token['access_token'],
            'Content-Type: application/json'
        ];

        $response = $this->makeHttpRequest($url, ['raw' => $rawMessage], $headers);
        
        if (isset($response['error'])) {
            throw new Exception("Gmail API error: " . $response['error']['message'] ?? 'Unknown error');
        }

        return $response;
    }

    /**
     * Create base64 encoded RFC 2822 email message
     */
    private function createRawMessage($to, $subject, $body) {
        $boundary = uniqid('PHP_EMAIL_');
        $headers = [
            'To' => $to,
            'Subject' => $subject,
            'Content-Type' => 'text/html; charset=UTF-8',
            'MIME-Version' => '1.0',
            'From' => $this->getSystemEmail()
        ];

        $email = '';
        foreach ($headers as $key => $value) {
            $email .= "$key: $value\r\n";
        }
        $email .= "\r\n$body";

        return base64_encode($email);
    }

    private function getSystemEmail() {
        // Extract domain from BASE_URL or use default
        $domain = parse_url(BASE_URL, PHP_URL_HOST) ?? 'library-system.com';
        return "no-reply@$domain";
    }

    private function loadToken() {
        if (!file_exists($this->tokenFile)) {
            throw new Exception("Token file not found. Please authenticate first.");
        }

        $token = json_decode(file_get_contents($this->tokenFile), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid token format");
        }

        return $token;
    }

    private function saveToken($token) {
        $tokenDir = dirname($this->tokenFile);
        if (!file_exists($tokenDir)) {
            mkdir($tokenDir, 0700, true);
        }

        if (file_put_contents($this->tokenFile, json_encode($token), LOCK_EX) === false) {
            throw new Exception("Failed to save token file");
        }

        chmod($this->tokenFile, 0600);
    }

    private function isTokenExpired($token) {
        return !isset($token['created_at']) || 
               (time() - $token['created_at'] > 3500); // 3500 seconds (58 mins)
    }

    /**
     * Make HTTP request using cURL
     */
    private function makeHttpRequest($url, $data, $headers = []) {
        $ch = curl_init();
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array_merge(['Accept: application/json'], $headers),
            CURLOPT_FAILONERROR => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 15
        ];
        
        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("HTTP request failed: $error");
        }
        
        curl_close($ch);
        $decoded = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $error = $decoded['error'] ?? ['message' => "HTTP $httpCode error"];
            throw new Exception("API error: " . $error['message']);
        }
        
        return $decoded;
    }
}