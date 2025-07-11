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

$page_title = "Sales Reports";
require_once 'includes/header.php';

// Date Range Filtering
$today = date('Y-m-d');
$date_from = $_GET['date_from'] ?? date('Y-m-01'); // Default to start of current month
$date_to = $_GET['date_to'] ?? $today; // Default to today

// Ensure dates are valid to prevent SQL errors, add one day to date_to for inclusive range in queries
$date_to_query = date('Y-m-d', strtotime($date_to . ' +1 day'));


// --- Overall Sales Summary ---
$sql_total_sales = "SELECT SUM(total_amount) as total_revenue, COUNT(id) as total_orders
                    FROM orders
                    WHERE status NOT IN ('cancelled', 'refunded')
                    AND order_date >= '" . escape_string($date_from) . "'
                    AND order_date < '" . escape_string($date_to_query) . "'";
$res_total_sales = mysqli_query($conn, $sql_total_sales);
$total_sales_summary = mysqli_fetch_assoc($res_total_sales);

// --- Sales by Product ---
$sql_sales_by_product = "SELECT p.name as product_name, SUM(oi.quantity) as total_quantity_sold, SUM(oi.quantity * oi.price_at_purchase) as product_revenue
                         FROM order_items oi
                         JOIN products p ON oi.product_id = p.id
                         JOIN orders o ON oi.order_id = o.id
                         WHERE o.status NOT IN ('cancelled', 'refunded')
                         AND o.order_date >= '" . escape_string($date_from) . "'
                         AND o.order_date < '" . escape_string($date_to_query) . "'
                         GROUP BY p.id, p.name
                         ORDER BY product_revenue DESC
                         LIMIT 10"; // Top 10 products
$res_sales_by_product = mysqli_query($conn, $sql_sales_by_product);
$sales_by_product = [];
if($res_sales_by_product) {
    while($row = mysqli_fetch_assoc($res_sales_by_product)) {
        $sales_by_product[] = $row;
    }
}

// --- Sales by Category ---
$sql_sales_by_category = "SELECT cat.name as category_name, SUM(oi.quantity * oi.price_at_purchase) as category_revenue
                          FROM order_items oi
                          JOIN products p ON oi.product_id = p.id
                          JOIN categories cat ON p.category_id = cat.id
                          JOIN orders o ON oi.order_id = o.id
                          WHERE o.status NOT IN ('cancelled', 'refunded')
                          AND o.order_date >= '" . escape_string($date_from) . "'
                          AND o.order_date < '" . escape_string($date_to_query) . "'
                          GROUP BY cat.id, cat.name
                          ORDER BY category_revenue DESC";
$res_sales_by_category = mysqli_query($conn, $sql_sales_by_category);
$sales_by_category = [];
if($res_sales_by_category) {
    while($row = mysqli_fetch_assoc($res_sales_by_category)) {
        $sales_by_category[] = $row;
    }
}

// --- Daily/Monthly Sales (for chart - simplified example) ---
// For a real chart, you'd aggregate by day or month within the range
$sql_daily_sales = "SELECT DATE(order_date) as sale_date, SUM(total_amount) as daily_total
                    FROM orders
                    WHERE status NOT IN ('cancelled', 'refunded')
                    AND order_date >= '" . escape_string($date_from) . "'
                    AND order_date < '" . escape_string($date_to_query) . "'
                    GROUP BY DATE(order_date)
                    ORDER BY sale_date ASC";
$res_daily_sales = mysqli_query($conn, $sql_daily_sales);
$daily_sales_data = [];
$daily_sales_labels = [];
$daily_sales_values = [];
if($res_daily_sales){
    while($row = mysqli_fetch_assoc($res_daily_sales)){
        $daily_sales_data[] = $row; // For table display
        $daily_sales_labels[] = date("M j", strtotime($row['sale_date']));
        $daily_sales_values[] = $row['daily_total'];
    }
}

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
    <!-- Export options can be added here -->
</div>

<form method="GET" action="reports_sales.php" class="form-inline mb-4">
    <div class="form-group mr-2">
        <label for="date_from" class="mr-1">From:</label>
        <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
    </div>
    <div class="form-group mr-2">
        <label for="date_to" class="mr-1">To:</label>
        <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
    </div>
    <button type="submit" class="btn btn-primary">Filter Report</button>
</form>

<div class="row">
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">Total Revenue</div>
            <div class="card-body">
                <h3 class="card-title">$<?php echo number_format($total_sales_summary['total_revenue'] ?? 0, 2); ?></h3>
                <p class="card-text">From <?php echo (int)($total_sales_summary['total_orders'] ?? 0); ?> orders.</p>
            </div>
        </div>
    </div>
     <div class="col-md-6 col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">Average Order Value</div>
            <div class="card-body">
                <h3 class="card-title">$<?php
                $avg_order_value = ($total_sales_summary['total_orders'] ?? 0) > 0 ? ($total_sales_summary['total_revenue'] ?? 0) / $total_sales_summary['total_orders'] : 0;
                echo number_format($avg_order_value, 2);
                ?></h3>
                 <p class="card-text">Calculated from total revenue and orders.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card">
            <div class="card-header">Sales Trend (<?php echo htmlspecialchars(date("M j, Y", strtotime($date_from))) . " - " . htmlspecialchars(date("M j, Y", strtotime($date_to))); ?>)</div>
            <div class="card-body">
                <?php if(!empty($daily_sales_labels)): ?>
                <canvas id="salesTrendChart"></canvas>
                <?php else: ?>
                <p>No sales data available for the selected period to display a trend.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">Top Selling Products (by Revenue, Top 10)</div>
            <div class="card-body">
                <?php if (!empty($sales_by_product)): ?>
                <table class="table table-sm">
                    <thead><tr><th>Product</th><th>Qty Sold</th><th>Revenue</th></tr></thead>
                    <tbody>
                    <?php foreach ($sales_by_product as $prod_sale): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($prod_sale['product_name']); ?></td>
                            <td><?php echo $prod_sale['total_quantity_sold']; ?></td>
                            <td>$<?php echo number_format($prod_sale['product_revenue'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>No product sales data for this period.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">Sales by Category (by Revenue)</div>
            <div class="card-body">
                 <?php if (!empty($sales_by_category)): ?>
                <table class="table table-sm">
                    <thead><tr><th>Category</th><th>Revenue</th></tr></thead>
                    <tbody>
                    <?php foreach ($sales_by_category as $cat_sale): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cat_sale['category_name']); ?></td>
                            <td>$<?php echo number_format($cat_sale['category_revenue'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>No category sales data for this period.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    <?php if(!empty($daily_sales_labels) && !empty($daily_sales_values)): ?>
    var ctxSalesTrend = document.getElementById('salesTrendChart').getContext('2d');
    var salesTrendChart = new Chart(ctxSalesTrend, {
        type: 'line', // or 'bar'
        data: {
            labels: <?php echo json_encode($daily_sales_labels); ?>,
            datasets: [{
                label: 'Daily Sales Revenue',
                data: <?php echo json_encode($daily_sales_values); ?>,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Adjust as needed
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += '$' + context.parsed.y.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                            return label;
                        }
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
