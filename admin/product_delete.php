<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

// Admin authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $_SESSION['error_message'] = "Access denied.";
    header("Location: " . SITE_URL . "login/");
    exit;
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id > 0) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // --- Step 1: Prepare all SQL statements first ---
    $stmt_main = $conn->prepare("SELECT image_url_main FROM products WHERE id = ?");
    $stmt_media = $conn->prepare("SELECT path_or_url FROM product_media WHERE product_id = ? AND media_type = 'image'");
    $stmt_delete = $conn->prepare("DELETE FROM products WHERE id = ?");

    // Check if all prepares were successful
    if ($stmt_main && $stmt_media && $stmt_delete) {

        // --- Step 2: Execute queries to fetch filenames ---
        $filenames_to_delete = [];

        $stmt_main->bind_param("i", $product_id);
        $stmt_main->execute();
        $result_main = $stmt_main->get_result();
        if ($row = $result_main->fetch_assoc()) {
            if (!empty($row['image_url_main'])) {
                $filenames_to_delete[] = $row['image_url_main'];
            }
        }
        $stmt_main->close();

        $stmt_media->bind_param("i", $product_id);
        $stmt_media->execute();
        $result_media = $stmt_media->get_result();
        while ($row = $result_media->fetch_assoc()) {
            $filenames_to_delete[] = $row['path_or_url'];
        }
        $stmt_media->close();

        // --- Step 3: Execute the delete query ---
        $stmt_delete->bind_param("i", $product_id);
        if ($stmt_delete->execute()) {
            // --- Step 4: If DB deletion is successful, delete the physical files ---
            $upload_dir = __DIR__ . '/../uploads/';
            foreach ($filenames_to_delete as $filename) {
                $file_path = $upload_dir . $filename;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            $_SESSION['success_message'] = "Product (ID: $product_id) and all associated media have been deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to delete product.";
        }
        $stmt_delete->close();

    } else {
        // One of the prepare calls failed
        $_SESSION['error_message'] = "An error occurred preparing the database query.";
    }

    $conn->close();

} else {
    $_SESSION['error_message'] = "Invalid product ID.";
}

// --- Step 5: Redirect back to the product list ---
header("Location: " . SITE_URL . "admin/products/");
exit;
?>
