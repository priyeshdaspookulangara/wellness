<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    // Or redirect to login page
    $_SESSION['message'] = "You must be logged in to submit a review.";
    $_SESSION['message_type'] = "danger";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $rating = $_POST['rating'];
    $review_text = $_POST['review_text'];
    $user_id = $_SESSION['user_id'];

    if (empty($rating) || empty($review_text)) {
        $_SESSION['message'] = "Rating and review text are required.";
        $_SESSION['message_type'] = "danger";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    $db = db_connect();
    $stmt = $db->prepare("
        INSERT INTO reviews (product_id, user_id, rating, review_text, created_at)
        VALUES (:product_id, :user_id, :rating, :review_text, NOW())
    ");
    $stmt->execute([
        'product_id' => $product_id,
        'user_id' => $user_id,
        'rating' => $rating,
        'review_text' => $review_text
    ]);

    $_SESSION['message'] = "Your review has been submitted.";
    $_SESSION['message_type'] = "success";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>
