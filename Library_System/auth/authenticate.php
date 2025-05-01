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
        $user_level = $_POST['user_level'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($user_level) || empty($username) || empty($password)) {
            throw new Exception("All fields are required");
        }

        // 2. Authentication Logic
        $user = null;
        
        // Special case for super admin login
        if ($user_level === 'super_admin') {
            $user = verify_super_admin($username, $password);
            
            if (!$user) {
                throw new Exception("Invalid super admin credentials");
            }
            
            // Set session variables
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
        
        // Regular login flow for other user types
        $user = login_user($username, $password, $user_level);
        
        if ($user === 'pending_approval') {
            $_SESSION['error'] = "Your account is pending approval";
            header("Location: " . BASE_URL . "/auth/login.php");
            exit();
        }
        
        if (!$user) {
            throw new Exception("Invalid credentials for selected user type");
        }
        
        // Successful login
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