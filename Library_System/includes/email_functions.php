<?php
require_once __DIR__ . '/gmail_api.php'; // Contains our GmailAPI class

/**
 * Send account approved notification
 */
function send_account_approved_email($to, $name, $username) {
    try {
        $gmail = new GmailAPI();
        $subject = "Your Library Account Has Been Approved";
        $message = "
            <html>
            <body>
                <p>Dear $name,</p>
                <p>Your library account has been approved!</p>
                <p><strong>Username:</strong> $username</p>
                <p>You can now login at: <a href=\"" . BASE_URL . "/auth/login.php\">" . BASE_URL . "/auth/login.php</a></p>
                <p>Thank you,<br>Library System</p>
            </body>
            </html>
        ";
        
        return $gmail->sendEmail($to, $subject, $message);
    } catch (Exception $e) {
        error_log("Approval email failed: " . $e->getMessage());
        throw new Exception("Failed to send approval email");
    }
}

/**
 * Notify admins of new registration
 */
function notify_admin_new_registration($username, $user_level) {
    try {
        $gmail = new GmailAPI();
        $admins = get_all_admins();
        $subject = "New $user_level Registration - Approval Required";
        $message = "
            <html>
            <body>
                <p>A new $user_level has registered:</p>
                <p><strong>Username:</strong> $username</p>
                <p>Please review and approve at: <a href=\"" . BASE_URL . "/admin/approve_users.php\">" . BASE_URL . "/admin/approve_users.php</a></p>
            </body>
            </html>
        ";
        
        foreach ($admins as $admin) {
            if ($admin['User_level'] === 'Super Admin' && !empty($admin['Email'])) {
                $gmail->sendEmail($admin['Email'], $subject, $message);
            }
        }
    } catch (Exception $e) {
        error_log("Admin notification failed: " . $e->getMessage());
        throw new Exception("Failed to notify admins");
    }
}

/**
 * Get all admin users
 */
function get_all_admins() {
    global $conn;
    $result = $conn->query("SELECT * FROM admin_tbl WHERE User_level IN ('Super Admin', 'Librarian')");
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get user by ID
 */
function get_user_by_id($account_id) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT l.*, COALESCE(s.name, a.Name) as name, COALESCE(s.email, a.Email) as email
        FROM login_tbl l
        LEFT JOIN std_tbl s ON l.std_id = s.std_id
        LEFT JOIN admin_tbl a ON l.admin_id = a.admin_id
        WHERE l.Account_ID = ?
    ");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
?>