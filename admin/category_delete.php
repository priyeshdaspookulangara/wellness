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

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id > 0) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // --- Step 1: Check if any products are assigned to this category ---
    $stmt_check = $conn->prepare("SELECT id FROM products WHERE category_id = ? LIMIT 1");
    $stmt_check->bind_param("i", $category_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // Category is not empty, prevent deletion
        $_SESSION['error_message'] = "Cannot delete category (ID: $category_id) because it still contains products. Please re-assign them to another category first.";
    } else {
        // Category is empty, proceed with deletion
        // Note: We should also consider deleting the category image from the uploads folder if it exists.
        // For now, focusing on the core safe-delete logic.
        $stmt_delete = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt_delete->bind_param("i", $category_id);

        if ($stmt_delete->execute()) {
            $_SESSION['success_message'] = "Category (ID: $category_id) has been deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to delete category.";
        }
        $stmt_delete->close();
    }

    $stmt_check->close();
    $conn->close();

} else {
    $_SESSION['error_message'] = "Invalid category ID.";
}

// --- Step 2: Redirect back to the category list ---
header("Location: " . SITE_URL . "admin/categories/");
exit;
?>
