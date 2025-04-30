<?php
require_once __DIR__ . '/../../includes/auth_functions.php';
require_role(['librarian', 'super_admin']);

$user = current_user();
$pending_approvals = get_pending_approvals();

$page_title = "Librarian Dashboard";
include __DIR__ . '/../../includes/template_functions.php';
output_header();
?>

<div class="dashboard">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>Library System</h3>
            <p>Librarian Dashboard</p>
        </div>
        <ul class="sidebar-menu">
            <li class="active"><a href="<?= BASE_URL ?>/dashboard/librarian/"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/dashboard/librarian/manage_books.php"><i class="fas fa-book"></i> Manage Books</a></li>
            <li><a href="<?= BASE_URL ?>/dashboard/librarian/manage_loans.php"><i class="fas fa-exchange-alt"></i> Manage Loans</a></li>
            <li><a href="<?= BASE_URL ?>/dashboard/librarian/manage_users.php"><i class="fas fa-users"></i> Manage Users</a></li>
            <?php if ($_SESSION['user_level'] === 'super_admin'): ?>
                <li><a href="<?= BASE_URL ?>/dashboard/super_admin/settings.php"><i class="fas fa-cog"></i> System Settings</a></li>
            <?php endif; ?>
            <li><a href="<?= BASE_URL ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    
    <main class="main-content">
        <div class="dashboard-header">
            <h2>Welcome, <?= htmlspecialchars($user['full_name']) ?></h2>
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></div>
                <span class="user-name"><?= htmlspecialchars($user['full_name']) ?></span>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Pending Approvals</h3>
                <p><?= count($pending_approvals) ?></p>
            </div>
            <div class="stat-card">
                <h3>Active Loans</h3>
                <p>42</p>
            </div>
            <div class="stat-card">
                <h3>Overdue Books</h3>
                <p>5</p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3>Pending Account Approvals</h3>
                <a href="<?= BASE_URL ?>/dashboard/librarian/manage_users.php" class="btn btn-accent">Manage Users</a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>User Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pending_approvals)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No pending approvals</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pending_approvals as $approval): ?>
                                <tr>
                                    <td><?= htmlspecialchars($approval['username']) ?></td>
                                    <td><?= htmlspecialchars($approval['name']) ?></td>
                                    <td><?= htmlspecialchars($approval['email']) ?></td>
                                    <td><?= ucfirst(str_replace('_', ' ', $approval['user_level'])) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/approve_user.php?id=<?= $approval['Account_ID'] ?>" class="btn btn-primary btn-sm">Approve</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php
output_footer();
?>