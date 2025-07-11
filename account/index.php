<?php
session_start();
require_once __DIR__ . '/../includes/db.php'; // Adjusted path

$page_title = "My Account";

// Check if user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "login/"); // Updated redirect
    exit;
}

$username = $_SESSION['username'] ?? 'User';

require_once __DIR__ . '/../templates/header.php'; // Adjusted path
?>

<h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
<p>This is your account dashboard. From here you can manage your profile, view your order history, and more.</p>

<div class="list-group mt-4">
    <a href="<?php echo SITE_URL; ?>profile_edit/" class="list-group-item list-group-item-action"> <!-- Updated link -->
        Edit Profile
    </a>
    <a href="<?php echo SITE_URL; ?>order_history/" class="list-group-item list-group-item-action"> <!-- Updated link -->
        Order History
    </a>
    <a href="<?php echo SITE_URL; ?>manage_addresses/" class="list-group-item list-group-item-action"> <!-- Updated link -->
        Manage Addresses
    </a>
    <a href="<?php echo SITE_URL; ?>change_password/" class="list-group-item list-group-item-action"> <!-- Updated link -->
        Change Password
    </a>
    <a href="<?php echo SITE_URL; ?>logout.php" class="list-group-item list-group-item-action text-danger"> <!-- logout.php is in root -->
        Logout
    </a>
</div>

<?php
// Placeholder for future content like recent orders, etc.
?>

<?php
require_once __DIR__ . '/../templates/footer.php'; // Adjusted path
?>
