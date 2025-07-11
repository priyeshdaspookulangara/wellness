<?php
session_start();
require_once __DIR__ . '/includes/db.php';

$page_title = "Edit Profile";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Placeholder for user data fetching and update logic
$user_id = $_SESSION['user_id'];
// $sql = "SELECT first_name, last_name, email, address_street, address_city, ... FROM users WHERE id = '$user_id'";
// ... fetch user data ...

require_once 'templates/header.php';
?>

<h1><?php echo $page_title; ?></h1>
<p>This page will allow users to update their profile information.</p>
<form>
    <div class="form-group">
        <label for="first_name">First Name</label>
        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php /* echo htmlspecialchars($user['first_name'] ?? ''); */ ?>">
    </div>
    <div class="form-group">
        <label for="last_name">Last Name</label>
        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php /* echo htmlspecialchars($user['last_name'] ?? ''); */ ?>">
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="<?php /* echo htmlspecialchars($user['email'] ?? ''); */ ?>" readonly>
        <small>Email cannot be changed here for security reasons.</small>
    </div>
    <!-- Add more fields for address etc. -->
    <button type="submit" class="btn btn-primary">Save Changes</button>
</form>

<?php
require_once 'templates/footer.php';
?>
