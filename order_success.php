<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php'; // For potential future use, like fetching order details to display

$page_title = "Order Successful";

$order_id = (int)($_GET['order_id'] ?? 0);

if (!$order_id) {
    // If no order ID, maybe redirect to homepage or account
    header("Location: index.php");
    exit;
}

// Optional: Fetch some order details to display a summary
$order_details = null;
$sql_order = "SELECT id, total_amount, customer_email FROM orders WHERE id = $order_id";
// If user is logged in, you might want to also check if this order_id belongs to them:
// if (isset($_SESSION['user_id'])) { $sql_order .= " AND user_id = " . (int)$_SESSION['user_id']; }
$result_order = mysqli_query($conn, $sql_order);
if ($result_order && mysqli_num_rows($result_order) > 0) {
    $order_details = mysqli_fetch_assoc($result_order);
} else {
    // Order not found or doesn't belong to user (if checked)
    // Redirect or show a generic message
    // For now, we'll allow viewing if ID is present, assuming it's from a fresh checkout.
}


require_once 'templates/header.php';
?>

<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <i class="fas fa-check-circle fa-5x text-success mb-4"></i>
            <h1 class="display-4">Thank You!</h1>

            <?php if ($order_details): ?>
                <p class="lead">Your order #<?php echo htmlspecialchars($order_details['id']); ?> has been placed successfully.</p>
                <p>A confirmation email will be sent to <strong><?php echo htmlspecialchars($order_details['customer_email']); ?></strong> shortly (not really, this is a demo!).</p>
                <p>Your order total was: <strong>$<?php echo number_format($order_details['total_amount'], 2); ?></strong></p>
            <?php else: ?>
                 <p class="lead">Your order has been placed successfully.</p>
                 <p>You should receive a confirmation email shortly (not really, this is a demo!).</p>
            <?php endif; ?>

            <hr class="my-4">

            <p>
                You can view your order details in your <a href="account.php">account dashboard</a> if you are registered,
                or keep your order ID (<?php echo htmlspecialchars($order_id); ?>) for reference.
            </p>
            <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-primary btn-lg mt-3">Continue Shopping</a>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
