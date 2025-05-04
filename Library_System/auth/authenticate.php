<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/auth_functions.php';
require_once __DIR__ . '/../config/db_config.php';

// Initialize secure session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => false,    // Set to true in production with HTTPS
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. Validate required fields
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            throw new Exception("Username and password are required");
        }

        // 2. Authentication Logic
        $user = null;

        // First, try to verify if user is super admin
        $user = verify_super_admin($username, $password);

        if ($user) {
            // Set session variables for super admin
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['Account_ID'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_level'] = $user['user_level'];
            $_SESSION['last_activity'] = time();
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

            header("Location: " . BASE_URL . "/dashboard/super_admin/");
            exit();
        }

        // If not super admin, try regular login flow
        $user = get_user_by_username($username);

        if (!$user || hash_password($password) !== $user['password']) {
            throw new Exception("Invalid username or password");
        }

        if (!$user['is_approved']) {
            $_SESSION['error'] = "Your account is pending approval";
            header("Location: " . BASE_URL . "/auth/login.php");
            exit();
        }

        // Set session variables for regular user
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['Account_ID'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_level'] = $user['user_level'];
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

        $dashboard = match($_SESSION['user_level']) {
            'super_admin' => '/dashboard/super_admin/',
            'librarian' => '/dashboard/librarian/',
            default => '/dashboard/student/'
        };

        header("Location: " . BASE_URL . $dashboard);
        exit();

    } catch (Exception $e) {
        error_log("Login Error: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header("Location: " . BASE_URL . "/auth/login.php");
        exit();
    }
}

// If not POST request
header("Location: " . BASE_URL . "/auth/login.php");
exit();
?>