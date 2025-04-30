<?php
require_once __DIR__ . '/../../includes/auth_functions.php';
require_once __DIR__ . '/../../includes/db_functions.php';
require_role('super_admin');

$user = current_user();
$pending_approvals = get_pending_approvals();

$page_title = "Super Admin Dashboard";
include __DIR__ . '/../../includes/template_functions.php';
output_header();
?>

<div class="dashboard">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>Library System</h3>
            <p>Super Admin Dashboard</p>
        </div>
        <ul class="sidebar-menu">
            <li class="active"><a href="<?= BASE_URL ?>/dashboard/super_admin/"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/dashboard/super_admin/manage_users.php"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="<?= BASE_URL ?>/dashboard/super_admin/manage_books.php"><i class="fas fa-book"></i> Manage Books</a></li>
            <li><a href="<?= BASE_URL ?>/dashboard/super_admin/manage_loans.php"><i class="fas fa-exchange-alt"></i> Manage Loans</a></li>
            <li><a href="<?= BASE_URL ?>/dashboard/super_admin/settings.php"><i class="fas fa-cog"></i> System Settings</a></li>
            <li><a href="<?= BASE_URL ?>/dashboard/super_admin/reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
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
                <h3>Total Users</h3>
                <p><?= get_total_users() ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Books</h3>
                <p><?= get_total_books() ?></p>
            </div>
            <div class="stat-card">
                <h3>Active Loans</h3>
                <p><?= get_active_loans_count() ?></p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3>Pending Account Approvals</h3>
                <a href="<?= BASE_URL ?>/dashboard/super_admin/manage_users.php" class="btn btn-accent">View All</a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>User Type</th>
                            <th>Date Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pending_approvals)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No pending approvals</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach (array_slice($pending_approvals, 0, 5) as $approval): ?>
                                <tr>
                                    <td><?= htmlspecialchars($approval['username']) ?></td>
                                    <td><?= htmlspecialchars($approval['name']) ?></td>
                                    <td><?= htmlspecialchars($approval['email']) ?></td>
                                    <td><?= ucwords(str_replace('_', ' ', $approval['user_level'])) ?></td>
                                    <td><?= date('M j, Y', strtotime($approval['created_at'])) ?></td>
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