<?php
session_start();
require_once '../config.php';

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["user_id"])) {
    header("location: " . SITE_URL . "login/");
    exit;
}

$pageTitle = "My Account";
include_once '../templates/header.php';
?>

<div class="container">
    <h2 class="mt-5 mb-4">My Account Dashboard</h2>
    <p>Welcome back, <?php echo htmlspecialchars($_SESSION["user_name"]); ?>!</p>
    <p>From your account dashboard, you can view your recent orders, manage your shipping and billing addresses, and edit your password and account details.</p>

    <div class="row">
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-box-open fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Order History</h5>
                    <p class="card-text">View your past orders and their status.</p>
                    <a href="<?php echo SITE_URL; ?>account/order_history.php" class="btn btn-primary">View Orders</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-map-marker-alt fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Manage Addresses</h5>
                    <p class="card-text">Add, edit, or delete your shipping addresses.</p>
                    <a href="<?php echo SITE_URL; ?>account/manage_addresses.php" class="btn btn-primary">Manage Addresses</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-user-edit fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Edit Profile</h5>
                    <p class="card-text">Update your name, email, and password.</p>
                    <a href="<?php echo SITE_URL; ?>account/profile_edit.php" class="btn btn-primary">Edit Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once '../templates/footer.php';
?>
