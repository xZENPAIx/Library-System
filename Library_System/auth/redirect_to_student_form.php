<?php
require_once __DIR__ . '/../includes/auth_functions.php';
require_once __DIR__ . '/../includes/db_functions.php';

if (!isset($_SESSION['signup_data'])) {
    $_SESSION['error'] = "Invalid registration flow";
    header("Location: " . BASE_URL . "/auth/signup.php");
    exit();
}

$page_title = "Student Registration";
include __DIR__ . '/../includes/template_functions.php';
output_header();
?>
<div class="auth-container">
    <div class="auth-card">
        <h2>Complete Student Registration</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <form action="<?= BASE_URL ?>/auth/register.php" method="post">
            <input type="hidden" name="user_level" value="<?= $_SESSION['signup_data']['user_level'] ?>">
            <input type="hidden" name="username" value="<?= $_SESSION['signup_data']['username'] ?>">
            <input type="hidden" name="password" value="<?= $_SESSION['signup_data']['password'] ?>">
            
            <div class="form-group">
                <label for="std_id">Student ID</label>
                <input type="text" id="std_id" name="std_id" required class="form-control">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Submit</button>
        </form>
    </div>
</div>
<?php output_footer(); ?>