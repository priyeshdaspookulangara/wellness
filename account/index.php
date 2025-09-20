<?php
session_start();
require_once '../config.php';
require_once '../includes/db.php'; // Even if empty, for future-proofing

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["user_id"])) {
    header("location: " . SITE_URL . "/login");
    exit;
}

$pageTitle = "My Account";
include_once '../templates/header.php';
?>

<div class="container">
    <h2 class="mt-5 mb-4">My Account Dashboard</h2>
    <p>Welcome back, <?php echo htmlspecialchars($_SESSION["user_name"]); ?>!</p>
    <p>From your account dashboard you can view your recent orders, manage your shipping and billing addresses, and edit your password and account details.</p>

    <div class="list-group">
        <a href="<?php echo SITE_URL; ?>/order_history" class="list-group-item list-group-item-action">
            Order History
        </a>
        <a href="<?php echo SITE_URL; ?>/manage_addresses" class="list-group-item list-group-item-action">
            Manage Addresses
        </a>
        <a href="<?php echo SITE_URL; ?>/profile_edit" class="list-group-item list-group-item-action">
            Edit Profile & Password
        </a>
        <a href="<?php echo SITE_URL; ?>/logout.php" class="list-group-item list-group-item-action text-danger">
            Logout
        </a>
    </div>
</div>

<?php
include_once '../templates/footer.php';
?>
