<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    // Should not happen if form is protected, but good to check
    $_SESSION['review_message'] = "You must be logged in to submit a review.";
    $_SESSION['review_message_type'] = "danger";
    header("Location: login.php"); // Or redirect back to product page
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_SESSION['user_id'];
    $product_id = (int)($_POST['product_id'] ?? 0);
    $product_slug = trim($_POST['product_slug'] ?? ''); // For redirect
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    $redirect_url = !empty($product_slug) ? SITE_URL . '/product/' . $product_slug : SITE_URL . '/index.php';

    // Validation
    if (!$product_id) {
        $_SESSION['review_message'] = "Invalid product specified for review.";
        $_SESSION['review_message_type'] = "danger";
        header("Location: " . $redirect_url);
        exit;
    }
    if ($rating < 1 || $rating > 5) {
        $_SESSION['review_message'] = "Please select a valid rating (1-5 stars).";
        $_SESSION['review_message_type'] = "danger";
        header("Location: " . $redirect_url . "#review-form"); // Link to form
        exit;
    }
    if (empty($comment)) {
        $_SESSION['review_message'] = "Please enter your review comment.";
        $_SESSION['review_message_type'] = "danger";
        header("Location: " . $redirect_url . "#review-form");
        exit;
    }
    if (strlen($comment) > 2000) { // Max comment length
        $_SESSION['review_message'] = "Your review comment is too long (max 2000 characters).";
        $_SESSION['review_message_type'] = "danger";
        header("Location: " . $redirect_url . "#review-form");
        exit;
    }

    // Check if user has purchased this product (re-validating server-side)
    // This is a simplified check; a more robust system might check order status (e.g., 'delivered').
    $sql_check_purchase = "SELECT o.id FROM orders o
                           JOIN order_items oi ON o.id = oi.order_id
                           WHERE o.user_id = $user_id
                           AND oi.product_id = $product_id
                           AND o.status = 'delivered' LIMIT 1"; // Only allow after delivery
    $res_check_purchase = mysqli_query($conn, $sql_check_purchase);
    // Temp: Allow review for product ID 101 for demo purposes without purchase check
    $is_demo_product = ($product_id == 101);

    if ( (!$res_check_purchase || mysqli_num_rows($res_check_purchase) == 0) && !$is_demo_product ) {
        $_SESSION['review_message'] = "You can only review products you have purchased and received.";
        $_SESSION['review_message_type'] = "warning";
        header("Location: " . $redirect_url);
        exit;
    }

    // Check if user has already reviewed this product
    $sql_check_existing = "SELECT id FROM reviews WHERE user_id = $user_id AND product_id = $product_id LIMIT 1";
    $res_check_existing = mysqli_query($conn, $sql_check_existing);
    if ($res_check_existing && mysqli_num_rows($res_check_existing) > 0) {
        $_SESSION['review_message'] = "You have already submitted a review for this product.";
        $_SESSION['review_message_type'] = "info";
        header("Location: " . $redirect_url);
        exit;
    }

    // Insert review
    $comment_esc = escape_string($comment);
    // Status is 'pending' by default, to be approved by admin
    $sql_insert_review = "INSERT INTO reviews (product_id, user_id, rating, comment, status, created_at, updated_at)
                          VALUES ($product_id, $user_id, $rating, '$comment_esc', 'pending', NOW(), NOW())";

    if (mysqli_query($conn, $sql_insert_review)) {
        $_SESSION['review_message'] = "Thank you! Your review has been submitted and is awaiting approval.";
        $_SESSION['review_message_type'] = "success";
    } else {
        $_SESSION['review_message'] = "Sorry, there was an error submitting your review. Please try again. " . mysqli_error($conn);
        $_SESSION['review_message_type'] = "danger";
    }

    header("Location: " . $redirect_url . (strpos($redirect_url, '#') === false ? '#customer-reviews' : '')); // Redirect to reviews section
    exit;

} else {
    // Not a POST request, redirect away
    header("Location: " . SITE_URL . "/index.php");
    exit;
}
?>
