<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

$page_title = "Order Details";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "login/");
    exit;
}
$user_id = (int)$_SESSION['user_id'];

// Get order ID from URL
$order_id = (int)($_GET['id'] ?? 0);
if (!$order_id) {
    // If no order ID, redirect to order history
    header("Location: " . SITE_URL . "order_history/");
    exit;
}

// Fetch order details, ensuring it belongs to the logged-in user
$sql_order = "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id";
$result_order = mysqli_query($conn, $sql_order);

if (!$result_order || mysqli_num_rows($result_order) === 0) {
    // Order not found or doesn't belong to this user
    $_SESSION['error_message'] = "Order not found or you do not have permission to view it.";
    header("Location: " . SITE_URL . "order_history/");
    exit;
}
$order = mysqli_fetch_assoc($result_order);
$page_title = "Details for Order #" . $order['id'];

// Fetch order items
$sql_items = "SELECT oi.*, p.slug AS product_slug
              FROM order_items oi
              LEFT JOIN products p ON oi.product_id = p.id
              WHERE oi.order_id = $order_id";
$result_items = mysqli_query($conn, $sql_items);
$order_items = [];
if ($result_items) {
    while ($item = mysqli_fetch_assoc($result_items)) {
        $order_items[] = $item;
    }
}

require_once __DIR__ . '/../templates/header.php';
?>

<div class="container py-5">
    <h1 class="mb-4"><?php echo $page_title; ?></h1>
    <p><a href="<?php echo SITE_URL; ?>order_history/">&laquo; Back to Order History</a></p>

    <div class="card">
        <div class="card-header">
            Order placed on <?php echo date("F j, Y", strtotime($order['order_date'])); ?> |
            Status: <span class="fw-bold"><?php echo ucfirst(htmlspecialchars($order['status'])); ?></span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Shipping Address</h5>
                    <p>
                        <?php echo htmlspecialchars($order['shipping_first_name'] . ' ' . $order['shipping_last_name']); ?><br>
                        <?php echo htmlspecialchars($order['shipping_address_street']); ?><br>
                        <?php echo htmlspecialchars($order['shipping_address_city']); ?>, <?php echo htmlspecialchars($order['shipping_address_state']); ?> <?php echo htmlspecialchars($order['shipping_address_zip']); ?><br>
                        <?php echo htmlspecialchars($order['shipping_address_country']); ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <h5>Order Summary</h5>
                    <p>
                        <strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?><br>
                        <strong>Subtotal:</strong> $<?php echo number_format($order['total_amount'] - $order['shipping_cost'], 2); ?><br>
                        <strong>Shipping:</strong> $<?php echo number_format($order['shipping_cost'], 2); ?><br>
                        <strong>Total:</strong> $<?php echo number_format($order['total_amount'], 2); ?>
                    </p>
                </div>
            </div>

            <h5 class="mt-4">Items in this Order</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td><a href="<?php echo SITE_URL; ?>product/?slug=<?php echo htmlspecialchars($item['product_slug']); ?>"><?php echo htmlspecialchars($item['product_name_at_purchase']); ?></a></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['price_at_purchase'], 2); ?></td>
                                <td>$<?php echo number_format($item['price_at_purchase'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
