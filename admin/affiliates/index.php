<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';

// Admin authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $_SESSION['admin_error'] = "Access denied.";
    header("Location: " . SITE_URL . "login/");
    exit;
}

$page_title = "Manage Affiliates";
$breadcrumbs = [ ['name' => 'Affiliates'] ];
require_once __DIR__ . '/../includes/header.php';

// Handle potential actions
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Fetch affiliates
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql_affiliates = "
    SELECT a.id, a.referral_code, a.status, a.created_at, u.name, u.email
    FROM affiliates a
    JOIN users u ON a.user_id = u.id
    ORDER BY a.created_at DESC
";
$stmt = $conn->prepare($sql_affiliates);
$stmt->execute();
$result = $stmt->get_result();
$affiliates_list = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $affiliates_list[] = $row;
    }
}
$stmt->close();

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
    <a href="<?php echo SITE_URL; ?>admin/affiliates/add.php" class="btn btn-sm btn-outline-primary"><i class="fas fa-plus"></i> Add New Affiliate</a>
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
                <th>Name</th>
                <th>Email</th>
                <th>Referral Code</th>
                <th>Status</th>
                <th>Joined At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($affiliates_list)): ?>
                <?php foreach ($affiliates_list as $affiliate): ?>
                    <tr>
                        <td><?php echo $affiliate['id']; ?></td>
                        <td><?php echo htmlspecialchars($affiliate['name']); ?></td>
                        <td><?php echo htmlspecialchars($affiliate['email']); ?></td>
                        <td><?php echo htmlspecialchars($affiliate['referral_code']); ?></td>
                        <td>
                            <?php if ($affiliate['status'] === 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date("M j, Y", strtotime($affiliate['created_at'])); ?></td>
                        <td class="action-buttons">
                            <a href="<?php echo SITE_URL; ?>admin/affiliates/edit.php?id=<?php echo $affiliate['id']; ?>" class="btn btn-sm btn-info" title="Edit Affiliate"><i class="fas fa-edit"></i></a>
                            <a href="<?php echo SITE_URL; ?>admin/affiliates/delete.php?id=<?php echo $affiliate['id']; ?>" class="btn btn-sm btn-danger confirm-delete" title="Delete Affiliate"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No affiliates found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>