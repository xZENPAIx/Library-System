<?php
require_once __DIR__ . '/../../includes/auth_functions.php';
require_once __DIR__ . '/../../includes/db_functions.php';
require_role('super_admin');

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $account_id = (int)$_GET['delete'];
    if (reject_user($account_id)) {
        $_SESSION['success'] = "User deleted successfully";
    } else {
        $_SESSION['error'] = "Failed to delete user";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_account_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM login_tbl WHERE Account_ID = ?");
    $stmt->bind_param("i", $edit_account_id);
    $stmt->execute();
    $edit_user = $stmt->get_result()->fetch_assoc();
} else {
    $edit_account_id = null;
    $edit_user = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_account_id'])) {
    $account_id = (int)$_POST['edit_account_id'];
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? null;
    if ($password === '') {
        $password = null;
    }
    if (update_user_credentials($account_id, $username, $password)) {
        $_SESSION['success'] = "User updated successfully";
    } else {
        $_SESSION['error'] = "Failed to update user";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$users = $conn->query("SELECT * FROM login_tbl")->fetch_all(MYSQLI_ASSOC);

$page_title = "Manage Users";
include __DIR__ . '/../../includes/template_functions.php';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/sidebar_fix.css">
<?php
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
    <li class="dropdown active">
        <a href="#" class="dropdown-toggle"><i class="fas fa-users"></i> Manage Users <span class="caret"></span></a>
        <ul class="dropdown-menu">
            <li><a href="<?= BASE_URL ?>/dashboard/super_admin/manage_users_students.php">Students</a></li>
            <li><a href="<?= BASE_URL ?>/dashboard/super_admin/manage_users_admin.php">Admin</a></li>
        </ul>
    </li>
    <li><a href="<?= BASE_URL ?>/dashboard/super_admin/manage_books.php"><i class="fas fa-book"></i> Manage Books</a></li>
    <li><a href="<?= BASE_URL ?>/dashboard/super_admin/manage_loans.php"><i class="fas fa-exchange-alt"></i> Manage Loans</a></li>
    <li><a href="<?= BASE_URL ?>/dashboard/super_admin/settings.php"><i class="fas fa-cog"></i> System Settings</a></li>
    <li><a href="<?= BASE_URL ?>/dashboard/super_admin/reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
    <li><a href="<?= BASE_URL ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
</ul>
    </aside>

    <main class="main-content">
        <div class="dashboard-header">
            <h2>Manage Users</h2>
        </div>

        <?php if ($edit_user): ?>
            <div class="card">
                <div class="card-header">
                    <h3>Edit User</h3>
                </div>
                <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
                    <input type="hidden" name="edit_account_id" value="<?= $edit_user['Account_ID'] ?>">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($edit_user['username']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password (leave blank to keep current)</label>
                        <input type="password" id="password" name="password" value="">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="table-responsive">
                <?php if (empty($users)): ?>
                    <p>No users found.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>User Level</th>
                                <th>Admin/Student ID</th>
                                <th>Created At</th>
                                <th>Approved</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= htmlspecialchars($user['user_level']) ?></td>
                                    <td>
                                        <?php
                                        if ($user['user_level'] === 'admin' || $user['user_level'] === 'librarian') {
                                            echo htmlspecialchars($user['admin_id']);
                                        } elseif ($user['user_level'] === 'student') {
                                            echo htmlspecialchars($user['std_id']);
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars($user['created_at']) ?></td>
                                    <td><?= $user['is_approved'] ? 'Yes' : 'No' ?></td>
                                    <td>
                                        <a href="?edit=<?= $user['Account_ID'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="?delete=<?= $user['Account_ID'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</a>
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
