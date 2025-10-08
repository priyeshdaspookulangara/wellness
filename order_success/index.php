<?php
session_start();
require_once '../config.php';

// Check if user has just placed an order
if (!isset($_SESSION['order_id'])) {
    header('Location: ' . SITE_URL);
    exit;
}

$order_id = $_SESSION['order_id'];
unset($_SESSION['order_id']); // Unset the session variable to prevent re-visiting

$pageTitle = "Order Successful";
include_once '../templates/header.php';
?>

<div class="container text-center my-5">
    <div class="jumbotron">
        <h1 class="display-4">Thank You!</h1>
        <p class="lead">Your order has been placed successfully.</p>
        <hr class="my-4">
        <p>Your Order ID is: <strong><?php echo htmlspecialchars($order_id); ?></strong></p>
        <p>You will receive an email confirmation shortly.</p>
        <a class="btn btn-primary btn-lg" href="<?php echo SITE_URL; ?>account/order_history/" role="button">View Order History</a>
    </div>
</div>

<?php
include_once '../templates/footer.php';
?>