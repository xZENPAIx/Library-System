<?php
require_once __DIR__ . '/../includes/auth_functions.php';
require_once __DIR__ . '/../includes/db_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_level = sanitize_input($_POST['user_level']);
    $username = sanitize_input($_POST['username']);
    $password = sanitize_input($_POST['password']);
    $confirm_password = sanitize_input($_POST['confirm_password']);
    
    // Validate passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match";
        header("Location: signup.php");
        exit();
    }
    
    // Validate password length
    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long";
        header("Location: signup.php");
        exit();
    }
    
    // Check if username already exists
    $existing_user = get_user_by_username($username);
    if ($existing_user) {
        $_SESSION['error'] = "Username already exists";
        header("Location: signup.php");
        exit();
    }
    
    // Validate ID based on user level
    if ($user_level === 'student') {
        $std_id = sanitize_input($_POST['std_id']);
        $admin_id = null;
        
        if (!student_id_exists($std_id)) {
            $_SESSION['signup_data'] = [
                'user_level' => $user_level,
                'username' => $username,
                'password' => $password,
                'std_id' => $std_id
            ];
            header("Location: redirect_to_student_form.php");
            exit();
        }
    } else {
        $admin_id = sanitize_input($_POST['admin_id']);
        $std_id = null;
        
        if (!admin_id_exists($admin_id)) {
            $_SESSION['signup_data'] = [
                'user_level' => $user_level,
                'username' => $username,
                'password' => $password,
                'admin_id' => $admin_id
            ];
            header("Location: redirect_to_admin_form.php");
            exit();
        }
    }
    
    // Create the user account
    if (create_user($username, $password, $user_level, $std_id, $admin_id)) {
        if ($user_level === 'student') {
            $_SESSION['success'] = "Registration successful! You can now login.";
            header("Location: login.php");
        } else {
            $_SESSION['success'] = "Registration submitted! Please wait for admin approval.";
            header("Location: login.php");
        }
        exit();
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: signup.php");
        exit();
    }
} else {
    header("Location: signup.php");
    exit();
}
?>