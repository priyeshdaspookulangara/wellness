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
    $stmt = $db->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $total_amount = 0;
    foreach ($cart as $product_id => $quantity) {
        if (isset($products[$product_id])) {
            $total_amount += $products[$product_id] * $quantity;
        }
    }

    try {
        $db->beginTransaction();

        // 1. Create order
        $stmt = $db->prepare("INSERT INTO orders (user_id, address_id, total_amount, status) VALUES (?, ?, ?, 'completed')");
        $stmt->execute([$user_id, $address_id, $total_amount]);
        $order_id = $db->lastInsertId();

        // 2. Create order items
        $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($cart as $product_id => $quantity) {
            if (isset($products[$product_id])) {
                $stmt->execute([$order_id, $product_id, $quantity, $products[$product_id]]);
            }
        }

        // 3. Commission calculation
        $stmt = $db->prepare("SELECT affiliate_id FROM referrals WHERE referred_user_id = ?");
        $stmt->execute([$user_id]);
        $referral = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($referral) {
            $affiliate_id = $referral['affiliate_id'];
            // 10% commission rate
            $commission_amount = $total_amount * 0.10;
            $stmt = $db->prepare("INSERT INTO commissions (affiliate_id, order_id, commission_amount, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$affiliate_id, $order_id, $commission_amount]);
        }

        $db->commit();

        // 4. Clear cart and redirect to success page
        unset($_SESSION['cart']);
        $_SESSION['order_id'] = $order_id;
        header('Location: ' . SITE_URL . 'order_success/');
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['message'] = "There was an error processing your order. Please try again. " . $e->getMessage();
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
$stmt = $db->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch cart item details
$product_ids = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($product_ids), '?'));
$stmt = $db->prepare("SELECT id, name, price, image FROM products WHERE id IN ($placeholders)");
$stmt->execute($product_ids);
$products_in_cart = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                <form action="" method="POST">
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
                    <button type="submit" class="btn btn-primary btn-lg mt-3">Place Order</button>
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