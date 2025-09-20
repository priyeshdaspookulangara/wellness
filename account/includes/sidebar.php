<?php
// Get the current page's filename
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="list-group">
    <a href="<?php echo SITE_URL; ?>account/order_history.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'order_history.php' || $current_page == 'order_details.php') ? 'active' : ''; ?>">
        Order History
    </a>
    <a href="<?php echo SITE_URL; ?>account/manage_addresses.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'manage_addresses.php' || $current_page == 'add_address.php' || $current_page == 'edit_address.php') ? 'active' : ''; ?>">
        Manage Addresses
    </a>
    <a href="<?php echo SITE_URL; ?>account/profile_edit.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'profile_edit.php') ? 'active' : ''; ?>">
        Edit Profile & Password
    </a>
    <a href="<?php echo SITE_URL; ?>logout.php" class="list-group-item list-group-item-action">
        Logout
    </a>
</div>
