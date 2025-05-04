<?php
require_once __DIR__ . '/../../includes/auth_functions.php';
require_once __DIR__ . '/../../includes/db_functions.php';
require_role('super_admin');

$books = get_books();

$page_title = "Manage Books";
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
    <li class="active"><a href="<?= BASE_URL ?>/dashboard/super_admin/manage_books.php"><i class="fas fa-book"></i> Manage Books</a></li>
    <li><a href="<?= BASE_URL ?>/dashboard/super_admin/manage_loans.php"><i class="fas fa-exchange-alt"></i> Manage Loans</a></li>
    <li><a href="<?= BASE_URL ?>/dashboard/super_admin/settings.php"><i class="fas fa-cog"></i> System Settings</a></li>
    <li><a href="<?= BASE_URL ?>/dashboard/super_admin/reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
    <li><a href="<?= BASE_URL ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
</ul>
    </aside>

    <main class="main-content">
        <div class="dashboard-header">
            <h2>Manage Books</h2>
        </div>

        <div class="card">
            <div class="table-responsive">
                <?php if (empty($books)): ?>
                    <p>No books found.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ISBN</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Available</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($books as $book): ?>
                                <tr>
                                    <td><?= htmlspecialchars($book['isbn']) ?></td>
                                    <td><?= htmlspecialchars($book['title']) ?></td>
                                    <td><?= htmlspecialchars($book['author']) ?></td>
                                    <td><?= htmlspecialchars($book['category']) ?></td>
                                    <td><?= htmlspecialchars($book['available']) ?></td>
                                    <td>
                                        <!-- Placeholder for actions like edit, delete -->
                                        <a href="#" class="btn btn-sm btn-primary disabled">Edit</a>
                                        <a href="#" class="btn btn-sm btn-danger disabled">Delete</a>
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

<?php
output_footer();
?>
