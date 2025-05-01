<?php
require_once __DIR__ . '/../includes/auth_functions.php';
require_once __DIR__ . '/../includes/db_functions.php';
require_once __DIR__ . '/../includes/email_functions.php';
require_role(['librarian', 'super_admin']);

// Define get_user_by_id function if not already defined
if (!function_exists('get_user_by_id')) {
    function get_user_by_id($account_id) {
        global $conn;
        
        $stmt = $conn->prepare("
            SELECT l.*, 
                   COALESCE(s.name, a.Name) AS name,
                   COALESCE(s.email, a.Email) AS email,
                   l.user_level
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
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid user ID";
    header("Location: " . BASE_URL . "/dashboard/" . $_SESSION['user_level'] . "/");
    exit();
}

$account_id = (int)$_GET['id'];

// Approve the user
if (approve_user($account_id)) {
    // Get user details
    $user = get_user_by_id($account_id);
    
    if ($user) {
        // Send approval email if applicable
        if ($user['user_level'] === 'student' && !empty($user['email'])) {
            try {
                send_account_approved_email(
                    $user['email'],
                    $user['name'] ?? 'User',
                    $user['username']
                );
                $_SESSION['success'] = "User approved and notified successfully";
            } catch (Exception $e) {
                error_log("Email error: " . $e->getMessage());
                $_SESSION['warning'] = "User approved but email notification failed";
            }
        } else {
            $_SESSION['success'] = "User approved successfully";
        }
    }
} else {
    $_SESSION['error'] = "Failed to approve user";
}

header("Location: " . BASE_URL . "/dashboard/" . $_SESSION['user_level'] . "/manage_users.php");
exit();
?>