<?php
require_once __DIR__ . '/../includes/auth_functions.php';
require_once __DIR__ . '/../includes/db_functions.php';
require_once __DIR__ . '/../includes/email_functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['pending_student_registration'])) {
    header("Location: signup.php");
    exit();
}

try {
    // Get registration data from session
    $reg_data = $_SESSION['pending_student_registration'];
    
    // Process form data
    $std_id = sanitize_input($_POST['std_id']);
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $contact = sanitize_input($_POST['contact']);
    $program = sanitize_input($_POST['program']);
    $year = sanitize_input($_POST['year']);
    $section = sanitize_input($_POST['section']);
    $province = sanitize_input($_POST['province']);
    $municipality = sanitize_input($_POST['municipality']);
    $barangay = sanitize_input($_POST['barangay']);
    
    // Start transaction
    $conn->begin_transaction();
    
    // 1. Create address record
    $stmt = $conn->prepare("INSERT INTO address_tbl (brgy, municipality, province) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $barangay, $municipality, $province);
    $stmt->execute();
    $address_id = $conn->insert_id;
    
    // 2. Create program record
    $stmt = $conn->prepare("INSERT INTO program_tbl (program_name, year_level, section) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $program, $year, $section);
    $stmt->execute();
    $program_id = $conn->insert_id;
    
    // 3. Create student record
    $stmt = $conn->prepare("INSERT INTO std_tbl (std_id, name, program_id, address_id, email, contact) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiiss", $std_id, $name, $program_id, $address_id, $email, $contact);
    $stmt->execute();
    
    // 4. Create login account
    if (create_user($reg_data['username'], $reg_data['password'], $reg_data['user_level'], $std_id, null)) {
        
        // Improved admin notification
        $notification_sent = notify_admin_new_registration([
            'student_id' => $std_id,
            'name' => $name,
            'email' => $email,
            'program' => $program,
            'username' => $reg_data['username']
        ]);
        
        if (!$notification_sent) {
            error_log("Failed to send admin notification for student: $std_id");
            // Continue anyway since this isn't critical
        }
        
        $conn->commit();
        unset($_SESSION['pending_student_registration']);
        
        $_SESSION['success'] = "Registration completed successfully!";
        header("Location: login.php");
        exit();
    }
    
    $conn->rollback();
    throw new Exception("Failed to create user account");
    
} catch (Exception $e) {
    if (isset($conn) && method_exists($conn, 'rollback')) {
        $conn->rollback();
    }
    $_SESSION['error'] = "Registration failed: " . $e->getMessage();
    error_log("Registration error: " . $e->getMessage());
    header("Location: complete_student_info.php");
    exit();
}
?>