<?php
require_once __DIR__ . '/gmail_api.php';
require_once __DIR__ . '/../config/db_config.php'; // For BASE_URL

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
                throw new InvalidArgumentException("Invalid email address");
            }

            $subject = "Your Library Account Has Been Approved";
            $message = $this->buildEmailTemplate("account_approved", [
                'name' => htmlspecialchars($name),
                'username' => htmlspecialchars($username),
                'login_url' => BASE_URL . "/auth/login.php"
            ]);

            return $this->gmail->sendEmail($to, $subject, $message);
        } catch (Exception $e) {
            error_log("Approval email failed to {$to}: " . $e->getMessage());
            throw new Exception("Failed to send approval email");
        }
    }

    /**
     * Notify admins of new registration
     */
    public function notifyAdminNewRegistration($studentData) {
        try {
            $admins = $this->getAllAdmins();
            $subject = "New Student Registration - Approval Required";
            $successCount = 0;

            foreach ($admins as $admin) {
                if (($admin['User_level'] === 'Super Admin' || $admin['User_level'] === 'Librarian') && !empty($admin['Email'])) {
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
                    }
                }
            }

            if ($successCount === 0) {
                throw new Exception("No admins were notified");
            }

            return $successCount;
        } catch (Exception $e) {
            error_log("Admin notification failed: " . $e->getMessage());
            throw new Exception("Failed to notify admins");
        }
    }

    /**
     * Get all admin users with notification privileges
     */
    private function getAllAdmins() {
        global $conn;
        
        $stmt = $conn->prepare("
            SELECT admin_id, Name, Email, User_level 
            FROM admin_tbl 
            WHERE User_level IN ('Super Admin', 'Librarian') 
            AND Email IS NOT NULL
            AND receive_notifications = 1
        ");
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) {
            error_log("Failed to fetch admins: " . $conn->error);
            return [];
        }
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Build email HTML template
     */
    private function buildEmailTemplate($template, $data = []) {
        $templatePath = __DIR__ . "/email_templates/{$template}.php";
        
        if (!file_exists($templatePath)) {
            throw new RuntimeException("Email template {$template} not found");
        }
        
        ob_start();
        extract($data);
        include $templatePath;
        return ob_get_clean();
    }
}

// Helper functions for backward compatibility
function notify_admin_new_registration($studentData) {
    $emailer = new EmailFunctions();
    return $emailer->notifyAdminNewRegistration($studentData);
}

function send_account_approved_email($to, $name, $username) {
    $emailer = new EmailFunctions();
    return $emailer->sendAccountApprovedEmail($to, $name, $username);
}
?>