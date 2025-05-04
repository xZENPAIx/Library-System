<?php
require_once __DIR__ . '/../../includes/auth_functions.php';
require_once __DIR__ . '/../../includes/db_functions.php';
require_role('super_admin');

$page_title = "Reports";
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
    <li><a href="<?= BASE_URL ?>/dashboard/super_admin/"><i class="fas fa-home"></i> Dashboard</a></li>
    <li class="dropdown">
        <a href="#" class="dropdown-toggle"><i class="fas fa-users"></i> Manage Users <span class="caret"></span></a>
        <ul class="dropdown-menu">
            <li><a href="<?= BASE_URL ?>/dashboard/super_admin/manage_users_students.php">Students</a></li>
            <li><a href="<?= BASE_URL ?>/dashboard/super_admin/manage_users_admin.php">Admin</a></li>
        </ul>
    </li>
    <li><a href="<?= BASE_URL ?>/dashboard/super_admin/manage_books.php"><i class="fas fa-book"></i> Manage Books</a></li>
    <li><a href="<?= BASE_URL ?>/dashboard/super_admin/manage_loans.php"><i class="fas fa-exchange-alt"></i> Manage Loans</a></li>
    <li><a href="<?= BASE_URL ?>/dashboard/super_admin/settings.php"><i class="fas fa-cog"></i> System Settings</a></li>
    <li class="active"><a href="<?= BASE_URL ?>/dashboard/super_admin/reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
    <li><a href="<?= BASE_URL ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
</ul>
    </aside>

    <main class="main-content">
        <div class="dashboard-header">
            <h2>Reports</h2>
        </div>

        <div class="card">
            <p>Reports functionality to be implemented.</p>
        </div>
    </main>
</div>

<?php
output_footer();
?>
