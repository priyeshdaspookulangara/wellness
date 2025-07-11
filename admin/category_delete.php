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

// CSRF protection token check would go here

$category_id = (int)($_GET['id'] ?? 0);

if (!$category_id) {
    $_SESSION['error_message'] = "Invalid category ID for deletion.";
    header("Location: categories.php");
    exit;
}

// Before deleting category, check if any products are assigned to it.
// The products.category_id has ON DELETE RESTRICT, so DB will prevent deletion if products exist.
$sql_check_products = "SELECT COUNT(*) as product_count FROM products WHERE category_id = $category_id";
$result_check = mysqli_query($conn, $sql_check_products);
$product_count_row = mysqli_fetch_assoc($result_check);

if ($product_count_row && $product_count_row['product_count'] > 0) {
    $_SESSION['error_message'] = "Cannot delete category (ID: $category_id) because it has " . $product_count_row['product_count'] . " associated products. Please reassign or delete these products first.";
    header("Location: categories.php");
    exit;
}

// Get the image filename to delete it from the server
$sql_get_image = "SELECT image_url FROM categories WHERE id = $category_id";
$result_image = mysqli_query($conn, $sql_get_image);
$image_to_delete = null;
if ($result_image && mysqli_num_rows($result_image) > 0) {
    $row = mysqli_fetch_assoc($result_image);
    $image_to_delete = $row['image_url'];
}

// Attempt to delete the category from the database
$sql_delete = "DELETE FROM categories WHERE id = $category_id";

if (mysqli_query($conn, $sql_delete)) {
    if (mysqli_affected_rows($conn) > 0) {
        // If category deleted successfully, try to delete its image
        if ($image_to_delete) {
            $upload_cat_dir = __DIR__ . '/../uploads/categories/';
            $image_path = $upload_cat_dir . $image_to_delete;
            if (file_exists($image_path)) {
                unlink($image_path); // Delete the file
            }
        }
        $_SESSION['success_message'] = "Category (ID: $category_id) deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Category (ID: $category_id) not found or already deleted.";
    }
} else {
    $_SESSION['error_message'] = "Failed to delete category (ID: $category_id). Error: " . mysqli_error($conn);
}

header("Location: categories.php");
exit;
?>
