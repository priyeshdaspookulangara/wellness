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
    <div class="row">
        <div class="col-md-3">
            <?php include_once 'includes/sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <h2 class="mt-5 mb-4">My Account Dashboard</h2>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION["user_name"]); ?>!</p>
            <p>From your account dashboard, you can view your recent orders, manage your shipping and billing addresses, and edit your password and account details.</p>
            <p>Select an option from the sidebar to get started.</p>
        </div>
    </div>
</div>

<?php
include_once '../templates/footer.php';
?>
