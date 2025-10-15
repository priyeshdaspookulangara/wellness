<?php
session_start();
require_once '../config.php';
require_once '../includes/db.php';
require_once '../templates/header.php';

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: ' . SITE_URL . 'cart/');
    exit();
}

// Fetch cart items from database for display
$cart_items = [];
$total_price = 0;
if (!empty($_SESSION['cart'])) {
    $product_ids = implode(',', array_keys($_SESSION['cart']));
    $stmt = db_connect()->prepare("SELECT * FROM products WHERE id IN ($product_ids)");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $product) {
        $quantity = $_SESSION['cart'][$product['id']];
        $cart_items[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'subtotal' => $product['price'] * $quantity,
        ];
        $total_price += $product['price'] * $quantity;
    }
}
?>

<div class="container my-5">
    <h2>Checkout</h2>
    <div class="row">
        <!-- Order Summary -->
        <div class="col-md-6">
            <h4>Order Summary</h4>
            <ul class="list-group mb-3">
                <?php foreach ($cart_items as $item): ?>
                    <li class="list-group-item d-flex justify-content-between lh-condensed">
                        <div>
                            <h6 class="my-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                            <small class="text-muted">Quantity: <?php echo $item['quantity']; ?></small>
                        </div>
                        <span class="text-muted">$<?php echo number_format($item['subtotal'], 2); ?></span>
                    </li>
                <?php endforeach; ?>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Total (USD)</span>
                    <strong>$<?php echo number_format($total_price, 2); ?></strong>
                </li>
            </ul>
        </div>

        <!-- Shipping Information -->
        <div class="col-md-6">
            <h4>Shipping Information</h4>
            <form action="process_order.php" method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" class="form-control" id="address" name="address" required>
                </div>
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" class="form-control" id="city" name="city" required>
                </div>
                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" class="form-control" id="state" name="state" required>
                </div>
                <div class="form-group">
                    <label for="zip">ZIP Code</label>
                    <input type="text" class="form-control" id="zip" name="zip" required>
                </div>
                <div class="form-group">
                    <label for="country">Country</label>
                    <input type="text" class="form-control" id="country" name="country" required>
                </div>
                <hr class="my-4">
                <h4>Payment Method</h4>
                <div class="d-block my-3">
                    <div class="custom-control custom-radio">
                        <input id="gpay" name="payment_method" type="radio" class="custom-control-input" value="gpay" checked required>
                        <label class="custom-control-label" for="gpay">Google Pay (Scan QR / UPI)</label>
                        <small class="form-text text-muted">Pay instantly using any UPI app. You will be shown a QR code and UPI ID after placing the order.</small>
                    </div>
                </div>
                <hr class="my-4">
                <button class="btn btn-primary btn-lg btn-block" type="submit">Place Order</button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>