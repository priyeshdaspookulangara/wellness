<?php
// Get the current page's URI to determine active state
$request_uri = $_SERVER['REQUEST_URI'];

// Check if user is an affiliate to show the dashboard link
$is_affiliate = false;
if (isset($_SESSION['user_id'])) {
    $conn_sidebar = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn_sidebar->connect_error) {
        error_log("Sidebar DB connection failed: " . $conn_sidebar->connect_error);
    } else {
        $stmt_sidebar = $conn_sidebar->prepare("SELECT id FROM affiliates WHERE user_id = ? AND status = 'active'");
        $stmt_sidebar->bind_param("i", $_SESSION['user_id']);
        $stmt_sidebar->execute();
        $result_sidebar = $stmt_sidebar->get_result();
        if ($result_sidebar->num_rows > 0) {
            $is_affiliate = true;
        }
        $stmt_sidebar->close();
        $conn_sidebar->close();
    }
}
?>
<div class="list-group">
    <a href="<?php echo SITE_URL; ?>account/order_history/" class="list-group-item list-group-item-action <?php echo (strpos($request_uri, 'order_history') !== false || strpos($request_uri, 'order_details') !== false) ? 'active' : ''; ?>">
        Order History
    </a>
    <a href="<?php echo SITE_URL; ?>account/manage_addresses/" class="list-group-item list-group-item-action <?php echo (strpos($request_uri, 'manage_addresses') !== false || strpos($request_uri, 'add_address') !== false || strpos($request_uri, 'edit_address') !== false) ? 'active' : ''; ?>">
        Manage Addresses
    </a>
    <a href="<?php echo SITE_URL; ?>account/profile_edit/" class="list-group-item list-group-item-action <?php echo (strpos($request_uri, 'profile_edit') !== false) ? 'active' : ''; ?>">
        Edit Profile & Password
    </a>
    <?php if ($is_affiliate): ?>
    <a href="<?php echo SITE_URL; ?>account/affiliate_dashboard/" class="list-group-item list-group-item-action <?php echo (strpos($request_uri, 'affiliate_dashboard') !== false) ? 'active' : ''; ?>">
        Affiliate Dashboard
    </a>
    <?php endif; ?>
    <a href="<?php echo SITE_URL; ?>logout.php" class="list-group-item list-group-item-action">
        Logout
    </a>
</div>
