<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json');

// Admin authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$image_id = filter_input(INPUT_POST, 'image_id', FILTER_VALIDATE_INT);
if (!$image_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid image ID.']);
    exit;
}

// This code is untested due to the lack of a live database environment.
try {
    $conn->begin_transaction();

    // First, get the image filename to delete the file
    $stmt_get = $conn->prepare("SELECT image_url FROM product_images WHERE id = ?");
    $stmt_get->bind_param("i", $image_id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    $image = $result->fetch_assoc();

    if ($image) {
        // Delete the record from the database
        $stmt_delete = $conn->prepare("DELETE FROM product_images WHERE id = ?");
        $stmt_delete->bind_param("i", $image_id);
        $stmt_delete->execute();

        if ($stmt_delete->affected_rows > 0) {
            // If DB deletion is successful, delete the file
            $image_path = __DIR__ . '/../../uploads/' . $image['image_url'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Image deleted successfully.']);
        } else {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to delete image from database.']);
        }
    } else {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Image not found.']);
    }

} catch (Exception $e) {
    $conn->rollback();
    // In a real app, log the error
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
}

exit;
?>