<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/auth_functions.php';
require_once __DIR__ . '/../config/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_level = sanitize_input($_POST['user_level']);
    $username = sanitize_input($_POST['username']);
    $password = sanitize_input($_POST['password']);
    
    // Special case for super admin login
    if ($user_level === 'super_admin') {
        $user = verify_super_admin($username, $password);
        
        if ($user) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['Account_ID'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_level'] = $user['user_level'];
            $_SESSION['last_activity'] = time();
            
            header("Location: " . BASE_URL . "/dashboard/super_admin/");
            exit();
        }
    }
    
    // Regular login flow for other user types
    $user = login_user($username, $password, $user_level);
    
    if ($user && $user !== 'pending_approval') {
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
    } elseif ($user === 'pending_approval') {
        $_SESSION['error'] = "Your account is pending approval by the administrator.";
    } else {
        $_SESSION['error'] = "Invalid username or password for selected user type";
    }
}

header("Location: " . BASE_URL . "/auth/login.php");
exit();
?>