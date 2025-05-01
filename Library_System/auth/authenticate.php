<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/auth_functions.php';
require_once __DIR__ . '/../config/db_config.php';

// Initialize secure session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => false, // SET TO TRUE IN PRODUCTION WITH HTTPS
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

        // 2. Special handling for super admin
        if ($user_level === 'super_admin') {
            $user = verify_super_admin($username, $password);
            
            if (!$user) {
                throw new Exception("Invalid super admin credentials");
            }
            
            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['user_id'] = $user['Account_ID'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_level'] = $user['user_level'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['last_activity'] = time();
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            
            // Redirect to super admin dashboard
            header("Location: " . BASE_URL . "/dashboard/super_admin/");
            exit();
        }
        
        // 3. Regular user authentication
        $user = login_user($username, $password, $user_level);
        
        if ($user === 'pending_approval') {
            $_SESSION['error'] = "Account pending approval";
            header("Location: " . BASE_URL . "/auth/login.php");
            exit();
        }
        
        if (!$user) {
            throw new Exception("Invalid credentials");
        }
        
        // Set session and redirect
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['Account_ID'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_level'] = $user['user_level'];
        $_SESSION['last_activity'] = time();
        
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

// Fallthrough for non-POST requests
header("Location: " . BASE_URL . "/auth/login.php");
exit();