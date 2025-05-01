<?php
require_once __DIR__ . '/../includes/auth_functions.php';
require_once __DIR__ . '/../includes/db_functions.php';
require_once __DIR__ . '/../includes/email_functions.php';
require_role(['librarian', 'super_admin']);

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