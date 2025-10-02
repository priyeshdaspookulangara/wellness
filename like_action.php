<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json');

// Check if product_id is provided
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid Product ID.']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
$session_id = session_id();

// This code is untested due to the lack of a live database environment.
try {
    $conn->begin_transaction();

    // Check if the user/session has already liked the product
    if ($user_id) {
        $stmt = $conn->prepare("SELECT id FROM product_likes WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
    } else {
        $stmt = $conn->prepare("SELECT id FROM product_likes WHERE session_id = ? AND product_id = ?");
        $stmt->bind_param("si", $session_id, $product_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User has already liked, so we unlike it
        $like_id = $result->fetch_assoc()['id'];
        $stmt_delete = $conn->prepare("DELETE FROM product_likes WHERE id = ?");
        $stmt_delete->bind_param("i", $like_id);
        $stmt_delete->execute();
        $action = 'unliked';
    } else {
        // New like
        $stmt_insert = $conn->prepare("INSERT INTO product_likes (product_id, user_id, session_id) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("iis", $product_id, $user_id, $session_id);
        $stmt_insert->execute();
        $action = 'liked';
    }

    // Get the new total like count for the product
    $stmt_count = $conn->prepare("SELECT COUNT(*) as like_count FROM product_likes WHERE product_id = ?");
    $stmt_count->bind_param("i", $product_id);
    $stmt_count->execute();
    $like_count = $stmt_count->get_result()->fetch_assoc()['like_count'];

    $conn->commit();

    echo json_encode(['success' => true, 'action' => $action, 'like_count' => $like_count]);

} catch (Exception $e) {
    $conn->rollback();
    // In a real app, log the error
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
}

exit;
?>