<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';

// Admin authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $_SESSION['admin_error'] = "Access denied.";
    header("Location: " . SITE_URL . "login/");
    exit;
}

$affiliate_id = $_GET['id'] ?? null;
if (!$affiliate_id) {
    header("Location: " . SITE_URL . "admin/affiliates/");
    exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// We can choose to just delete the affiliate record.
// Commissions and referrals will remain, but will no longer be associated with an active affiliate.
// Alternatively, we could set the status to 'deleted' or handle related records.
// For simplicity, we will delete the affiliate record.

$stmt_delete = $conn->prepare("DELETE FROM affiliates WHERE id = ?");
$stmt_delete->bind_param("i", $affiliate_id);

if ($stmt_delete->execute()) {
    $_SESSION['success_message'] = "Affiliate deleted successfully.";
} else {
    $_SESSION['error_message'] = "Failed to delete affiliate. Please try again.";
}

header("Location: " . SITE_URL . "admin/affiliates/");
exit;
?>