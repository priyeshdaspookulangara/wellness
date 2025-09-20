<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';

// Admin authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: " . SITE_URL . "login/");
    exit;
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id === 0) {
    $_SESSION['error_message'] = "Invalid product ID.";
    header("Location: " . SITE_URL . "admin/products/");
    exit;
}

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Product not found.";
    header("Location: " . SITE_URL . "admin/products/");
    exit;
}
$product_data = $result->fetch_assoc();
$stmt->close();

// Fetch product media
$media_stmt = $conn->prepare("SELECT * FROM product_media WHERE product_id = ?");
$media_stmt->bind_param("i", $product_id);
$media_stmt->execute();
$media_result = $media_stmt->get_result();
$product_media = [];
while ($row = $media_result->fetch_assoc()) {
    $product_media[] = $row;
}
$media_stmt->close();

// Handle media deletion
if (isset($_GET['delete_media'])) {
    $media_id_to_delete = (int)$_GET['delete_media'];

    // First, get the filename to delete from the server
    $stmt_get_media = $conn->prepare("SELECT path_or_url, media_type FROM product_media WHERE id = ? AND product_id = ?");
    $stmt_get_media->bind_param("ii", $media_id_to_delete, $product_id);
    $stmt_get_media->execute();
    $result_media = $stmt_get_media->get_result();
    if ($result_media->num_rows === 1) {
        $media_to_delete = $result_media->fetch_assoc();

        // Now, delete the database record
        $stmt_delete = $conn->prepare("DELETE FROM product_media WHERE id = ?");
        $stmt_delete->bind_param("i", $media_id_to_delete);
        if ($stmt_delete->execute()) {
            // If DB deletion is successful and it's an image, delete the file
            if ($media_to_delete['media_type'] === 'image') {
                $file_path = __DIR__ . '/../../uploads/' . $media_to_delete['path_or_url'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            $_SESSION['success_message'] = "Media item deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to delete media item.";
        }
        $stmt_delete->close();
    }
    $stmt_get_media->close();

    header("Location: " . SITE_URL . "admin/product_edit/?id=" . $product_id);
    exit;
}


// Handle form submission for UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Full Product Data Update Logic ---
    $product_data['name'] = trim($_POST['name'] ?? '');
    $product_data['description'] = trim($_POST['description'] ?? '');
    // ... (retrieve all other fields from $_POST)

    $errors = [];
    if (empty($product_data['name'])) {
        $errors[] = "Product name is required.";
    }
    // ... (full validation for other fields)

    if (empty($errors)) {
        // Update main product table
        $sql_update_product = "UPDATE products SET name = ?, description = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update_product);
        // Bind all the parameters...
        $stmt_update->bind_param("ssi", $product_data['name'], $product_data['description'], $product_id);
        $stmt_update->execute();
        $stmt_update->close();
    }
    // --- End of Full Product Data Update Logic ---


    // Handle additional images
    if (isset($_FILES['additional_images'])) {
        $upload_dir = __DIR__ . '/../../uploads/';
        foreach ($_FILES['additional_images']['name'] as $key => $name) {
            if ($_FILES['additional_images']['error'][$key] == UPLOAD_ERR_OK) {
                $file_info = pathinfo($name);
                $image_file = uniqid('prod_edit_') . '.' . strtolower($file_info['extension']);
                $target_file = $upload_dir . $image_file;
                if (move_uploaded_file($_FILES['additional_images']['tmp_name'][$key], $target_file)) {
                    $media_type = 'image';
                    $stmt_media = $conn->prepare("INSERT INTO product_media (product_id, media_type, path_or_url) VALUES (?, ?, ?)");
                    $stmt_media->bind_param("iss", $product_id, $media_type, $image_file);
                    $stmt_media->execute();
                    $stmt_media->close();
                }
            }
        }
    }

    // Handle video URL
    $video_url = trim($_POST['video_url'] ?? '');
    // First, delete existing video URL for this product
    $stmt_delete_video = $conn->prepare("DELETE FROM product_media WHERE product_id = ? AND media_type = 'video'");
    $stmt_delete_video->bind_param("i", $product_id);
    $stmt_delete_video->execute();
    $stmt_delete_video->close();
    // Now, insert the new one if it's provided
    if (!empty($video_url) && filter_var($video_url, FILTER_VALIDATE_URL)) {
        $media_type = 'video';
        $stmt_video = $conn->prepare("INSERT INTO product_media (product_id, media_type, path_or_url) VALUES (?, ?, ?)");
        $stmt_video->bind_param("iss", $product_id, $media_type, $video_url);
        $stmt_video->execute();
        $stmt_video->close();
    }

    // In a full implementation, you'd also update the main product table here.
    // For now, just redirect
    $_SESSION['success_message'] = "Product media updated successfully!";
    header("Location: " . SITE_URL . "admin/product_edit/?id=" . $product_id);
    exit;
}


$page_title = "Edit Product";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Product (ID: <?php echo $product_id; ?>)</h1>
    <a href="<?php echo SITE_URL; ?>admin/products/" class="btn btn-sm btn-outline-secondary">Back to Products</a>
</div>

<?php // We will use the same form as add_product, but pre-fill it with $product_data and $product_media ?>
<form action="<?php echo SITE_URL; ?>admin/product_edit/?id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Product Details</div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label for="name">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product_data['name']); ?>" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($product_data['description']); ?></textarea>
                    </div>
                    <?php // Other text-based fields would go here, pre-filled like the ones above ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">Media</div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label>Main Product Image</label>
                        <?php if (!empty($product_data['image_url_main'])): ?>
                            <img src="<?php echo SITE_URL . 'uploads/' . htmlspecialchars($product_data['image_url_main']); ?>" alt="Main Image" class="img-fluid mb-2" style="max-width: 100px;">
                        <?php endif; ?>
                        <input type="file" class="form-control" name="image_url_main">
                    </div>
                    <hr>
                    <div class="form-group mb-3">
                        <label>Additional Images</label>
                        <div class="mb-2">
                            <?php foreach ($product_media as $media): if ($media['media_type'] == 'image'): ?>
                                <div class="d-inline-block position-relative">
                                    <img src="<?php echo SITE_URL . 'uploads/' . htmlspecialchars($media['path_or_url']); ?>" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                    <a href="?id=<?php echo $product_id; ?>&delete_media=<?php echo $media['id']; ?>" class="btn btn-sm btn-danger position-absolute top-0 end-0" style="padding: 0.1rem 0.3rem; font-size: 0.7rem;">&times;</a>
                                </div>
                            <?php endif; endforeach; ?>
                        </div>
                        <input type="file" class="form-control" name="additional_images[]" multiple>
                    </div>
                    <hr>
                    <div class="form-group mb-3">
                        <label>Product Video URL</label>
                        <?php
                            $video_url = '';
                            foreach ($product_media as $media) {
                                if ($media['media_type'] == 'video') {
                                    $video_url = $media['path_or_url'];
                                    // Assuming one video per product for simplicity
                                    break;
                                }
                            }
                        ?>
                        <input type="text" class="form-control" name="video_url" value="<?php echo htmlspecialchars($video_url); ?>" placeholder="e.g., https://www.youtube.com/watch?v=...">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block w-100">Update Product</button>
        </div>
    </div>
</form>

<?php
// The full backend logic for handling the UPDATE, file uploads, and media deletion
// will be added in a subsequent step. This is just the form structure.
require_once __DIR__ . '/../includes/footer.php';
?>
