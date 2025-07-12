<?php
session_start();
require_once __DIR__ . '/../../config.php'; // Corrected
require_once __DIR__ . '/../../includes/db.php'; // Corrected

// Admin authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $_SESSION['admin_error'] = "Access denied.";
    header("Location: " . SITE_URL . "login/"); // Corrected redirect
    exit;
}

$page_title = "Manage Categories";
// Breadcrumbs for this page
$breadcrumbs = [
    ['name' => 'Categories']
];
require_once __DIR__ . '/../includes/header.php'; // Corrected

// Fetch categories from database
$sql_categories = "SELECT id, name, slug, description, image_url FROM categories ORDER BY name ASC";
$result_categories = mysqli_query($conn, $sql_categories);
$categories = [];
if ($result_categories) {
    while ($row = mysqli_fetch_assoc($result_categories)) {
        $categories[] = $row;
    }
} else {
    echo "<div class='alert alert-danger'>Error fetching categories: " . mysqli_error($conn) . "</div>";
}

// Handle messages from other category actions
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="../category_add/" class="btn btn-sm btn-outline-primary"> <!-- Corrected link -->
            <i class="fas fa-plus"></i> Add New Category
        </a>
    </div>
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
                <th>Image</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($category['id']); ?></td>
                        <td>
                            <?php if (!empty($category['image_url'])): ?>
                                <img src="<?php echo SITE_URL . 'uploads/categories/' . htmlspecialchars($category['image_url']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" style="width: 50px; height: auto;">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/50" alt="No image" style="width: 50px;">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                        <td><?php echo htmlspecialchars($category['slug']); ?></td>
                        <td><?php echo htmlspecialchars(substr($category['description'] ?? '', 0, 100)) . (strlen($category['description'] ?? '') > 100 ? '...' : ''); ?></td>
                        <td class="action-buttons">
                            <a href="../category_edit/?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-info" title="Edit"><i class="fas fa-edit"></i></a> <!-- Corrected link -->
                            <a href="<?php echo SITE_URL . 'admin/category_delete.php?id=' . $category['id']; ?>" class="btn btn-sm btn-danger confirm-delete" title="Delete"><i class="fas fa-trash"></i></a> <!-- category_delete.php is in admin root -->
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No categories found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php'; // Corrected
?>
