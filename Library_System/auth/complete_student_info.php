<?php
require_once __DIR__ . '/../includes/auth_functions.php';

if (!isset($_SESSION['pending_student_registration'])) {
    header("Location: signup.php");
    exit();
}

$page_title = "Complete Student Information";
include __DIR__ . '/../includes/template_functions.php';
output_header();
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Complete Your Student Information</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <form action="process_student_info.php" method="post">
            <input type="hidden" name="std_id" value="<?= $_SESSION['pending_student_registration']['std_id'] ?>">
            
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required class="form-control">
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required class="form-control">
            </div>
            
            <div class="form-group">
                <label for="contact">Contact Number</label>
                <input type="tel" id="contact" name="contact" required class="form-control">
            </div>
            
            <!-- Add more fields as needed -->
            
            <button type="submit" class="btn btn-primary btn-block">Submit Information</button>
        </form>
    </div>
</div>

<?php output_footer(); ?>