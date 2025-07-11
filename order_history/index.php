<?php
session_start();
require_once __DIR__ . '/../config.php'; // Added
require_once __DIR__ . '/../includes/db.php'; // Corrected

$page_title = "Order History";

if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "login/"); // Updated redirect
    exit;
}

// Placeholder for fetching user's orders
$user_id = $_SESSION['user_id'];
// $sql = "SELECT id, order_date, total_amount, status FROM orders WHERE user_id = '$user_id' ORDER BY order_date DESC";
// ... fetch orders ...

require_once __DIR__ . '/../templates/header.php'; // Corrected
?>

<h1><?php echo $page_title; ?></h1>
<p>Here you can view your past orders.</p>

<table class="table table-striped">
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
        <!-- Example Row -->
        <tr>
            <td>#1001</td>
            <td>2023-10-26</td>
            <td>$79.99</td>
            <td>Shipped</td>
            <td><a href="<?php echo SITE_URL; ?>order_details/?id=1001" class="btn btn-sm btn-info">View Details</a></td> <!-- Assuming order_details/index.php will exist -->
        </tr>
        <tr>
            <td colspan="5" class="text-center">No orders found.</td>
        </tr>
    </tbody>
</table>


<?php
require_once __DIR__ . '/../templates/footer.php'; // Corrected
?>
