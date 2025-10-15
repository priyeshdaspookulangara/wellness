<?php
session_start();
require_once '../config.php';
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL);
    exit();
}

$order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$transaction_id = filter_input(INPUT_POST, 'transaction_id', FILTER_SANITIZE_STRING);

if (!$order_id || !$transaction_id) {
    // Basic validation failed
    header('Location: ' . SITE_URL . 'checkout/confirmation.php?error=invalid_input');
    exit();
}

$pdo = db_connect();

// Update the order with the transaction ID
$stmt = $pdo->prepare(
    "UPDATE orders SET transaction_id = ?, status = 'pending_verification' WHERE id = ?"
);
$stmt->execute([$transaction_id, $order_id]);

// Redirect to a thank you page or order details page
header('Location: ' . SITE_URL . 'account/orders.php?order_id=' . $order_id . '&status=updated');
exit();