<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

// Admin authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $_SESSION['admin_error'] = "Access denied.";
    header("Location: " . SITE_URL . "/login.php");
    exit;
}

// CSRF token validation would be good here

$user_id_to_delete = (int)($_GET['id'] ?? 0);

if (!$user_id_to_delete) {
    $_SESSION['error_message'] = "Invalid user ID for deletion.";
    header("Location: users.php");
    exit;
}

// Prevent deleting oneself
if ($user_id_to_delete == $_SESSION['user_id']) {
    $_SESSION['error_message'] = "You cannot delete your own account.";
    header("Location: users.php");
    exit;
}

// Fetch user details to check if they are an admin
$sql_get_user = "SELECT is_admin FROM users WHERE id = $user_id_to_delete";
$result_get_user = mysqli_query($conn, $sql_get_user);

if ($result_get_user && mysqli_num_rows($result_get_user) > 0) {
    $user_to_delete_data = mysqli_fetch_assoc($result_get_user);
    if ($user_to_delete_data['is_admin']) {
        $_SESSION['error_message'] = "Admin accounts cannot be deleted through this interface for security reasons.";
        header("Location: users.php");
        exit;
    }
} else {
    $_SESSION['error_message'] = "User not found.";
    header("Location: users.php");
    exit;
}

// Before deleting user, consider what to do with their related data:
// - Orders: orders.user_id is ON DELETE SET NULL. So orders will remain, but become "guest" orders. This is often acceptable.
// - Reviews: reviews.user_id is ON DELETE CASCADE. So user's reviews will be deleted. This is also often acceptable.
// - Blog Posts (if user was an author): blog_posts.user_id is ON DELETE SET NULL. Author becomes NULL.

$sql_delete_user = "DELETE FROM users WHERE id = $user_id_to_delete";

if (mysqli_query($conn, $sql_delete_user)) {
    if (mysqli_affected_rows($conn) > 0) {
        $_SESSION['success_message'] = "User (ID: $user_id_to_delete) deleted successfully. Associated orders are now guest orders, and their reviews have been removed.";
    } else {
        $_SESSION['error_message'] = "User (ID: $user_id_to_delete) not found or already deleted.";
    }
} else {
    $_SESSION['error_message'] = "Failed to delete user (ID: $user_id_to_delete). Error: " . mysqli_error($conn);
}

header("Location: users.php");
exit;
?>
