<?php
session_start();
require_once __DIR__ . '/includes/db.php';

$page_title = "Order History";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Placeholder for fetching user's orders
$user_id = $_SESSION['user_id'];
// $sql = "SELECT id, order_date, total_amount, status FROM orders WHERE user_id = '$user_id' ORDER BY order_date DESC";
// ... fetch orders ...

require_once 'templates/header.php';
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
            <td><a href="order_details.php?id=1001" class="btn btn-sm btn-info">View Details</a></td>
        </tr>
        <tr>
            <td colspan="5" class="text-center">No orders found.</td>
        </tr>
    </tbody>
</table>


<?php
require_once 'templates/footer.php';
?>
