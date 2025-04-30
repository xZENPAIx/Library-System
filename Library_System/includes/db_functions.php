<?php
require_once __DIR__ . '/../config/db_config.php';

/**
 * Database-specific functions only
 * (All shared functions like sanitize_input() should be in auth_functions.php)
 */

// Database CRUD Operations
function create_user($username, $password, $user_level, $std_id = null, $admin_id = null) {
    global $conn;
    
    $hashed_password = hash_password($password);
    
    $stmt = $conn->prepare("INSERT INTO login_tbl (username, password, user_level, std_id, admin_id, is_approved) VALUES (?, ?, ?, ?, ?, ?)");
    $is_approved = ($user_level === 'student') ? 1 : 0;
    $stmt->bind_param("sssssi", $username, $hashed_password, $user_level, $std_id, $admin_id, $is_approved);
    
    return $stmt->execute();
}

function approve_user($account_id) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE login_tbl SET is_approved = 1 WHERE Account_ID = ?");
    $stmt->bind_param("i", $account_id);
    
    return $stmt->execute();
}

function student_id_exists($std_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT std_id FROM std_tbl WHERE std_id = ?");
    $stmt->bind_param("s", $std_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

function admin_id_exists($admin_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT admin_id FROM admin_tbl WHERE admin_id = ?");
    $stmt->bind_param("s", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

function get_pending_approvals() {
    global $conn;
    
    $result = $conn->query("
        SELECT l.Account_ID, l.username, l.user_level, 
               COALESCE(s.name, a.Name) as name,
               COALESCE(s.email, a.Email) as email,
               l.created_at
        FROM login_tbl l
        LEFT JOIN std_tbl s ON l.std_id = s.std_id
        LEFT JOIN admin_tbl a ON l.admin_id = a.admin_id
        WHERE l.is_approved = 0
        ORDER BY l.created_at DESC
    ");
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function get_student($std_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT s.*, p.program_name, p.year_level, p.section,
               CONCAT(a.brgy, ', ', a.municipality, ', ', a.province) as address
        FROM std_tbl s
        JOIN program_tbl p ON s.program_id = p.program_id
        JOIN address_tbl a ON s.address_id = a.address_id
        WHERE s.std_id = ?
    ");
    $stmt->bind_param("s", $std_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

function get_books($search = '') {
    global $conn;
    
    $search = "%$search%";
    $stmt = $conn->prepare("
        SELECT * FROM book_tbl 
        WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ?
        ORDER BY title
    ");
    $stmt->bind_param("sss", $search, $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function get_book($isbn) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM book_tbl WHERE isbn = ?");
    $stmt->bind_param("s", $isbn);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

function get_student_loans($std_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT l.*, b.author, b.category
        FROM lending_tbl l
        JOIN book_tbl b ON l.isbn = b.isbn
        WHERE l.std_id = ? AND l.return_date IS NULL
        ORDER BY l.due_date
    ");
    $stmt->bind_param("s", $std_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function checkout_book($isbn, $std_id, $days = 14) {
    global $conn;
    
    $book = get_book($isbn);
    if (!$book || $book['available'] <= 0) return false;
    
    $student = get_student($std_id);
    if (!$student) return false;
    
    $borrow_date = date('Y-m-d H:i:s');
    $due_date = date('Y-m-d H:i:s', strtotime("+$days days"));
    
    $conn->begin_transaction();
    try {
        // Create loan record
        $stmt = $conn->prepare("INSERT INTO lending_tbl (isbn, title, std_id, name, email, borrow_date, due_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $isbn, $book['title'], $std_id, $student['name'], $student['email'], $borrow_date, $due_date);
        $stmt->execute();
        
        // Update book status
        $stmt = $conn->prepare("UPDATE book_tbl SET available = available - 1, lended = lended + 1 WHERE isbn = ?");
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        
        // Update student record
        $stmt = $conn->prepare("UPDATE std_tbl SET borrowed_books = borrowed_books + 1 WHERE std_id = ?");
        $stmt->bind_param("s", $std_id);
        $stmt->execute();
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

function return_book($lending_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT l.* FROM lending_tbl l WHERE l.lending_id = ?");
    $stmt->bind_param("i", $lending_id);
    $stmt->execute();
    $loan = $stmt->get_result()->fetch_assoc();
    
    if (!$loan || $loan['return_date'] !== null) return false;
    
    $return_date = date('Y-m-d H:i:s');
    
    $conn->begin_transaction();
    try {
        // Update loan record
        $stmt = $conn->prepare("UPDATE lending_tbl SET return_date = ? WHERE lending_id = ?");
        $stmt->bind_param("si", $return_date, $lending_id);
        $stmt->execute();
        
        // Update book status
        $stmt = $conn->prepare("UPDATE book_tbl SET available = available + 1 WHERE isbn = ?");
        $stmt->bind_param("s", $loan['isbn']);
        $stmt->execute();
        
        // Update student record
        $stmt = $conn->prepare("UPDATE std_tbl SET borrowed_books = borrowed_books - 1 WHERE std_id = ?");
        $stmt->bind_param("s", $loan['std_id']);
        $stmt->execute();
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// Statistics functions
function get_total_users() {
    global $conn;
    $result = $conn->query("SELECT COUNT(*) as count FROM login_tbl");
    return $result->fetch_assoc()['count'];
}

function get_total_books() {
    global $conn;
    $result = $conn->query("SELECT COUNT(*) as count FROM book_tbl");
    return $result->fetch_assoc()['count'];
}

function get_active_loans_count() {
    global $conn;
    $result = $conn->query("SELECT COUNT(*) as count FROM lending_tbl WHERE return_date IS NULL");
    return $result->fetch_assoc()['count'];
}