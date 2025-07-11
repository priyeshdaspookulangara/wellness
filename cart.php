<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php'; // For product details if needed, and escape_string

$page_title = "Shopping Cart";
require_once 'templates/header.php';

// Initialize cart if not already set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart_items = $_SESSION['cart'];
$cart_products_details = [];
$subtotal = 0;
$total_items = 0;

if (!empty($cart_items)) {
    // Get product IDs from cart to fetch details
    $product_ids = array_keys($cart_items);
    $ids_string = implode(',', array_map('intval', $product_ids)); // Securely create ID list

    if (!empty($ids_string)) {
        $sql = "SELECT id, name, price, image_url_main, slug, stock FROM products WHERE id IN ($ids_string)";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            while ($product = mysqli_fetch_assoc($result)) {
                $quantity_in_cart = $cart_items[$product['id']]['quantity'];
                $product['quantity_in_cart'] = $quantity_in_cart;
                $product['line_total'] = $product['price'] * $quantity_in_cart;
                $subtotal += $product['line_total'];
                $total_items += $quantity_in_cart;
                $cart_products_details[$product['id']] = $product;
            }
        } else {
            echo "<div class='alert alert-danger'>Error fetching product details: " . mysqli_error($conn) . "</div>";
        }
    }
}
// For display, ensure items are shown in the order they were added or a consistent order
// Re-order $cart_products_details based on $_SESSION['cart'] order if necessary, though current loop by product_ids from DB is usually fine.

$shipping_cost = 5.00; // Example flat rate shipping
$grand_total = $subtotal + $shipping_cost;

?>

<h1 class="mb-4"><?php echo $page_title; ?></h1>

<?php if (isset($_SESSION['cart_message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['cart_message_type'] ?? 'info'; ?>">
        <?php echo htmlspecialchars($_SESSION['cart_message']); ?>
    </div>
    <?php unset($_SESSION['cart_message'], $_SESSION['cart_message_type']); ?>
<?php endif; ?>

<?php if (!empty($cart_products_details)): ?>
    <div class="row">
        <div class="col-md-8">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width:10%;">Image</th>
                        <th style="width:35%;">Product</th>
                        <th style="width:15%;">Price</th>
                        <th style="width:15%;">Quantity</th>
                        <th style="width:15%;">Total</th>
                        <th style="width:10%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_products_details as $item): ?>
                    <tr>
                        <td>
                            <a href="<?php echo SITE_URL . '/product/' . htmlspecialchars($item['slug']); ?>">
                                <img src="<?php echo SITE_URL . '/uploads/' . htmlspecialchars($item['image_url_main'] ?? 'default.png'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-fluid" style="max-height: 75px;">
                            </a>
                        </td>
                        <td>
                            <a href="<?php echo SITE_URL . '/product/' . htmlspecialchars($item['slug']); ?>">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </a>
                            <?php if ($item['quantity_in_cart'] > $item['stock']): ?>
                                <br><small class="text-danger">Not enough stock! Available: <?php echo $item['stock']; ?></small>
                            <?php endif; ?>
                        </td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>
                            <form action="cart_actions.php" method="POST" class="form-inline">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <input type="number" name="quantity" value="<?php echo $item['quantity_in_cart']; ?>" min="1" max="<?php echo $item['stock']; ?>" class="form-control form-control-sm" style="width: 60px;">
                                <button type="submit" class="btn btn-sm btn-outline-secondary ml-1" title="Update Quantity"><i class="fas fa-sync-alt"></i></button>
                            </form>
                        </td>
                        <td>$<?php echo number_format($item['line_total'], 2); ?></td>
                        <td>
                            <form action="cart_actions.php" method="POST">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Remove Item"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-outline-secondary"><i class="fas fa-shopping-bag"></i> Continue Shopping</a>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Order Summary</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Subtotal (<?php echo $total_items; ?> items)
                            <span>$<?php echo number_format($subtotal, 2); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Shipping
                            <span>$<?php echo number_format($shipping_cost, 2); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center font-weight-bold">
                            Grand Total
                            <span>$<?php echo number_format($grand_total, 2); ?></span>
                        </li>
                    </ul>
                    <a href="checkout.php" class="btn btn-primary btn-block mt-3 <?php echo $total_items == 0 ? 'disabled' : ''; ?>">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        Your shopping cart is empty. <a href="<?php echo SITE_URL; ?>/index.php">Start shopping now!</a>
    </div>
<?php endif; ?>

<?php
require_once 'templates/footer.php';
?>
