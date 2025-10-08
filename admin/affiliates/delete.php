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

$db = db_connect();

// We can choose to just delete the affiliate record.
// Commissions and referrals will remain, but will no longer be associated with an active affiliate.
// Alternatively, we could set the status to 'deleted' or handle related records.
// For simplicity, we will delete the affiliate record.

$sql_delete = "DELETE FROM affiliates WHERE id = ?";
$stmt_delete = $db->prepare($sql_delete);

if ($stmt_delete->execute([$affiliate_id])) {
    $_SESSION['success_message'] = "Affiliate deleted successfully.";
} else {
    $_SESSION['error_message'] = "Failed to delete affiliate. Please try again.";
}

header("Location: " . SITE_URL . "admin/affiliates/");
exit;
?>