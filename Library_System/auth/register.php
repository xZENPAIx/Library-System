<?php
require_once __DIR__ . '/../includes/auth_functions.php';
require_once __DIR__ . '/../includes/db_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_level = sanitize_input($_POST['user_level']);
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password']; // Don't sanitize passwords
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match";
        header("Location: signup.php");
        exit();
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long";
        header("Location: signup.php");
        exit();
    }

    // Check if username exists
    if (get_user_by_username($username)) {
        $_SESSION['error'] = "Username already exists";
        header("Location: signup.php");
        exit();
    }

    if ($user_level === 'student') {
        $std_id = sanitize_input($_POST['std_id']);
        
        if (!student_id_exists($std_id)) {
            // Store registration data in session
            $_SESSION['pending_student_registration'] = [
                'username' => $username,
                'password' => $password,
                'std_id' => $std_id,
                'user_level' => $user_level
            ];
            
            // Redirect to student information form
            header("Location: " . BASE_URL . "/auth/complete_student_info.php");
            exit();
        }
        
        // If student ID exists, proceed with normal registration
        if (create_user($username, $password, $user_level, $std_id, null)) {
            notify_admin_new_registration($username, $user_level);
            $_SESSION['success'] = "Registration successful! Please wait for admin approval.";
            header("Location: login.php");
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