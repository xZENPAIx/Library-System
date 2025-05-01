<?php
require_once __DIR__ . '/../includes/auth_functions.php';
require_once __DIR__ . '/../includes/gmail_api.php';
require_role(['super_admin']);

try {
    $gmail = new GmailAPI();
    $_SESSION['oauth2state'] = bin2hex(random_bytes(16));
    $authUrl = $gmail->createAuthUrl();
    header("Location: " . $authUrl);
    exit();
} catch (Exception $e) {
    $_SESSION['error'] = "Failed to initiate Gmail connection: " . $e->getMessage();
    error_log("Gmail connection error: " . $e->getMessage());
    header("Location: " . BASE_URL . "/admin/settings.php");
    exit();
}
?>