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

$page_title = "Manage Orders";
require_once 'includes/header.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $order_id_update = (int)($_POST['order_id'] ?? 0);
    $new_status = escape_string($_POST['status'] ?? '');
    $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];

    if ($order_id_update > 0 && in_array($new_status, $valid_statuses)) {
        $sql_update_status = "UPDATE orders SET status = '$new_status', updated_at = NOW() WHERE id = $order_id_update";
        if (mysqli_query($conn, $sql_update_status)) {
            $_SESSION['success_message'] = "Order #$order_id_update status updated to '$new_status'.";
        } else {
            $_SESSION['error_message'] = "Failed to update order status: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error_message'] = "Invalid order ID or status for update.";
    }
    // Redirect to same page to prevent form resubmission
    header("Location: orders.php" . (isset($_GET['filter_status']) ? "?filter_status=".$_GET['filter_status'] : ""));
    exit;
}


// Filtering
$filter_status = $_GET['filter_status'] ?? '';
$where_clause = '';
if (!empty($filter_status)) {
    $status_esc = escape_string($filter_status);
    $where_clause = "WHERE o.status = '$status_esc'";
}


// Fetch orders from database
$sql_orders = "SELECT o.id, o.order_date, o.total_amount, o.status, o.customer_email,
                      u.username AS user_username,
                      CONCAT(o.shipping_first_name, ' ', o.shipping_last_name) AS shipping_name
               FROM orders o
               LEFT JOIN users u ON o.user_id = u.id
               $where_clause
               ORDER BY o.order_date DESC";
$result_orders = mysqli_query($conn, $sql_orders);
$orders = [];
if ($result_orders) {
    while ($row = mysqli_fetch_assoc($result_orders)) {
        $orders[] = $row;
    }
} else {
    echo "<div class='alert alert-danger'>Error fetching orders: " . mysqli_error($conn) . "</div>";
}

$all_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];

// Handle messages
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
    <!-- Any buttons like "Export Orders" could go here -->
</div>

<?php if ($success_message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="mb-3">
    <form action="orders.php" method="GET" class="form-inline">
        <label for="filter_status" class="mr-2">Filter by Status:</label>
        <select name="filter_status" id="filter_status" class="form-control mr-2">
            <option value="">All Statuses</option>
            <?php foreach ($all_statuses as $status_option): ?>
                <option value="<?php echo $status_option; ?>" <?php echo ($filter_status == $status_option) ? 'selected' : ''; ?>>
                    <?php echo ucfirst($status_option); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
         <?php if (!empty($filter_status)): ?>
            <a href="orders.php" class="btn btn-outline-secondary ml-2">Clear Filter</a>
        <?php endif; ?>
    </form>
</div>


<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Customer</th> <!-- Email or Username -->
                <th>Shipping Name</th>
                <th>Total</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                        <td><?php echo date("M j, Y, g:i a", strtotime($order['order_date'])); ?></td>
                        <td>
                            <?php echo htmlspecialchars($order['customer_email']); ?>
                            <?php if ($order['user_username']): ?>
                                <br><small class="text-muted">(<?php echo htmlspecialchars($order['user_username']); ?>)</small>
                            <?php else: ?>
                                <br><small class="text-muted">(Guest)</small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($order['shipping_name']); ?></td>
                        <td>$<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                        <td>
                            <form action="orders.php<?php echo !empty($filter_status) ? '?filter_status='.$filter_status : ''; ?>" method="POST" class="form-inline">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                                    <?php foreach ($all_statuses as $status_val): ?>
                                        <option value="<?php echo $status_val; ?>" <?php echo ($order['status'] == $status_val) ? 'selected' : ''; ?>>
                                            <?php echo ucfirst($status_val); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <!-- <button type="submit" class="btn btn-sm btn-outline-secondary ml-1">Update</button> -->
                            </form>
                        </td>
                        <td class="action-buttons">
                            <a href="order_view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info" title="View Details"><i class="fas fa-eye"></i></a>
                            <!-- Add other actions like print invoice, etc. -->
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No orders found<?php echo !empty($filter_status) ? ' matching this status' : ''; ?>.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Simple pagination placeholder
// echo "<p>Pagination will go here if many orders.</p>";
?>

<?php
require_once 'includes/footer.php';
?>
