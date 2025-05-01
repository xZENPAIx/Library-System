<?php
require_once __DIR__ . '/../../includes/auth_functions.php';
require_once __DIR__ . '/../../includes/db_functions.php';
require_once __DIR__ . '/../../includes/email_functions.php';
require_role(['super_admin', 'librarian']);

// Handle approval
if (isset($_GET['approve'])) {
    $account_id = (int)$_GET['approve'];
    if (approve_user($account_id)) {
        // Get user details to send email
        $user = get_user_by_id($account_id);
        if ($user && $user['user_level'] === 'student') {
            try {
                $student = get_student($user['std_id']);
                if ($student && !empty($student['email'])) {
                    send_account_approved_email(
                        $student['email'],
                        $student['name'],
                        $user['username']
                    );
                    $_SESSION['success'] = "User approved and notified successfully";
                }
            } catch (Exception $e) {
                error_log("Email error: " . $e->getMessage());
                $_SESSION['warning'] = "User approved but email notification failed";
            }
        }
    } else {
        $_SESSION['error'] = "Failed to approve user";
    }
    header("Location: approve_users.php");
    exit();
}

// Display pending users
$page_title = "Approve Users";
include __DIR__ . '/../../includes/template_functions.php';
output_header();
?>

<div class="dashboard">
    <main class="main-content">
        <div class="card">
            <div class="card-header">
                <h3>Pending Approvals</h3>
            </div>
            <div class="table-responsive">
                <?php $pending_users = get_pending_approvals(); ?>
                <?php if (empty($pending_users)): ?>
                    <p class="text-center">No pending approvals</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Type</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= ucfirst($user['user_level']) ?></td>
                                    <td><?= htmlspecialchars($user['name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($user['email'] ?? 'N/A') ?></td>
                                    <td>
                                        <a href="?approve=<?= $user['Account_ID'] ?>" 
                                           class="btn btn-primary"
                                           onclick="return confirm('Approve this user?')">
                                            Approve
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php output_footer(); ?>