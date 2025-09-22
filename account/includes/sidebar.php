<?php
// Get the current page's URI to determine active state
$request_uri = $_SERVER['REQUEST_URI'];
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
    <a href="<?php echo SITE_URL; ?>logout.php" class="list-group-item list-group-item-action">
        Logout
    </a>
</div>
