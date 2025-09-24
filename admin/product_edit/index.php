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

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// Handle media deletion first if requested
if (isset($_GET['delete_media'])) {
    $media_id_to_delete = (int)$_GET['delete_media'];
    $stmt_get_media = $conn->prepare("SELECT path_or_url FROM product_media WHERE id = ? AND product_id = ? AND media_type = 'image'");
    $stmt_get_media->bind_param("ii", $media_id_to_delete, $product_id);
    if ($stmt_get_media->execute()) {
        $result_media = $stmt_get_media->get_result();
        if ($media_to_delete = $result_media->fetch_assoc()) {
            $stmt_delete = $conn->prepare("DELETE FROM product_media WHERE id = ?");
            $stmt_delete->bind_param("i", $media_id_to_delete);
            if ($stmt_delete->execute()) {
                $file_path = __DIR__ . '/../../uploads/' . $media_to_delete['path_or_url'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                $_SESSION['success_message'] = "Media item deleted successfully.";
            }
            $stmt_delete->close();
        }
    }
    $stmt_get_media->close();
    header("Location: " . SITE_URL . "admin/product_edit/?id=" . $product_id);
    exit;
}

// Handle form submission for UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $new_data = [
        'name' => trim($_POST['name'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'category_id' => (int)($_POST['category_id'] ?? 0),
        'description' => trim($_POST['description'] ?? ''),
        'how_it_works' => trim($_POST['how_it_works'] ?? ''),
        'health_benefits_text' => trim($_POST['health_benefits_text'] ?? ''),
        'gauss_strength' => trim($_POST['gauss_strength'] ?? ''),
        'material_quality_design' => trim($_POST['material_quality_design'] ?? ''),
        'usage_guide_text' => trim($_POST['usage_guide_text'] ?? ''),
        'price' => trim($_POST['price'] ?? ''),
        'stock' => (int)($_POST['stock'] ?? 0),
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'is_on_sale' => isset($_POST['is_on_sale']) ? 1 : 0,
        'sale_price' => !empty($_POST['sale_price']) ? trim($_POST['sale_price']) : null
    ];

    // --- Validation would go here ---

    // Handle main image upload
    $image_file_name = $product_data['image_url_main']; // Keep old image by default
    if (isset($_FILES['image_url_main']) && $_FILES['image_url_main']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../uploads/';
        $file_info = pathinfo($_FILES['image_url_main']['name']);
        $new_image_file_name = uniqid('prod_') . '.' . strtolower($file_info['extension']);
        if (move_uploaded_file($_FILES['image_url_main']['tmp_name'], $upload_dir . $new_image_file_name)) {
            if ($image_file_name && file_exists($upload_dir . $image_file_name)) {
                unlink($upload_dir . $image_file_name);
            }
            $image_file_name = $new_image_file_name;
        } else {
            $errors[] = "Failed to upload new main image.";
        }
    }

    if (empty($errors)) {
        $sql_update = "UPDATE products SET name=?, slug=?, category_id=?, description=?, how_it_works=?, health_benefits_text=?, gauss_strength=?, material_quality_design=?, usage_guide_text=?, price=?, stock=?, image_url_main=?, is_featured=?, is_on_sale=?, sale_price=?, updated_at=NOW() WHERE id=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ssissssssdsiisi", $new_data['name'], $new_data['slug'], $new_data['category_id'], $new_data['description'], $new_data['how_it_works'], $new_data['health_benefits_text'], $new_data['gauss_strength'], $new_data['material_quality_design'], $new_data['usage_guide_text'], $new_data['price'], $new_data['stock'], $image_file_name, $new_data['is_featured'], $new_data['is_on_sale'], $new_data['sale_price'], $product_id);
        $stmt_update->execute();
        $stmt_update->close();

        // Handle new additional images
        if (isset($_FILES['additional_images'])) {
            $stmt_media = $conn->prepare("INSERT INTO product_media (product_id, media_type, path_or_url) VALUES (?, 'image', ?)");
            foreach ($_FILES['additional_images']['name'] as $key => $name) {
                if ($_FILES['additional_images']['error'][$key] == UPLOAD_ERR_OK) {
                    $file_info = pathinfo($name);
                    $new_add_image_name = uniqid('prod_add_') . '.' . strtolower($file_info['extension']);
                    if (move_uploaded_file($_FILES['additional_images']['tmp_name'][$key], $upload_dir . $new_add_image_name)) {
                        $stmt_media->bind_param("is", $product_id, $new_add_image_name);
                        $stmt_media->execute();
                    }
                }
            }
            $stmt_media->close();
        }

        // Handle video URL update
        $video_url = trim($_POST['video_url'] ?? '');
        $stmt_delete_video = $conn->prepare("DELETE FROM product_media WHERE product_id = ? AND media_type = 'video'");
        $stmt_delete_video->bind_param("i", $product_id);
        $stmt_delete_video->execute();
        $stmt_delete_video->close();
        if (!empty($video_url) && filter_var($video_url, FILTER_VALIDATE_URL)) {
            $stmt_video = $conn->prepare("INSERT INTO product_media (product_id, media_type, path_or_url) VALUES (?, 'video', ?)");
            $stmt_video->bind_param("is", $product_id, $video_url);
            $stmt_video->execute();
            $stmt_video->close();
        }

        $_SESSION['success_message'] = "Product updated successfully!";
        header("Location: " . SITE_URL . "admin/product_edit/?id=" . $product_id);
        exit;
    }
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
$media_stmt = $conn->prepare("SELECT * FROM product_media WHERE product_id = ? ORDER BY media_type");
$media_stmt->bind_param("i", $product_id);
$media_stmt->execute();
$media_result = $media_stmt->get_result();
$product_media = [];
$video_url = '';
while ($row = $media_result->fetch_assoc()) {
    if($row['media_type'] == 'video') {
        $video_url = $row['path_or_url'];
    } else {
        $product_media[] = $row;
    }
}
$media_stmt->close();

// Fetch categories for dropdown
$result_categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $result_categories->fetch_all(MYSQLI_ASSOC);

$page_title = "Edit Product";
$breadcrumbs = [
    ['name' => 'Products', 'link' => SITE_URL . 'admin/products/'],
    ['name' => 'Edit Product']
];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Product (ID: <?php echo $product_id; ?>)</h1>
</div>

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
                        <label for="slug">Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($product_data['slug']); ?>">
                    </div>
                    <div class="form-group mb-3">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($product_data['description']); ?></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="how_it_works">How It Works</label>
                        <textarea class="form-control" id="how_it_works" name="how_it_works" rows="3"><?php echo htmlspecialchars($product_data['how_it_works']); ?></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="health_benefits_text">Health Concerns Addressed</label>
                        <input type="text" class="form-control" id="health_benefits_text" name="health_benefits_text" value="<?php echo htmlspecialchars($product_data['health_benefits_text']); ?>">
                    </div>
                    <div class="form-group mb-3">
                        <label for="material_quality_design">Material Quality & Design</label>
                        <textarea class="form-control" id="material_quality_design" name="material_quality_design" rows="3"><?php echo htmlspecialchars($product_data['material_quality_design']); ?></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="usage_guide_text">Usage Guides</label>
                        <textarea class="form-control" id="usage_guide_text" name="usage_guide_text" rows="3"><?php echo htmlspecialchars($product_data['usage_guide_text']); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">Pricing & Stock</div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label for="price">Price ($)</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($product_data['price']); ?>" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="stock">Stock Quantity</label>
                        <input type="number" class="form-control" id="stock" name="stock" value="<?php echo htmlspecialchars($product_data['stock']); ?>" required>
                    </div>
                     <div class="form-group form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="is_on_sale" name="is_on_sale" value="1" <?php echo $product_data['is_on_sale'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_on_sale">Is on Sale?</label>
                    </div>
                    <div class="form-group mb-3" id="sale_price_group" style="<?php echo $product_data['is_on_sale'] ? '' : 'display:none;'; ?>">
                        <label for="sale_price">Sale Price ($)</label>
                        <input type="number" step="0.01" class="form-control" id="sale_price" name="sale_price" value="<?php echo htmlspecialchars($product_data['sale_price'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">Organization</div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label for="category_id">Category</label>
                        <select class="form-control form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($product_data['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="gauss_strength">Magnetic Strength (Gauss)</label>
                        <input type="text" class="form-control" id="gauss_strength" name="gauss_strength" value="<?php echo htmlspecialchars($product_data['gauss_strength']); ?>">
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">Media</div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label>Main Product Image</label>
                        <?php if (!empty($product_data['image_url_main'])): ?>
                            <img src="<?php echo SITE_URL . 'uploads/' . htmlspecialchars($product_data['image_url_main']); ?>" alt="Main Image" class="img-fluid mb-2" style="max-width: 100px;">
                        <?php endif; ?>
                        <input type="file" class="form-control" name="image_url_main">
                        <small class="form-text text-muted">Upload a new image to replace the current one.</small>
                    </div>
                    <hr>
                    <div class="form-group mb-3">
                        <label>Additional Images</label>
                        <div class="mb-2">
                            <?php foreach ($product_media as $media): ?>
                                <div class="d-inline-block position-relative">
                                    <img src="<?php echo SITE_URL . 'uploads/' . htmlspecialchars($media['path_or_url']); ?>" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                    <a href="?id=<?php echo $product_id; ?>&delete_media=<?php echo $media['id']; ?>" class="btn btn-sm btn-danger position-absolute top-0 end-0" style="padding: 0.1rem 0.3rem; font-size: 0.7rem;" onclick="return confirm('Are you sure you want to delete this image?');">&times;</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="file" class="form-control" name="additional_images[]" multiple>
                    </div>
                    <hr>
                    <div class="form-group mb-3">
                        <label for="video_url">Product Video URL</label>
                        <input type="text" class="form-control" id="video_url" name="video_url" value="<?php echo htmlspecialchars($video_url); ?>" placeholder="e.g., https://www.youtube.com/watch?v=...">
                    </div>
                </div>
            </div>
             <div class="card mb-3">
                <div class="card-header">Visibility</div>
                <div class="card-body">
                    <div class="form-group form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1" <?php echo $product_data['is_featured'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_featured">Feature on homepage</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Update Product</button>
        </div>
    </div>
</form>

<script>
$(document).ready(function(){
    $('#is_on_sale').change(function(){
        if($(this).is(":checked")) {
            $('#sale_price_group').show();
        } else {
            $('#sale_price_group').hide();
            $('#sale_price').val('');
        }
    });
});
</script>

<?php
$conn->close();
require_once __DIR__ . '/../includes/footer.php';
?>
