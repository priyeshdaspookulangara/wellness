<?php
session_start();
require_once __DIR__ . '/includes/db.php';

$page_title = "Change Password";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Placeholder for password change logic
$user_id = $_SESSION['user_id'];

require_once 'templates/header.php';
?>

<h1><?php echo $page_title; ?></h1>
<p>Use the form below to change your password.</p>

<form method="POST" action="change_password.php"> <!-- Action would point to itself or a handler -->
    <div class="form-group">
        <label for="current_password">Current Password</label>
        <input type="password" class="form-control" id="current_password" name="current_password" required>
    </div>
    <div class="form-group">
        <label for="new_password">New Password</label>
        <input type="password" class="form-control" id="new_password" name="new_password" required>
    </div>
    <div class="form-group">
        <label for="confirm_new_password">Confirm New Password</label>
        <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required>
    </div>
    <button type="submit" class="btn btn-primary">Change Password</button>
</form>


<?php
require_once 'templates/footer.php';
?>
