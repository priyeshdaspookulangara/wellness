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

$page_title = "Manage Products";
require_once __DIR__ . '/../includes/header.php'; // Corrected

// Fetch products from database
$sql_products = "SELECT p.id, p.name, p.price, p.stock, p.is_featured, p.is_on_sale, c.name as category_name, p.image_url_main
                 FROM products p
                 LEFT JOIN categories c ON p.category_id = c.id
                 ORDER BY p.created_at DESC";
$result_products = mysqli_query($conn, $sql_products);
$products = [];
if ($result_products) {
    while ($row = mysqli_fetch_assoc($result_products)) {
        $products[] = $row;
    }
} else {
    // Handle error, e.g., display a message
    echo "<div class='alert alert-danger'>Error fetching products: " . mysqli_error($conn) . "</div>";
}

// Handle messages from other product actions (add, edit, delete)
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="../product_add/" class="btn btn-sm btn-outline-primary"> <!-- Corrected link -->
            <i class="fas fa-plus"></i> Add New Product
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
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Featured</th>
                <th>On Sale</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['id']); ?></td>
                        <td>
                            <?php if (!empty($product['image_url_main'])): ?>
                                <img src="<?php echo SITE_URL . 'uploads/' . htmlspecialchars($product['image_url_main']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 50px; height: auto;">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/50" alt="No image" style="width: 50px;">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                        <td>$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($product['stock']); ?></td>
                        <td><?php echo $product['is_featured'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'; ?></td>
                        <td><?php echo $product['is_on_sale'] ? '<span class="badge bg-warning text-dark">Yes</span>' : '<span class="badge bg-secondary">No</span>'; ?></td>
                        <td class="action-buttons">
                            <a href="../product_edit/index.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-info" title="Edit"><i class="fas fa-edit"></i></a> <!-- Corrected link -->
                            <a href="<?php echo SITE_URL . 'admin/product_delete.php?id=' . $product['id']; ?>" class="btn btn-sm btn-danger confirm-delete" title="Delete"><i class="fas fa-trash"></i></a> <!-- product_delete.php is in admin root -->
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">No products found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php'; // Corrected
?>
