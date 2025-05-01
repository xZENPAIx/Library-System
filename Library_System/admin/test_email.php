<?php
require_once __DIR__ . '/../includes/auth_functions.php';
require_once __DIR__ . '/../includes/gmail_api.php';
require_once __DIR__ . '/../includes/email_functions.php';
require_role(['super_admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/admin/settings.php");
    exit();
}

$email = filter_var($_POST['test_email'] ?? '', FILTER_VALIDATE_EMAIL);
if (!$email) {
    $_SESSION['error'] = "Invalid email address";
    header("Location: " . BASE_URL . "/admin/settings.php");
    exit();
}

try {
    $subject = "Library System Test Email";
    $message = "This is a test email from the Library System.";
    
    $gmail = new GmailAPI();
    $gmail->sendEmail($email, $subject, $message);
    
    $_SESSION['success'] = "Test email sent successfully to $email";
} catch (Exception $e) {
    $_SESSION['error'] = "Failed to send test email: " . $e->getMessage();
    error_log("Test email error: " . $e->getMessage());
}

header("Location: " . BASE_URL . "/admin/settings.php");
exit();
?>
