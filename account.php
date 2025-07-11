<?php
session_start();
require_once __DIR__ . '/includes/db.php'; // For potential future use

$page_title = "My Account";

// Check if user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'] ?? 'User';

require_once 'templates/header.php';
?>

<h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
<p>This is your account dashboard. From here you can manage your profile, view your order history, and more.</p>

<div class="list-group mt-4">
    <a href="profile_edit.php" class="list-group-item list-group-item-action">
        Edit Profile
    </a>
    <a href="order_history.php" class="list-group-item list-group-item-action">
        Order History
    </a>
    <a href="manage_addresses.php" class="list-group-item list-group-item-action">
        Manage Addresses
    </a>
    <a href="change_password.php" class="list-group-item list-group-item-action">
        Change Password
    </a>
    <a href="logout.php" class="list-group-item list-group-item-action text-danger">
        Logout
    </a>
</div>

<?php
// Placeholder for future content like recent orders, etc.
?>

<?php
require_once 'templates/footer.php';
?>
