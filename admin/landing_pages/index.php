<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

// Check for admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . SITE_URL . "/login");
    exit;
}

$db = db_connect();
$stmt = $db->query("SELECT * FROM landing_pages ORDER BY created_at DESC");
$landing_pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1>Landing Page Creator</h1>
            <a href="edit.php" class="btn btn-primary mb-3">Create New Landing Page</a>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Page Name</th>
                        <th>URL</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($landing_pages as $page): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($page['page_name']); ?></td>
                            <td><a href="<?php echo SITE_URL . '/' . htmlspecialchars($page['slug']); ?>" target="_blank">/<?php echo htmlspecialchars($page['slug']); ?></a></td>
                            <td><?php echo $page['is_active'] ? 'Active' : 'Inactive'; ?></td>
                            <td><?php echo $page['created_at']; ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $page['page_id']; ?>" class="btn btn-sm btn-info">Edit</a>
                                <a href="delete.php?id=<?php echo $page['page_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
