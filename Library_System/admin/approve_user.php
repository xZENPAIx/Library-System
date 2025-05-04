<?php
require_once __DIR__ . '/../includes/auth_functions.php';
require_once __DIR__ . '/../includes/db_functions.php';
require_role(['super_admin', 'librarian']);

if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $account_id = (int)$_GET['approve'];
    
    if (approve_user($account_id)) {
        $_SESSION['success'] = "User approved successfully";
    } else {
        $_SESSION['error'] = "Failed to approve user";
    }
    
    header("Location: ../dashboard/super_admin/index.php");
    exit();
}

if (isset($_GET['reject']) && is_numeric($_GET['reject'])) {
    $account_id = (int)$_GET['reject'];
    
    if (reject_user($account_id)) {
        $_SESSION['success'] = "User rejected successfully";
    } else {
        $_SESSION['error'] = "Failed to reject user";
    }
    
    header("Location: ../dashboard/super_admin/index.php");
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