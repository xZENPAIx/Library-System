<?php
require_once __DIR__ . '/../includes/auth_functions.php';
require_once __DIR__ . '/../includes/db_functions.php';
require_role(['librarian', 'super_admin']);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid user ID";
    header("Location: " . BASE_URL . "/dashboard/" . $_SESSION['user_level'] . "/");
    exit();
}

$account_id = (int)$_GET['id'];

// Approve the user
if (approve_user($account_id)) {
    // Get user details to send email
    $stmt = $conn->prepare("
        SELECT l.username, COALESCE(s.email, a.Email) as email
        FROM login_tbl l
        LEFT JOIN std_tbl s ON l.std_id = s.std_id
        LEFT JOIN admin_tbl a ON l.admin_id = a.admin_id
        WHERE l.Account_ID = ?
    ");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    $_SESSION['success'] = "User approved successfully";
} else {
    $_SESSION['error'] = "Failed to approve user";
}

header("Location: " . BASE_URL . "/dashboard/" . $_SESSION['user_level'] . "/manage_users.php");
exit();
?>