<?php
require_once __DIR__ . '/../includes/auth_functions.php';
redirect_if_logged_in();

// Generate CSRF token for the login form
$csrf_token = generate_csrf_token();

$page_title = "Login";
include __DIR__ . '/../includes/template_functions.php';
output_header();
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Library System Login</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <form action="authenticate.php" method="post" id="login-form">
            <!-- CSRF Token Field -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            
            <div class="form-group">
                <label for="user_level">Login As</label>
                <select id="user_level" name="user_level" required class="form-control">
                    <option value="">Select user type</option>
                    <option value="student">Student</option>
                    <option value="librarian">Librarian</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required class="form-control">
            </div>
            
            <div class="form-group password-toggle">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required class="form-control">
                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        
        <div class="auth-links">
            <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
        </div>
    </div>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const password = document.getElementById('password');
    const icon = this;
    
    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});
</script>

<?php
output_footer();
?>