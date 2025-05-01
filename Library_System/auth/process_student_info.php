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
    
    // Create student record first
    $conn->begin_transaction();
    
    // 1. Create address (simplified example)
    $conn->query("INSERT INTO address_tbl (brgy, municipality, province) 
                 VALUES ('Unknown', 'Unknown', 'Unknown')");
    $address_id = $conn->insert_id;
    
    // 2. Create program (simplified example)
    $conn->query("INSERT INTO program_tbl (program_name, year_level, section) 
                 VALUES ('Undecided', '1', 'A')");
    $program_id = $conn->insert_id;
    
    // 3. Create student record
    $stmt = $conn->prepare("INSERT INTO std_tbl 
                           (std_id, name, program_id, address_id, email, contact) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiiss", $std_id, $name, $program_id, $address_id, $email, $contact);
    $stmt->execute();
    
    // 4. Create login account
    if (create_user($reg_data['username'], $reg_data['password'], 
                   $reg_data['user_level'], $std_id, null)) {
        
        // Notify admin
        notify_admin_new_registration($reg_data['username'], $reg_data['user_level']);
        
        $conn->commit();
        unset($_SESSION['pending_student_registration']);
        
        $_SESSION['success'] = "Registration submitted! Please wait for admin approval.";
        header("Location: login.php");
        exit();
    }
    
    $conn->rollback();
    throw new Exception("Registration failed");
    
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Error completing registration: " . $e->getMessage();
    header("Location: complete_student_info.php");
    exit();
}
?>