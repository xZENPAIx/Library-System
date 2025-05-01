<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/db_functions.php';

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sanitize user input to prevent XSS attacks
 */
function sanitize_input($data) {
    global $conn;
    $data = stripslashes(trim($data));
    return $conn->real_escape_string(htmlspecialchars($data, ENT_QUOTES, 'UTF-8'));
}
/**
 * Hash password with SHA256 and application salt
 */
function hash_password($password) {
    return hash('sha256', $password . 'LIBRARY_SALT_2023');
}

/**
 * Get user by username from database
 */
function get_user_by_username($username) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM login_tbl WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Verify super admin credentials with strict checks
 */
function verify_super_admin($username, $password) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT l.* 
        FROM login_tbl l
        JOIN admin_tbl a ON l.admin_id = a.admin_id
        WHERE l.username = ? 
        AND l.user_level = 'super_admin'
        AND a.User_level = 'Super Admin'
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    return ($user && hash_password($password) === $user['password']) ? $user : false;
}

/**
 * Authenticate regular users (non-super admin)
 */
function login_user($username, $password, $requested_level) {
    if ($requested_level === 'super_admin') {
        return false; // Super admin handled separately
    }
    
    $user = get_user_by_username($username);
    
    if (!$user || hash_password($password) !== $user['password']) {
        return false;
    }
    
    if ($user['user_level'] !== $requested_level) {
        return false;
    }
    
    if (!$user['is_approved']) {
        return 'pending_approval';
    }
    
    return $user;
}

/**
 * Check if user is authenticated
 */
function is_authenticated() {
    return isset($_SESSION['user_id']);
}

/**
 * Require authentication for protected pages
 */
function require_auth() {
    if (!is_authenticated()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        $_SESSION['error'] = "Please login to access this page";
        header("Location: " . BASE_URL . "/auth/login.php");
        exit();
    }

    // Regenerate session ID periodically to prevent fixation
    if (!isset($_SESSION['created_at'])) {
        $_SESSION['created_at'] = time();
    } elseif (time() - $_SESSION['created_at'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['created_at'] = time();
    }

    // Validate user agent for extra security
    if (!isset($_SESSION['user_agent'])) {
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    } elseif ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        logout();
        die("Session hijacking detected.");
    }
}

/**
 * Require specific role(s) for authorization
 */
function require_role($allowed_roles) {
    require_auth();
    
    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }
    
    if (!in_array($_SESSION['user_level'], $allowed_roles)) {
        header("Location: " . BASE_URL . "/unauthorized.php");
        exit();
    }
}

/**
 * Get current authenticated user's data
 */
function current_user() {
    if (!is_authenticated()) {
        return null;
    }
    
    global $conn;
    
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("
        SELECT l.*, 
               COALESCE(s.name, a.Name) as full_name,
               COALESCE(s.email, a.Email) as email,
               COALESCE(s.std_id, a.admin_id) as user_identifier
        FROM login_tbl l
        LEFT JOIN std_tbl s ON l.std_id = s.std_id
        LEFT JOIN admin_tbl a ON l.admin_id = a.admin_id
        WHERE l.Account_ID = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Redirect if user is already logged in
 */
function redirect_if_logged_in() {
    if (is_authenticated()) {
        $dashboard = match($_SESSION['user_level']) {
            'super_admin' => '/dashboard/super_admin/',
            'librarian' => '/dashboard/librarian/',
            default => '/dashboard/student/'
        };
        
        header("Location: " . BASE_URL . $dashboard);
        exit();
    }
}

/**
 * Properly destroy user session
 */
function logout() {
    // Clear session data
    $_SESSION = array();

    // Destroy session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 42000,
            $params["path"], 
            $params["domain"],
            $params["secure"], 
            $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Generate and store CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validate_csrf_token($token) {
    if (
        !isset($_SESSION['csrf_token']) ||
        !isset($_SESSION['csrf_token_time']) ||
        !hash_equals($_SESSION['csrf_token'], $token) ||
        (time() - $_SESSION['csrf_token_time']) > 3600 // 1 hour expiration
    ) {
        // For debugging purposes (remove in production)
        error_log("CSRF Validation Failed: " . json_encode([
            'session_token' => $_SESSION['csrf_token'] ?? null,
            'provided_token' => $token,
            'token_time' => isset($_SESSION['csrf_token_time']) ? time() - $_SESSION['csrf_token_time'] : null
        ]));
        
        throw new Exception("CSRF validation failed. Please refresh the page and try again.");
    }
    
    // Consume the token after validation
    unset($_SESSION['csrf_token']);
    unset($_SESSION['csrf_token_time']);
}