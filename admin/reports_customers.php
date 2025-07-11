<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php'; // Database connection

// Admin authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $_SESSION['admin_error'] = "Access denied.";
    header("Location: " . SITE_URL . "/login.php");
    exit;
}

$page_title = "Customer Reports";
require_once 'includes/header.php';

// Date Range Filtering
$date_from_cust = $_GET['date_from_cust'] ?? date('Y-m-01');
$date_to_cust = $_GET['date_to_cust'] ?? date('Y-m-d');
$date_to_cust_query = date('Y-m-d', strtotime($date_to_cust . ' +1 day'));


// --- New Customer Registrations Over Time ---
$sql_new_customers = "SELECT DATE(created_at) as registration_date, COUNT(id) as new_users_count
                      FROM users
                      WHERE created_at >= '" . escape_string($date_from_cust) . "'
                      AND created_at < '" . escape_string($date_to_cust_query) . "'
                      AND is_admin = 0 -- Exclude admin registrations from customer reports
                      GROUP BY DATE(created_at)
                      ORDER BY registration_date ASC";
$res_new_customers = mysqli_query($conn, $sql_new_customers);
$new_customer_data = [];
$new_customer_labels = [];
$new_customer_values = [];
if($res_new_customers){
    while($row = mysqli_fetch_assoc($res_new_customers)){
        $new_customer_data[] = $row; // For table display
        $new_customer_labels[] = date("M j", strtotime($row['registration_date']));
        $new_customer_values[] = $row['new_users_count'];
    }
}
$total_new_customers_period = array_sum($new_customer_values);


// --- Top Customers (by total spending in period) ---
$sql_top_customers_spending = "SELECT u.id, u.username, u.email,
                                      CONCAT(u.first_name, ' ', u.last_name) AS full_name,
                                      SUM(o.total_amount) as total_spent,
                                      COUNT(o.id) as total_orders
                               FROM users u
                               JOIN orders o ON u.id = o.user_id
                               WHERE o.status NOT IN ('cancelled', 'refunded')
                               AND o.order_date >= '" . escape_string($date_from_cust) . "'
                               AND o.order_date < '" . escape_string($date_to_cust_query) . "'
                               GROUP BY u.id, u.username, u.email, full_name
                               ORDER BY total_spent DESC
                               LIMIT 10"; // Top 10
$res_top_customers_spending = mysqli_query($conn, $sql_top_customers_spending);
$top_customers_spending = [];
if($res_top_customers_spending){
    while($row = mysqli_fetch_assoc($res_top_customers_spending)){
        $top_customers_spending[] = $row;
    }
}

// --- Top Customers (by number of orders in period) ---
$sql_top_customers_orders = "SELECT u.id, u.username, u.email,
                                    CONCAT(u.first_name, ' ', u.last_name) AS full_name,
                                    COUNT(o.id) as total_orders,
                                    SUM(o.total_amount) as total_spent_by_order_count
                             FROM users u
                             JOIN orders o ON u.id = o.user_id
                             WHERE o.status NOT IN ('cancelled', 'refunded')
                             AND o.order_date >= '" . escape_string($date_from_cust) . "'
                             AND o.order_date < '" . escape_string($date_to_cust_query) . "'
                             GROUP BY u.id, u.username, u.email, full_name
                             ORDER BY total_orders DESC
                             LIMIT 10"; // Top 10
$res_top_customers_orders = mysqli_query($conn, $sql_top_customers_orders);
$top_customers_orders = [];
if($res_top_customers_orders){
    while($row = mysqli_fetch_assoc($res_top_customers_orders)){
        $top_customers_orders[] = $row;
    }
}

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
</div>

<form method="GET" action="reports_customers.php" class="form-inline mb-4">
    <div class="form-group mr-2">
        <label for="date_from_cust" class="mr-1">From:</label>
        <input type="date" class="form-control" id="date_from_cust" name="date_from_cust" value="<?php echo htmlspecialchars($date_from_cust); ?>">
    </div>
    <div class="form-group mr-2">
        <label for="date_to_cust" class="mr-1">To:</label>
        <input type="date" class="form-control" id="date_to_cust" name="date_to_cust" value="<?php echo htmlspecialchars($date_to_cust); ?>">
    </div>
    <button type="submit" class="btn btn-primary">Filter Report</button>
</form>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">New Customer Registrations (<?php echo htmlspecialchars(date("M j, Y", strtotime($date_from_cust))) . " - " . htmlspecialchars(date("M j, Y", strtotime($date_to_cust))); ?>)</div>
            <div class="card-body">
                <h4>Total New Customers: <?php echo $total_new_customers_period; ?></h4>
                <?php if(!empty($new_customer_labels)): ?>
                <canvas id="newCustomersChart" style="max-height: 300px;"></canvas>
                <?php else: ?>
                <p>No new customer registrations in this period.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">Top 10 Customers (by Spending)</div>
            <div class="card-body">
                <?php if (!empty($top_customers_spending)): ?>
                <table class="table table-sm table-hover">
                    <thead><tr><th>Customer</th><th>Orders</th><th>Total Spent</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($top_customers_spending as $cust): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($cust['full_name'] ?: $cust['username']); ?><br>
                                <small class="text-muted"><?php echo htmlspecialchars($cust['email']); ?></small>
                            </td>
                            <td><?php echo $cust['total_orders']; ?></td>
                            <td>$<?php echo number_format($cust['total_spent'], 2); ?></td>
                            <td><a href="user_view.php?id=<?php echo $cust['id']; ?>" class="btn btn-xs btn-outline-secondary">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>No customer spending data for this period.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">Top 10 Customers (by Number of Orders)</div>
            <div class="card-body">
                <?php if (!empty($top_customers_orders)): ?>
                <table class="table table-sm table-hover">
                    <thead><tr><th>Customer</th><th>Orders</th><th>Total Spent</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($top_customers_orders as $cust): ?>
                         <tr>
                            <td>
                                <?php echo htmlspecialchars($cust['full_name'] ?: $cust['username']); ?><br>
                                <small class="text-muted"><?php echo htmlspecialchars($cust['email']); ?></small>
                            </td>
                            <td><?php echo $cust['total_orders']; ?></td>
                            <td>$<?php echo number_format($cust['total_spent_by_order_count'], 2); ?></td>
                            <td><a href="user_view.php?id=<?php echo $cust['id']; ?>" class="btn btn-xs btn-outline-secondary">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>No customer order count data for this period.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<!-- Include Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    <?php if(!empty($new_customer_labels) && !empty($new_customer_values)): ?>
    var ctxNewCustomers = document.getElementById('newCustomersChart').getContext('2d');
    var newCustomersChart = new Chart(ctxNewCustomers, {
        type: 'bar', // or 'line'
        data: {
            labels: <?php echo json_encode($new_customer_labels); ?>,
            datasets: [{
                label: 'New Customer Registrations',
                data: <?php echo json_encode($new_customer_values); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1 // Ensure y-axis shows whole numbers for counts
                    }
                }
            }
        }
    });
    <?php endif; ?>
});
</script>

<?php
require_once 'includes/footer.php';
?>
