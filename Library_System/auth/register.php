<?php
require_once __DIR__ . '/../includes/auth_functions.php';
require_once __DIR__ . '/../includes/db_functions.php';
require_once __DIR__ . '/../includes/email_functions.php';

// Initialize all variables
$user_level = '';
$username = '';
$password = '';
$confirm_password = '';
$std_id = null;
$admin_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (!isset($_POST['user_level'], $_POST['username'], $_POST['password'], $_POST['confirm_password'])) {
        $_SESSION['error'] = "Missing required fields";
        header("Location: signup.php");
        exit();
    }

    // Sanitize inputs
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

    // Handle student registration
    if ($user_level === 'student') {
        if (!isset($_POST['std_id'])) {
            $_SESSION['error'] = "Student ID is required";
            header("Location: signup.php");
            exit();
        }

        $std_id = sanitize_input($_POST['std_id']);
        
        if (!student_id_exists($std_id)) {
            // Store registration data in session
            $_SESSION['pending_student_registration'] = [
                'username' => $username,
                'password' => $password,
                'std_id' => $std_id,
                'user_level' => $user_level
            ];
            
            // Redirect to complete student info form
            header("Location: " . BASE_URL . "/auth/complete_student_info.php");
            exit();
        }
        
        // If student ID exists, proceed with registration
        if (create_user($username, $password, $user_level, $std_id, null)) {
            // Notify admin with complete student data
            notify_admin_new_registration([
                'username' => $username,
                'user_level' => $user_level,
                'std_id' => $std_id,
                'name' => '', // Will be updated in complete_student_info.php
                'email' => '', // Will be updated in complete_student_info.php
                'program' => '' // Will be updated in complete_student_info.php
            ]);
            
            $_SESSION['success'] = "Registration successful! Please wait for admin approval.";
            header("Location: login.php");
            exit();
        }
    } 
    // Handle admin registration
    else {
        if (!isset($_POST['admin_id'])) {
            $_SESSION['error'] = "Admin ID is required";
            header("Location: signup.php");
            exit();
        }

        $admin_id = sanitize_input($_POST['admin_id']);
        
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
    
    // Final user creation attempt
    if (create_user($username, $password, $user_level, $std_id, $admin_id)) {
        $_SESSION['success'] = $user_level === 'student' 
            ? "Registration successful! You can now login." 
            : "Registration submitted! Please wait for admin approval.";
        header("Location: login.php");
        exit();
    }

    $_SESSION['error'] = "Registration failed. Please try again.";
    header("Location: signup.php");
    exit();
}

// If not POST request, redirect to signup
header("Location: signup.php");
exit();
?>