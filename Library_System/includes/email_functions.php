<?php
require_once __DIR__ . '/gmail_api.php';
require_once __DIR__ . '/../config/config.php'; // Contains BASE_URL and other constants

class EmailFunctions {
    private $gmail;
    
    public function __construct() {
        $this->gmail = new GmailAPI();
    }

    /**
     * Send account approved notification
     */
    public function sendAccountApprovedEmail($to, $name, $username) {
        try {
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException("Invalid email address: " . $to);
            }

            $subject = "Your Library Account Has Been Approved";
            $message = $this->buildEmailTemplate("account_approved", [
                'name' => htmlspecialchars($name),
                'username' => htmlspecialchars($username),
                'login_url' => BASE_URL . "/auth/login.php"
            ]);

            $result = $this->gmail->sendEmail($to, $subject, $message);
            
            if (!$result) {
                throw new Exception("Gmail API returned false");
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Approval email failed to {$to}: " . $e->getMessage());
            throw new Exception("Failed to send approval email");
        }
    }

    /**
     * Notify admins of new registration (improved version)
     */
    public function notifyAdminNewRegistration($studentData) {
        try {
            // Validate required student data
            $requiredFields = ['std_id', 'name', 'email', 'program'];
            foreach ($requiredFields as $field) {
                if (empty($studentData[$field])) {
                    throw new InvalidArgumentException("Missing student data field: {$field}");
                }
            }

            $admins = $this->getAllAdmins();
            if (empty($admins)) {
                error_log("No admin accounts available for notifications");
                return false;
            }

            $subject = "New Student Registration - Approval Required";
            $successCount = 0;
            $failedRecipients = [];

            foreach ($admins as $admin) {
                try {
                    $message = $this->buildEmailTemplate("new_registration", [
                        'student_id' => htmlspecialchars($studentData['std_id']),
                        'student_name' => htmlspecialchars($studentData['name']),
                        'student_email' => htmlspecialchars($studentData['email']),
                        'program' => htmlspecialchars($studentData['program']),
                        'approval_url' => BASE_URL . "/admin/approve_users.php",
                        'admin_name' => htmlspecialchars($admin['Name'] ?? 'Admin')
                    ]);

                    if ($this->gmail->sendEmail($admin['Email'], $subject, $message)) {
                        $successCount++;
                    } else {
                        $failedRecipients[] = $admin['Email'];
                    }
                } catch (Exception $e) {
                    error_log("Failed to notify admin {$admin['Email']}: " . $e->getMessage());
                    $failedRecipients[] = $admin['Email'];
                }
            }

            if (!empty($failedRecipients)) {
                error_log("Failed to notify these admins: " . implode(", ", $failedRecipients));
            }

            return $successCount > 0;
        } catch (Exception $e) {
            error_log("Admin notification system error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all admin users who should receive notifications
     */
    private function getAllAdmins() {
        global $conn;
        
        try {
            $stmt = $conn->prepare("
                SELECT admin_id, Name, Email, User_level 
                FROM admin_tbl 
                WHERE User_level IN ('Super Admin', 'Librarian') 
                AND Email IS NOT NULL
                AND receive_notifications = 1
            ");
            
            if (!$stmt) {
                throw new Exception("Failed to prepare admin query: " . $conn->error);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Database error fetching admins: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Build email HTML template from file
     */
    private function buildEmailTemplate($template, $data = []) {
        $templatePath = __DIR__ . "/email_templates/{$template}.php";
        
        if (!file_exists($templatePath)) {
            throw new RuntimeException("Email template not found: {$templatePath}");
        }
        
        ob_start();
        extract($data);
        include $templatePath;
        $content = ob_get_clean();
        
        if (empty($content)) {
            throw new RuntimeException("Generated empty email content for template: {$template}");
        }
        
        return $content;
    }
}

// Helper functions for backward compatibility
function notify_admin_new_registration($studentData) {
    static $emailer = null;
    
    if ($emailer === null) {
        $emailer = new EmailFunctions();
    }
    
    try {
        return $emailer->notifyAdminNewRegistration($studentData);
    } catch (Exception $e) {
        error_log("Notification helper error: " . $e->getMessage());
        return false;
    }
}

function send_account_approved_email($to, $name, $username) {
    static $emailer = null;
    
    if ($emailer === null) {
        $emailer = new EmailFunctions();
    }
    
    try {
        return $emailer->sendAccountApprovedEmail($to, $name, $username);
    } catch (Exception $e) {
        error_log("Approval email helper error: " . $e->getMessage());
        return false;
    }
}
?>