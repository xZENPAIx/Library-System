<?php
require_once __DIR__ . '/../../includes/auth_functions.php';
require_role('student');

$user = current_user();
$student = get_student($user['std_id']);
$loans = get_student_loans($user['std_id']);

$page_title = "Student Dashboard";
include __DIR__ . '/../../includes/template_functions.php';
output_header();
?>

<div class="dashboard">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>Library System</h3>
            <p>Student Dashboard</p>
        </div>
        <ul class="sidebar-menu">
            <li class="active"><a href="<?= BASE_URL ?>/dashboard/student/"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/dashboard/student/browse_books.php"><i class="fas fa-book"></i> Browse Books</a></li>
            <li><a href="<?= BASE_URL ?>/dashboard/student/my_loans.php"><i class="fas fa-exchange-alt"></i> My Loans</a></li>
            <li><a href="<?= BASE_URL ?>/dashboard/student/profile.php"><i class="fas fa-user"></i> My Profile</a></li>
            <li><a href="<?= BASE_URL ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    
    <main class="main-content">
        <div class="dashboard-header">
            <h2>Welcome, <?= htmlspecialchars($student['name']) ?></h2>
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($student['name'], 0, 1)) ?></div>
                <span class="user-name"><?= htmlspecialchars($student['name']) ?></span>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Books Borrowed</h3>
                <p><?= $student['borrowed_books'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Overdue Books</h3>
                <p><?= count(array_filter($loans, fn($loan) => strtotime($loan['due_date']) < time())) ?></p>
            </div>
            <div class="stat-card">
                <h3>Program</h3>
                <p><?= htmlspecialchars($student['program_name']) ?></p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3>My Current Loans</h3>
                <a href="<?= BASE_URL ?>/dashboard/student/my_loans.php" class="btn btn-accent">View All</a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($loans)): ?>
                            <tr>
                                <td colspan="4" class="text-center">No current loans</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($loans as $loan): ?>
                                <tr>
                                    <td><?= htmlspecialchars($loan['title']) ?></td>
                                    <td><?= htmlspecialchars($loan['author']) ?></td>
                                    <td><?= date('M j, Y', strtotime($loan['due_date'])) ?></td>
                                    <td>
                                        <span class="status-badge <?= strtotime($loan['due_date']) < time() ? 'status-overdue' : 'status-active' ?>">
                                            <?= strtotime($loan['due_date']) < time() ? 'Overdue' : 'Active' ?>
                                        </span>
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