<?php
require_once __DIR__ . '/../includes/auth_functions.php';

logout();

// Redirect to login page with success message
$_SESSION['success'] = "You have been logged out successfully.";
header("Location: login.php");
exit();
?>