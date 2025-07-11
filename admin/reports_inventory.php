<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php'; // Database connection

// Admin authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $_SESSION['admin_error'] = "Access denied.";
    header("Location: " . SITE_URL . "/login.php");
    exit;
}

$page_title = "Inventory Reports";
require_once 'includes/header.php';

// Low Stock Threshold
$low_stock_threshold = $_GET['low_stock_threshold'] ?? 10; // Default to 10
if (!is_numeric($low_stock_threshold) || $low_stock_threshold < 0) {
    $low_stock_threshold = 10;
}


// --- Low Stock Products ---
$sql_low_stock = "SELECT id, name, slug, stock, category_id
                  FROM products
                  WHERE stock <= " . (int)$low_stock_threshold . " AND stock > 0
                  ORDER BY stock ASC";
$res_low_stock = mysqli_query($conn, $sql_low_stock);
$low_stock_products = [];
if($res_low_stock) {
    while($row = mysqli_fetch_assoc($res_low_stock)) {
        $low_stock_products[] = $row;
    }
}

// --- Out of Stock Products ---
$sql_out_of_stock = "SELECT id, name, slug, category_id
                     FROM products
                     WHERE stock <= 0
                     ORDER BY name ASC";
$res_out_of_stock = mysqli_query($conn, $sql_out_of_stock);
$out_of_stock_products = [];
if($res_out_of_stock) {
    while($row = mysqli_fetch_assoc($res_out_of_stock)) {
        $out_of_stock_products[] = $row;
    }
}

// --- All Product Stock Levels (could be paginated for large catalogs) ---
$sql_all_stock = "SELECT p.id, p.name, p.slug, p.stock, c.name as category_name
                  FROM products p
                  LEFT JOIN categories c ON p.category_id = c.id
                  ORDER BY p.name ASC";
// Add LIMIT and OFFSET for pagination if needed
$res_all_stock = mysqli_query($conn, $sql_all_stock);
$all_product_stocks = [];
if($res_all_stock) {
    while($row = mysqli_fetch_assoc($res_all_stock)) {
        $all_product_stocks[] = $row;
    }
}

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
</div>

<form method="GET" action="reports_inventory.php" class="form-inline mb-4">
    <div class="form-group mr-2">
        <label for="low_stock_threshold" class="mr-1">Low Stock Threshold:</label>
        <input type="number" class="form-control" id="low_stock_threshold" name="low_stock_threshold" value="<?php echo htmlspecialchars($low_stock_threshold); ?>" min="0">
    </div>
    <button type="submit" class="btn btn-primary">Update Threshold</button>
</form>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-warning text-white">Low Stock Products (<= <?php echo (int)$low_stock_threshold; ?> items)</div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                <?php if (!empty($low_stock_products)): ?>
                <table class="table table-sm table-hover">
                    <thead><tr><th>ID</th><th>Product</th><th>Stock</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($low_stock_products as $prod): ?>
                        <tr>
                            <td><?php echo $prod['id']; ?></td>
                            <td><?php echo htmlspecialchars($prod['name']); ?></td>
                            <td><span class="badge badge-warning"><?php echo $prod['stock']; ?></span></td>
                            <td><a href="product_edit.php?id=<?php echo $prod['id']; ?>" class="btn btn-xs btn-outline-secondary">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>No products currently at or below the low stock threshold.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-danger text-white">Out of Stock Products</div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                <?php if (!empty($out_of_stock_products)): ?>
                <table class="table table-sm table-hover">
                     <thead><tr><th>ID</th><th>Product</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($out_of_stock_products as $prod): ?>
                        <tr>
                            <td><?php echo $prod['id']; ?></td>
                            <td><?php echo htmlspecialchars($prod['name']); ?></td>
                             <td><a href="product_edit.php?id=<?php echo $prod['id']; ?>" class="btn btn-xs btn-outline-secondary">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>No products are currently out of stock.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">All Product Stock Levels</div>
            <div class="card-body">
                <?php if (!empty($all_product_stocks)): ?>
                <table class="table table-sm table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_product_stocks as $prod): ?>
                        <tr>
                            <td><?php echo $prod['id']; ?></td>
                            <td><?php echo htmlspecialchars($prod['name']); ?></td>
                            <td><?php echo htmlspecialchars($prod['category_name'] ?? 'N/A'); ?></td>
                            <td><?php echo $prod['stock']; ?></td>
                            <td>
                                <?php if ($prod['stock'] <= 0): ?>
                                    <span class="badge badge-danger">Out of Stock</span>
                                <?php elseif ($prod['stock'] <= $low_stock_threshold): ?>
                                    <span class="badge badge-warning">Low Stock</span>
                                <?php else: ?>
                                    <span class="badge badge-success">In Stock</span>
                                <?php endif; ?>
                            </td>
                            <td><a href="product_edit.php?id=<?php echo $prod['id']; ?>" class="btn btn-xs btn-outline-secondary">Edit Stock</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- Add pagination controls here if implementing pagination -->
                <?php else: ?>
                <p>No products found in the system.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<?php
require_once 'includes/footer.php';
?>
