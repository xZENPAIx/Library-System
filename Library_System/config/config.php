<?php
// Check if constants are already defined before declaring them
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(__DIR__));
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', APP_ROOT . '/uploads');
if (!defined('SECRET_KEY')) define('SECRET_KEY', 'vsualibsys_secure_key_@2023!');
if (!defined('PASSWORD_HASH_COST')) define('PASSWORD_HASH_COST', 12);
if (!defined('MIN_PASSWORD_LENGTH')) define('MIN_PASSWORD_LENGTH', 8);
if (!defined('REQUIRE_PASSWORD_UPPERCASE')) define('REQUIRE_PASSWORD_UPPERCASE', true);
if (!defined('REQUIRE_PASSWORD_NUMBER')) define('REQUIRE_PASSWORD_NUMBER', true);
if (!defined('REQUIRE_PASSWORD_SPECIAL_CHAR')) define('REQUIRE_PASSWORD_SPECIAL_CHAR', true);
if (!defined('EMAIL_FROM')) define('EMAIL_FROM', 'ryuutokoyami888@gmail.com');
if (!defined('EMAIL_FROM_NAME')) define('EMAIL_FROM_NAME', 'Library System');
if (!defined('EMAIL_ADMIN')) define('EMAIL_ADMIN', 'ryuutokoyami888@gmail.com');
if (!defined('GMAIL_CLIENT_ID')) define('GMAIL_CLIENT_ID', '1007545920882-qbcarpavflafmpjn8og9bjra5se1vqer.apps.googleusercontent.com');
if (!defined('GMAIL_CLIENT_SECRET')) define('GMAIL_CLIENT_SECRET', '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');
if (!defined('GMAIL_REFRESH_TOKEN')) define('GMAIL_REFRESH_TOKEN', '1//04fBmUyyJklanCgYIARAAGAQSNwF-L9Ir_7MxWy3or1f8XOies0Njk2-pKwiakQeCB9i9pppqb9lhKH0iOw75rbSbnPF4oSTnqjY');
if (!defined('GMAIL_USER')) define('GMAIL_USER', 'ryuutokoyami888@gmail.com');
if (!defined('MAX_LOGIN_ATTEMPTS')) define('MAX_LOGIN_ATTEMPTS', 5);
if (!defined('ACCOUNT_APPROVAL_REQUIRED')) define('ACCOUNT_APPROVAL_REQUIRED', true);
if (!defined('LOGS_DIR')) define('LOGS_DIR', APP_ROOT . '/logs');
if (!defined('APP_VERSION')) define('APP_VERSION', '1.0.0');

// Session configuration - only if session not started
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}

// Error Reporting
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
}

// Timezone
date_default_timezone_set('America/New_York');

// Include the database connection
require_once __DIR__ . '/db_config.php';
?>