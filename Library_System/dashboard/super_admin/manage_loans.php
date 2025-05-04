<?php
require_once __DIR__ . '/../../includes/auth_functions.php';
require_once __DIR__ . '/../../includes/db_functions.php';
require_role('super_admin');

$loans = $conn->query("
    SELECT l.*, b.title, b.author, b.category, s.name as student_name
    FROM lending_tbl l
    JOIN book_tbl b ON l.isbn = b.isbn
    JOIN std_tbl s ON l.std_id = s.std_id
    ORDER BY l.borrow_date DESC
")->fetch_all(MYSQLI_ASSOC);

$page_title = "Manage Loans";
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
    <li class="active"><a href="<?= BASE_URL ?>/dashboard/super_admin/manage_loans.php"><i class="fas fa-exchange-alt"></i> Manage Loans</a></li>
    <li><a href="<?= BASE_URL ?>/dashboard/super_admin/settings.php"><i class="fas fa-cog"></i> System Settings</a></li>
    <li><a href="<?= BASE_URL ?>/dashboard/super_admin/reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
    <li><a href="<?= BASE_URL ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
</ul>
    </aside>

    <main class="main-content">
        <div class="dashboard-header">
            <h2>Manage Loans</h2>
        </div>

        <div class="card">
            <div class="table-responsive">
                <?php if (empty($loans)): ?>
                    <p>No loans found.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ISBN</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Student</th>
                                <th>Borrow Date</th>
                                <th>Due Date</th>
                                <th>Return Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loans as $loan): ?>
                                <tr>
                                    <td><?= htmlspecialchars($loan['isbn']) ?></td>
                                    <td><?= htmlspecialchars($loan['title']) ?></td>
                                    <td><?= htmlspecialchars($loan['author']) ?></td>
                                    <td><?= htmlspecialchars($loan['category']) ?></td>
                                    <td><?= htmlspecialchars($loan['student_name']) ?></td>
                                    <td><?= htmlspecialchars($loan['borrow_date']) ?></td>
                                    <td><?= htmlspecialchars($loan['due_date']) ?></td>
                                    <td><?= htmlspecialchars($loan['return_date'] ?? 'Not returned') ?></td>
                                    <td>
                                        <!-- Placeholder for actions like mark returned -->
                                        <a href="#" class="btn btn-sm btn-primary disabled">Mark Returned</a>
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
