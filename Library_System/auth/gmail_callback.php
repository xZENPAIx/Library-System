<?php
require_once __DIR__ . '/../includes/gmail_api.php';
require_once __DIR__ . '/../includes/auth_functions.php';

session_start();

try {
    if (!isset($_GET['code'])) {
        throw new Exception("Authorization code missing");
    }

    if (isset($_GET['state']) && $_GET['state'] !== $_SESSION['oauth2state']) {
        throw new Exception("Invalid state parameter");
    }

    $gmail = new GmailAPI();
    $token = $gmail->fetchAccessTokenWithAuthCode($_GET['code']);
    
    $_SESSION['gmail_connected'] = true;
    $_SESSION['success'] = "Gmail API connected successfully!";
} catch (Exception $e) {
    $_SESSION['error'] = "Gmail connection failed: " . $e->getMessage();
    error_log("Gmail callback error: " . $e->getMessage());
}

header("Location: " . BASE_URL . "/admin/settings.php");
exit();
?>