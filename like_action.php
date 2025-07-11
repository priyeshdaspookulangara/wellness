<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php'; // For db connection and escape_string

header('Content-Type: application/json'); // Set content type to JSON for all responses

$response = ['success' => false, 'message' => 'An unknown error occurred.', 'is_liked' => false, 'like_count' => 0];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'You must be logged in to like products.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$product_id = (int)($_POST['product_id'] ?? 0);
$action = trim($_POST['action'] ?? ''); // 'like' or 'unlike'

if (!$product_id || $product_id <= 0) {
    $response['message'] = 'Invalid product specified.';
    echo json_encode($response);
    exit;
}

// Check if product exists
$sql_check_product = "SELECT id FROM products WHERE id = $product_id";
$res_check_product = mysqli_query($conn, $sql_check_product);
if (!$res_check_product || mysqli_num_rows($res_check_product) == 0) {
    $response['message'] = 'Product not found.';
    echo json_encode($response);
    exit;
}

// Check current like status for this user and product
$sql_is_liked = "SELECT id FROM user_product_likes WHERE user_id = $user_id AND product_id = $product_id";
$res_is_liked = mysqli_query($conn, $sql_is_liked);
$current_user_likes_product = ($res_is_liked && mysqli_num_rows($res_is_liked) > 0);

if ($action === 'like') {
    if ($current_user_likes_product) {
        $response['message'] = 'You already liked this product.';
        $response['success'] = true; // Or false, depending on how you want to handle "already liked"
        $response['is_liked'] = true;
    } else {
        $sql_insert_like = "INSERT INTO user_product_likes (user_id, product_id, created_at) VALUES ($user_id, $product_id, NOW())";
        if (mysqli_query($conn, $sql_insert_like)) {
            $response['success'] = true;
            $response['message'] = 'Product liked successfully!';
            $response['is_liked'] = true;
        } else {
            $response['message'] = 'Failed to like product. Error: ' . mysqli_error($conn);
        }
    }
} elseif ($action === 'unlike') {
    if (!$current_user_likes_product) {
        $response['message'] = 'You have not liked this product yet.';
        $response['success'] = true; // Or false
        $response['is_liked'] = false;
    } else {
        $sql_delete_like = "DELETE FROM user_product_likes WHERE user_id = $user_id AND product_id = $product_id";
        if (mysqli_query($conn, $sql_delete_like)) {
            $response['success'] = true;
            $response['message'] = 'Product unliked successfully!';
            $response['is_liked'] = false;
        } else {
            $response['message'] = 'Failed to unlike product. Error: ' . mysqli_error($conn);
        }
    }
} else {
    $response['message'] = 'Invalid action specified.';
}

// Get updated total like count for the product (optional, but good for UI)
$sql_like_count = "SELECT COUNT(id) as count FROM user_product_likes WHERE product_id = $product_id";
$res_like_count = mysqli_query($conn, $sql_like_count);
if ($res_like_count) {
    $count_data = mysqli_fetch_assoc($res_like_count);
    $response['like_count'] = (int)$count_data['count'];
}

echo json_encode($response);
exit;
?>
