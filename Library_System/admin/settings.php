<?php
require_once __DIR__ . '/../includes/auth_functions.php';
require_once __DIR__ . '/../includes/gmail_api.php';
require_role(['super_admin']);

$page_title = "System Settings";
include __DIR__ . '/../includes/template_functions.php';
output_header();

$gmailStatus = false;
try {
    $gmail = new GmailAPI();
    $gmailStatus = file_exists(__DIR__ . '/../config/gmail_token.json');
} catch (Exception $e) {
    $_SESSION['error'] = "Gmail API check failed: " . $e->getMessage();
}
?>

<div class="dashboard">
    <main class="main-content">
        <div class="card">
            <div class="card-header">
                <h3>Email Settings</h3>
            </div>
            <div class="card-body">
                <div class="alert <?= $gmailStatus ? 'alert-success' : 'alert-error' ?>">
                    Gmail API: <?= $gmailStatus ? 'Connected' : 'Not Connected' ?>
                </div>
                
                <?php if (!$gmailStatus): ?>
                    <a href="<?= BASE_URL ?>/admin/connect_gmail.php" 
                       class="btn btn-primary">
                       Connect Gmail Account
                    </a>
                <?php endif; ?>
                
                <h4 class="mt-4">Test Email</h4>
                <form action="<?= BASE_URL ?>/admin/test_email.php" method="post">
                    <div class="form-group">
                        <label>Recipient Email</label>
                        <input type="email" name="test_email" required 
                               class="form-control" 
                               value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-accent">
                        Send Test Email
                    </button>
                </form>
            </div>
        </div>
    </main>
</div>

<?php output_footer(); ?>