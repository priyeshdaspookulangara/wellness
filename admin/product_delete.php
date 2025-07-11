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

// For CSRF protection, ideally a token should be validated here.
// For simplicity, we're skipping it in this version.

$product_id = (int)($_GET['id'] ?? 0);

if (!$product_id) {
    $_SESSION['error_message'] = "Invalid product ID for deletion.";
    header("Location: products.php");
    exit;
}

// First, get the image filename to delete it from the server
$sql_get_image = "SELECT image_url_main FROM products WHERE id = $product_id";
$result_image = mysqli_query($conn, $sql_get_image);
$image_to_delete = null;
if ($result_image && mysqli_num_rows($result_image) > 0) {
    $row = mysqli_fetch_assoc($result_image);
    $image_to_delete = $row['image_url_main'];
}

// Attempt to delete the product from the database
// Consider foreign key constraints:
// - order_items: If ON DELETE RESTRICT, deletion will fail if orders reference this product.
//   Change to ON DELETE SET NULL or handle archiving products instead of hard deletes.
// - reviews: If ON DELETE CASCADE, reviews will be deleted. This is often desired.
// For this example, we assume RESTRICT is not preventing deletion or it's handled (e.g. product not in orders)

$sql_delete = "DELETE FROM products WHERE id = $product_id";

if (mysqli_query($conn, $sql_delete)) {
    if (mysqli_affected_rows($conn) > 0) {
        // If product deleted successfully, try to delete its image
        if ($image_to_delete) {
            $upload_dir = __DIR__ . '/../uploads/';
            $image_path = $upload_dir . $image_to_delete;
            if (file_exists($image_path)) {
                unlink($image_path); // Delete the file
            }
        }
        $_SESSION['success_message'] = "Product (ID: $product_id) deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Product (ID: $product_id) not found or already deleted.";
    }
} else {
    // Check for foreign key constraint violation (MySQL error code 1451)
    if(mysqli_errno($conn) == 1451){
        $_SESSION['error_message'] = "Cannot delete product (ID: $product_id) because it is referenced by existing orders or other records. Consider archiving the product instead.";
    } else {
        $_SESSION['error_message'] = "Failed to delete product (ID: $product_id). Error: " . mysqli_error($conn);
    }
}

header("Location: products.php");
exit;
?>
