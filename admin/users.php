<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

// Admin authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $_SESSION['admin_error'] = "Access denied.";
    header("Location: " . SITE_URL . "/login.php");
    exit;
}

$page_title = "Manage Users";
require_once 'includes/header.php';

// Handle potential actions like delete or role change (add later if needed)
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Fetch users (excluding the current admin perhaps, or all for now)
// Add pagination later
$sql_users = "SELECT id, username, email, first_name, last_name, is_admin, created_at FROM users ORDER BY created_at DESC";
$result_users = mysqli_query($conn, $sql_users);
$users_list = [];
if ($result_users) {
    while ($row = mysqli_fetch_assoc($result_users)) {
        $users_list[] = $row;
    }
} else {
    echo "<div class='alert alert-danger'>Error fetching users: " . mysqli_error($conn) . "</div>";
}

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
    <!-- <a href="user_add.php" class="btn btn-sm btn-outline-primary"><i class="fas fa-plus"></i> Add New User</a> -->
    <!-- Adding users manually by admin might not be a primary feature for e-commerce customers -->
</div>

<?php if ($success_message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Full Name</th>
                <th>Role</th>
                <th>Registered At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users_list)): ?>
                <?php foreach ($users_list as $user_item): ?>
                    <tr>
                        <td><?php echo $user_item['id']; ?></td>
                        <td><?php echo htmlspecialchars($user_item['username']); ?></td>
                        <td><?php echo htmlspecialchars($user_item['email']); ?></td>
                        <td><?php echo htmlspecialchars(($user_item['first_name'] ?? '') . ' ' . ($user_item['last_name'] ?? '')); ?></td>
                        <td>
                            <?php if ($user_item['is_admin']): ?>
                                <span class="badge badge-danger">Admin</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Customer</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date("M j, Y", strtotime($user_item['created_at'])); ?></td>
                        <td class="action-buttons">
                            <a href="user_view.php?id=<?php echo $user_item['id']; ?>" class="btn btn-sm btn-info" title="View/Edit User"><i class="fas fa-eye"></i> Edit</a>
                            <?php if ($user_item['id'] != $_SESSION['user_id'] && !$user_item['is_admin'] /* Prevent deleting self or other admins for simplicity */): ?>
                                <a href="user_delete.php?id=<?php echo $user_item['id']; ?>" class="btn btn-sm btn-danger confirm-delete" title="Delete User"><i class="fas fa-trash"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once 'includes/footer.php';
?>
