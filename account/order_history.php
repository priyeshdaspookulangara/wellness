<?php
session_start();
require_once '../config.php';

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["user_id"])) {
    header("location: " . SITE_URL . "login/");
    exit;
}

$pageTitle = "Order History";
include_once '../templates/header.php';

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION["user_id"];

// Fetch orders for the current user
$sql = "SELECT id, order_date, total_amount, status FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<div class="container">
    <h2 class="mt-5 mb-4">Order History</h2>
    <?php if (count($orders) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars(date("F j, Y", strtotime($order['order_date']))); ?></td>
                            <td>$<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($order['status'])); ?></td>
                            <td>
                                <a href="<?php echo SITE_URL; ?>account/order_details.php?order_id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            You have not placed any orders yet. <a href="<?php echo SITE_URL; ?>">Browse products</a>.
        </div>
    <?php endif; ?>
</div>

<?php
include_once '../templates/footer.php';
?>
