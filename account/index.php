<?php
session_start();
include_once '../config.php';

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "/login");
    exit();
}

// Fetch user details from the database
$user_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$pageTitle = 'My Account';
include_once '../templates/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <div class="list-group">
            <a href="<?php echo SITE_URL; ?>/account" class="list-group-item list-group-item-action active">
                Account Dashboard
            </a>
            <a href="<?php echo SITE_URL; ?>/order_history" class="list-group-item list-group-item-action">
                Order History
            </a>
            <a href="<?php echo SITE_URL; ?>/profile_edit" class="list-group-item list-group-item-action">
                Edit Profile
            </a>
            <a href="<?php echo SITE_URL; ?>/manage_addresses" class="list-group-item list-group-item-action">
                Manage Addresses
            </a>
            <a href="<?php echo SITE_URL; ?>/logout.php" class="list-group-item list-group-item-action">
                Logout
            </a>
        </div>
    </div>
    <div class="col-md-9">
        <h2>Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</h2>
        <p>This is your account dashboard. You can manage your orders, profile, and addresses from here.</p>
        <hr>
        <h4>Account Details</h4>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    </div>
</div>

<?php include_once '../templates/footer.php'; ?>
