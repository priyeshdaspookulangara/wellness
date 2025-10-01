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

$page_title = "Manage Landing Pages";
require_once __DIR__ . '/../includes/header.php';

// Fetch all products to populate the dropdown
$sql_products = "SELECT id, name FROM products ORDER BY name ASC";
$result_products = mysqli_query($conn, $sql_products);
$products = [];
if ($result_products) {
    while ($row = mysqli_fetch_assoc($result_products)) {
        $products[] = $row;
    }
}

// Handle messages from create.php
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
</div>

<?php if ($success_message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        Create a New Landing Page
    </div>
    <div class="card-body">
        <form action="create.php" method="POST">
            <div class="form-group">
                <label for="product_id">Select a Product</label>
                <select class="form-control" id="product_id" name="product_id" required>
                    <option value="">Choose...</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create Landing Page</button>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>