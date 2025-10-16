<?php
session_start();
require_once '../config.php';
require_once '../includes/db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Please log in to proceed to checkout.";
    $_SESSION['message_type'] = "warning";
    header('Location: ' . SITE_URL . 'login/');
    exit;
}

$user_id = $_SESSION['user_id'];
$db = db_connect();

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address_id = $_POST['address_id'] ?? null;
    $cart = $_SESSION['cart'] ?? [];

    if (empty($cart)) {
        header('Location: ' . SITE_URL . 'cart/');
        exit;
    }

    if (empty($address_id)) {
        $_SESSION['message'] = "Please select a shipping address.";
        $_SESSION['message_type'] = "danger";
        header('Location: ' . SITE_URL . 'checkout/');
        exit;
    }

    // Verify address belongs to user
    $stmt = $db->prepare("SELECT id FROM addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$address_id, $user_id]);
    if ($stmt->rowCount() == 0) {
        $_SESSION['message'] = "Invalid address selected.";
        $_SESSION['message_type'] = "danger";
        header('Location: ' . SITE_URL . 'checkout/');
        exit;
    }

    // Fetch product details and calculate total
    $product_ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt_products = $db->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
    $stmt_products->execute($product_ids);
    $products = $stmt_products->fetchAll(PDO::FETCH_KEY_PAIR);

    $total_amount = 0;
    foreach ($cart as $product_id => $quantity) {
        if (isset($products[$product_id])) {
            $total_amount += $products[$product_id] * $quantity;
        }
    }

    try {
        $db->beginTransaction();

        // 1. Create order
        $payment_method = $_POST['payment_method'] ?? 'cod';
        $order_status = ($payment_method === 'cod') ? 'processing' : 'completed';

        $stmt_order = $db->prepare("INSERT INTO orders (user_id, address_id, total_amount, status, payment_method) VALUES (?, ?, ?, ?, ?)");
        $stmt_order->execute([$user_id, $address_id, $total_amount, $order_status, $payment_method]);
        $order_id = $db->lastInsertId();

        // 2. Create order items
        $stmt_items = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($cart as $product_id => $quantity) {
            if (isset($products[$product_id])) {
                $stmt_items->execute([$order_id, $product_id, $quantity, $products[$product_id]]);
            }
        }

        // 3. Commission calculation
        $stmt_referral = $db->prepare("SELECT affiliate_id FROM referrals WHERE referred_user_id = ?");
        $stmt_referral->execute([$user_id]);
        $referral = $stmt_referral->fetch(PDO::FETCH_ASSOC);

        if ($referral) {
            // Fetch commission rate from settings
            $stmt_rate = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'affiliate_commission_rate'");
            $stmt_rate->execute();
            $commission_rate_percent = $stmt_rate->fetchColumn();
            $commission_rate = is_numeric($commission_rate_percent) ? (float)$commission_rate_percent / 100 : 0.10; // Default to 10%

            $affiliate_id = $referral['affiliate_id'];
            $commission_amount = $total_amount * $commission_rate;

            $stmt_commission = $db->prepare("INSERT INTO commissions (affiliate_id, order_id, commission_amount, status) VALUES (?, ?, ?, 'pending')");
            $stmt_commission->execute([$affiliate_id, $order_id, $commission_amount]);
        }

        $db->commit();

        // 4. Clear cart and redirect to success page
        unset($_SESSION['cart']);
        $_SESSION['order_id'] = $order_id;
        header('Location: ' . SITE_URL . 'order_success/');
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['message'] = "There was an error processing your order. Please try again.";
        $_SESSION['message_type'] = "danger";
        header('Location: ' . SITE_URL . 'checkout/');
        exit;
    }
}


// Display checkout page
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    $_SESSION['message'] = "Your cart is empty.";
    $_SESSION['message_type'] = "info";
    header('Location: ' . SITE_URL . 'cart/');
    exit;
}

// Fetch addresses
$stmt_addr = $db->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC");
$stmt_addr->execute([$user_id]);
$addresses = $stmt_addr->fetchAll(PDO::FETCH_ASSOC);

// Fetch cart item details
$product_ids_cart = array_keys($cart);
$placeholders_cart = implode(',', array_fill(0, count($product_ids_cart), '?'));
$stmt_cart_items = $db->prepare("SELECT id, name, price, image_url_main as image, is_cod_available FROM products WHERE id IN ($placeholders_cart)");
$stmt_cart_items->execute($product_ids_cart);
$products_in_cart = $stmt_cart_items->fetchAll(PDO::FETCH_ASSOC);

$all_items_cod_available = true;
foreach ($products_in_cart as $product) {
    if (!$product['is_cod_available']) {
        $all_items_cod_available = false;
        break;
    }
}

$pageTitle = "Checkout";
include_once '../templates/header.php';
?>

<div class="container">
    <h1 class="my-4">Checkout</h1>
    <div class="row">
        <div class="col-md-8">
            <h4>Shipping Address</h4>
            <?php if (empty($addresses)): ?>
                <div class="alert alert-warning">
                    You have no saved addresses. Please <a href="<?php echo SITE_URL; ?>account/add_address/">add an address</a> before proceeding.
                </div>
            <?php else: ?>
                <form action="<?php echo SITE_URL; ?>checkout/index.php" method="POST">
                    <?php foreach ($addresses as $address): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <input type="radio" name="address_id" value="<?php echo $address['id']; ?>" id="address_<?php echo $address['id']; ?>" <?php echo $address['is_default'] ? 'checked' : ''; ?>>
                                <label for="address_<?php echo $address['id']; ?>">
                                    <strong><?php echo htmlspecialchars($address['address_line1']); ?></strong><br>
                                    <?php echo htmlspecialchars($address['city']); ?>, <?php echo htmlspecialchars($address['state']); ?> <?php echo htmlspecialchars($address['postal_code']); ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($all_items_cod_available): ?>
                        <h4 class="mt-4">Payment Method</h4>
                        <div class="card">
                            <div class="card-body">
                                <input type="radio" name="payment_method" value="cod" id="payment_cod" checked>
                                <label for="payment_cod">Cash on Delivery</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg mt-3">Place Order</button>
                    <?php else: ?>
                        <div class="alert alert-warning mt-4">
                            One or more items in your cart are not eligible for Cash on Delivery. Please remove them to use this payment method.
                        </div>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <h4>Order Summary</h4>
            <ul class="list-group mb-3">
                <?php
                $total = 0;
                foreach ($products_in_cart as $product) {
                    $quantity = $cart[$product['id']];
                    $subtotal = $product['price'] * $quantity;
                    $total += $subtotal;
                    echo '<li class="list-group-item d-flex justify-content-between lh-condensed">';
                    echo '<div><h6 class="my-0">' . htmlspecialchars($product['name']) . ' (x' . $quantity . ')</h6></div>';
                    echo '<span class="text-muted">$' . number_format($subtotal, 2) . '</span>';
                    echo '</li>';
                }
                ?>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Total (USD)</span>
                    <strong>$<?php echo number_format($total, 2); ?></strong>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php include_once '../templates/footer.php'; ?>