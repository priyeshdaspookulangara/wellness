<?php
// Get the current page's URI to determine active state
$request_uri = $_SERVER['REQUEST_URI'];

// Check if user is an affiliate to show the dashboard link
require_once __DIR__ . '/../../includes/db.php';
$is_affiliate = false;
if (isset($_SESSION['user_id'])) {
    try {
        $db = db_connect();
        $stmt = $db->prepare("SELECT id FROM affiliates WHERE user_id = ? AND status = 'active'");
        $stmt->execute([$_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $is_affiliate = true;
        }
    } catch (PDOException $e) {
        // If the database connection fails, we'll log the error and continue
        // without showing the affiliate link.
        error_log("Affiliate check failed in sidebar: " . $e->getMessage());
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
