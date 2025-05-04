<?php
require_once __DIR__ . '/../includes/auth_functions.php';
redirect_if_logged_in();

$page_title = "Sign Up";
include __DIR__ . '/../includes/template_functions.php';
output_header();
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Create an Account</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <form action="register.php" method="post" id="signup-form">
            <div class="form-group">
                <label for="user_level">Account Type</label>
                <select id="user_level" name="user_level" required onchange="toggleIdField()" class="form-control">
                    <option value="">Select account type</option>
                    <option value="student">Student</option>
                    <option value="librarian">Librarian</option>
                </select>
            </div>
            
            <div class="form-group" id="std_id_group" style="display:none;">
                <label for="std_id">Student ID</label>
                <input type="text" id="std_id" name="std_id" class="form-control">
            </div>
            
            <div class="form-group" id="admin_id_group" style="display:none;">
                <label for="admin_id">Admin ID</label>
                <input type="text" id="admin_id" name="admin_id" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required class="form-control">
            </div>
            
            <div class="form-group password-toggle">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="8" class="form-control">
                <i class="fas fa-eye toggle-password" id="togglePassword1"></i>
            </div>
            
            <div class="form-group password-toggle">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8" class="form-control">
                <i class="fas fa-eye toggle-password" id="togglePassword2"></i>
            </div>
            
            <div class="terms-group">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">I agree to the <a href="<?= BASE_URL ?>/terms.php">Terms of Service</a></label>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>
        
        <div class="auth-links">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</div>

<script>
function toggleIdField() {
    const userLevel = document.getElementById("user_level").value;
    document.getElementById("std_id_group").style.display = userLevel === "student" ? "block" : "none";
    document.getElementById("admin_id_group").style.display = userLevel === "librarian" ? "block" : "none";
    
    document.getElementById("std_id").required = userLevel === "student";
    document.getElementById("admin_id").required = userLevel === "librarian";
}

// Password toggle functionality
document.getElementById('togglePassword1').addEventListener('click', function() {
    const password = document.getElementById('password');
    const icon = this;
    togglePasswordVisibility(password, icon);
});

document.getElementById('togglePassword2').addEventListener('click', function() {
    const password = document.getElementById('confirm_password');
    const icon = this;
    togglePasswordVisibility(password, icon);
});

function togglePasswordVisibility(field, icon) {
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

document.getElementById("signup-form").addEventListener("submit", function(e) {
    const password = document.getElementById("password").value;
    const confirm = document.getElementById("confirm_password").value;
    
    if (password !== confirm) {
        e.preventDefault();
        alert("Passwords do not match!");
        return false;
    }
    
    if (password.length < 8) {
        e.preventDefault();
        alert("Password must be at least 8 characters long!");
        return false;
    }
    
    const terms = document.getElementById("terms");
    if (!terms.checked) {
        e.preventDefault();
        alert("You must agree to the Terms of Service");
        return false;
    }
    
    return true;
});
</script>

<?php
output_footer();
?>