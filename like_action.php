<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to like products.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $action = $_POST['action'];
    $user_id = $_SESSION['user_id'];

    $db = db_connect();

    if ($action === 'like') {
        // Check if already liked
        $stmt = $db->prepare("SELECT * FROM product_likes WHERE user_id = :user_id AND product_id = :product_id");
        $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);

        if (!$stmt->fetch()) {
            $insert_stmt = $db->prepare("INSERT INTO product_likes (user_id, product_id) VALUES (:user_id, :product_id)");
            $insert_stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);
        }
    } elseif ($action === 'unlike') {
        $delete_stmt = $db->prepare("DELETE FROM product_likes WHERE user_id = :user_id AND product_id = :product_id");
        $delete_stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);
    }

    // Get new like count
    $count_stmt = $db->prepare("SELECT COUNT(*) FROM product_likes WHERE product_id = :product_id");
    $count_stmt->execute(['product_id' => $product_id]);
    $like_count = $count_stmt->fetchColumn();

    echo json_encode(['success' => true, 'like_count' => $like_count]);
}
?>
