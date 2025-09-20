<?php
session_start();
require_once '../config.php';
require_once '../includes/db.php';

if (!isset($_SESSION["user_id"])) {
    header("location: " . SITE_URL . "/login");
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id == 0) {
    header("location: " . SITE_URL . "/account/order_history.php");
    exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch order details
$stmt = $conn->prepare("SELECT o.*, a.address_line1, a.address_line2, a.city, a.state, a.postal_code, a.country FROM orders o LEFT JOIN addresses a ON o.address_id = a.id WHERE o.id = ? AND o.user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 1) {
    $order = $result->fetch_assoc();
} else {
    header("location: " . SITE_URL . "/account/order_history.php");
    exit;
}
$stmt->close();

// Fetch order items
$items_stmt = $conn->prepare("SELECT oi.quantity, oi.price, p.name as product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);
$items_stmt->close();
$conn->close();

$pageTitle = "Order Details";
include_once '../templates/header.php';
?>

<div class="container">
    <h2 class="mt-5 mb-4">Order Details for #<?php echo htmlspecialchars($order['id']); ?></h2>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Order Items</div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td>$<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></td>
                                    <td>$<?php echo htmlspecialchars(number_format($item['price'] * $item['quantity'], 2)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Order Summary</div>
                <div class="card-body">
                    <p><strong>Order Date:</strong> <?php echo htmlspecialchars(date("F j, Y", strtotime($order['order_date']))); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($order['status'])); ?></p>
                    <p><strong>Total Amount:</strong> $<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></p>
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-header">Shipping Address</div>
                <div class="card-body">
                    <p>
                        <?php echo htmlspecialchars($order['address_line1']); ?><br>
                        <?php if (!empty($order['address_line2'])) echo htmlspecialchars($order['address_line2']) . '<br>'; ?>
                        <?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['state']); ?> <?php echo htmlspecialchars($order['postal_code']); ?><br>
                        <?php echo htmlspecialchars($order['country']); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <a href="<?php echo SITE_URL; ?>/account/order_history.php" class="btn btn-primary mt-4">Back to Order History</a>
</div>

<?php
include_once '../templates/footer.php';
?>
