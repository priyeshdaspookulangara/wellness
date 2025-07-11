<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

// Admin authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $_SESSION['admin_error'] = "Access denied.";
    header("Location: " . SITE_URL . "/login.php");
    exit;
}

$order_id = (int)($_GET['id'] ?? 0);
if (!$order_id) {
    $_SESSION['error_message'] = "Invalid order ID.";
    header("Location: orders.php");
    exit;
}

$page_title = "View Order #" . $order_id;
require_once 'includes/header.php';

// Fetch order details
$sql_order = "SELECT o.*, u.username AS user_username
              FROM orders o
              LEFT JOIN users u ON o.user_id = u.id
              WHERE o.id = $order_id";
$result_order = mysqli_query($conn, $sql_order);

if (!$result_order || mysqli_num_rows($result_order) === 0) {
    echo "<div class='alert alert-danger'>Order not found.</div>";
    require_once 'includes/footer.php';
    exit;
}
$order = mysqli_fetch_assoc($result_order);

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

$all_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="orders.php" class="btn btn-sm btn-outline-secondary mr-2">Back to Orders</a>
        <!-- <a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-print"></i> Print Invoice</a> -->
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">Order Items</div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price at Purchase</th>
                            <th>Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td>
                                <a href="<?php echo SITE_URL . '/product.php?slug=' . htmlspecialchars($item['product_slug'] ?? $item['product_id']); ?>" target="_blank"> <!-- Assuming product_slug is available, otherwise fallback to ID which might not work if product.php expects slug -->
                                    <?php echo htmlspecialchars($item['product_name_at_purchase']); ?>
                                </a>
                                <br><small class="text-muted">Product ID: <?php echo $item['product_id']; ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td>$<?php echo htmlspecialchars(number_format($item['price_at_purchase'], 2)); ?></td>
                            <td>$<?php echo htmlspecialchars(number_format($item['price_at_purchase'] * $item['quantity'], 2)); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (!empty($order['notes'])): ?>
        <div class="card mb-4">
            <div class="card-header">Customer Notes</div>
            <div class="card-body">
                <p><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">Order Summary</div>
            <div class="card-body">
                <p><strong>Order Date:</strong> <?php echo date("F j, Y, g:i a", strtotime($order['order_date'])); ?></p>
                <p><strong>Status:</strong> <span class="badge badge-info"><?php echo ucfirst(htmlspecialchars($order['status'])); ?></span></p>
                <hr>
                <p><strong>Subtotal:</strong> $<?php echo number_format($order['total_amount'] - $order['shipping_cost'], 2); // Approximate, assuming total includes shipping ?></p>
                <p><strong>Shipping Cost:</strong> $<?php echo number_format($order['shipping_cost'], 2); ?></p>
                <p><strong>Total Amount:</strong> $<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></p>
                <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                <p><strong>Payment Status:</strong> <?php echo ucfirst(htmlspecialchars($order['payment_status'])); ?></p>

                <form action="orders.php" method="POST" class="mt-3">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <div class="form-group">
                        <label for="status"><strong>Update Order Status:</strong></label>
                        <select name="status" id="status" class="form-control">
                            <?php foreach ($all_statuses as $status_val): ?>
                                <option value="<?php echo $status_val; ?>" <?php echo ($order['status'] == $status_val) ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($status_val); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Update Status</button>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Customer & Shipping Details</div>
            <div class="card-body">
                <p>
                    <strong>Customer:</strong>
                    <?php echo htmlspecialchars($order['customer_email']); ?>
                    <?php if ($order['user_username']): ?>
                        (Account: <a href="user_view.php?id=<?php echo $order['user_id']; ?>"><?php echo htmlspecialchars($order['user_username']); ?></a>)
                    <?php else: ?>
                        (Guest)
                    <?php endif; ?>
                </p>
                <?php if (!empty($order['customer_phone'])): ?>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                <?php endif; ?>
                <hr>
                <h5>Shipping Address</h5>
                <p>
                    <?php echo htmlspecialchars($order['shipping_first_name'] . ' ' . $order['shipping_last_name']); ?><br>
                    <?php echo htmlspecialchars($order['shipping_address_street']); ?><br>
                    <?php if(!empty($order['shipping_address_apartment'])) echo htmlspecialchars($order['shipping_address_apartment']) . '<br>'; ?>
                    <?php echo htmlspecialchars($order['shipping_address_city']); ?>, <?php echo htmlspecialchars($order['shipping_address_state']); ?> <?php echo htmlspecialchars($order['shipping_address_zip']); ?><br>
                    <?php echo htmlspecialchars($order['shipping_address_country']); ?>
                </p>
                 <p><strong>Shipping Method:</strong> <?php echo htmlspecialchars($order['shipping_method']); ?></p>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
