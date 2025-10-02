<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';

// Admin authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $_SESSION['admin_error'] = "Access denied.";
    header("Location: " . SITE_URL . "login/");
    exit;
}

// Check for product ID
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$product_id) {
    $_SESSION['error_message'] = "Invalid product ID.";
    header("Location: " . SITE_URL . "admin/products/");
    exit;
}

// Fetch product data from the database to be used for the form and update logic
$stmt_fetch = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt_fetch->bind_param("i", $product_id);
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();
$product_data = $result->fetch_assoc();

if (!$product_data) {
    $_SESSION['error_message'] = "Product not found.";
    header("Location: " . SITE_URL . "admin/products/");
    exit;
}

$errors = [];

// Handle form submission before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start with the existing image name. This will only be overwritten if a new image is successfully uploaded.
    $image_filename_to_update = $product_data['image_url_main'];

    // Check if a new file has been uploaded and there are no upload errors.
    if (isset($_FILES['image_url_main']) && $_FILES['image_url_main']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../uploads/';
        $file_info = pathinfo($_FILES['image_url_main']['name']);
        $new_image_name = uniqid('prod_') . '.' . strtolower($file_info['extension']);
        $target_file = $upload_dir . $new_image_name;
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $upload_ok = true;

        if (!in_array(strtolower($file_info['extension']), $allowed_types)) {
            $errors[] = "Invalid image file type. Allowed: " . implode(', ', $allowed_types);
            $upload_ok = false;
        } elseif ($_FILES['image_url_main']['size'] > 2 * 1024 * 1024) {
            $errors[] = "Image file size exceeds 2MB limit.";
            $upload_ok = false;
        }

        if ($upload_ok) {
            if (move_uploaded_file($_FILES['image_url_main']['tmp_name'], $target_file)) {
                // SUCCESS: The new file is saved. Set it as the filename for the database.
                $image_filename_to_update = $new_image_name;
                // Delete the old image file if it exists and is different from the new one.
                if (!empty($product_data['image_url_main']) && file_exists($upload_dir . $product_data['image_url_main'])) {
                    unlink($upload_dir . $product_data['image_url_main']);
                }
            } else {
                $errors[] = "Failed to move uploaded file.";
            }
        }
    }

    // Sanitize and retrieve the rest of the form data
    $submitted_data = [
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
        'sale_price' => !empty($_POST['sale_price']) ? trim($_POST['sale_price']) : null,
    ];

    // Other validations...
    if (empty($submitted_data['name'])) $errors[] = "Product name is required.";
    if (empty($submitted_data['slug'])) $errors[] = "Slug is required.";
    // ...

    if (empty($errors)) {
        // Check for unique slug
        $stmt_check_slug = $conn->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
        $stmt_check_slug->bind_param("si", $submitted_data['slug'], $product_id);
        $stmt_check_slug->execute();
        if ($stmt_check_slug->get_result()->num_rows > 0) {
            $errors[] = "Product slug already exists.";
        } else {
            // All checks passed, proceed with database update
            $sql_update = "UPDATE products SET
                name = ?, slug = ?, category_id = ?, description = ?, how_it_works = ?,
                health_benefits_text = ?, gauss_strength = ?, material_quality_design = ?,
                usage_guide_text = ?, price = ?, stock = ?, image_url_main = ?,
                is_featured = ?, is_on_sale = ?, sale_price = ?, updated_at = NOW()
                WHERE id = ?";

            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param(
                "ssissssssdsisidi",
                $submitted_data['name'], $submitted_data['slug'], $submitted_data['category_id'],
                $submitted_data['description'], $submitted_data['how_it_works'], $submitted_data['health_benefits_text'],
                $submitted_data['gauss_strength'], $submitted_data['material_quality_design'], $submitted_data['usage_guide_text'],
                $submitted_data['price'], $submitted_data['stock'], $image_filename_to_update,
                $submitted_data['is_featured'], $submitted_data['is_on_sale'], $submitted_data['sale_price'],
                $product_id
            );

            if ($stmt_update->execute()) {
                $_SESSION['success_message'] = "Product updated successfully!";
                header("Location: " . SITE_URL . "admin/products/");
                exit;
            } else {
                $errors[] = "Failed to update product in database: " . $stmt_update->error;
            }
        }
    }

    // If there were any errors, merge submitted data back into $product_data for form repopulation
    if (!empty($errors)) {
        $product_data = array_merge($product_data, $submitted_data);
        // Ensure the image displayed is the one that will be saved (or the original if upload failed)
        $product_data['image_url_main'] = $image_filename_to_update;
    }
}

// Fetch categories for dropdown
$sql_categories = "SELECT id, name FROM categories ORDER BY name ASC";
$result_categories = mysqli_query($conn, $sql_categories);
$categories = [];
if ($result_categories) {
    while ($row = mysqli_fetch_assoc($result_categories)) {
        $categories[] = $row;
    }
}

$page_title = "Edit Product";
$breadcrumbs = [
    ['name' => 'Products', 'link' => SITE_URL . 'admin/products/'],
    ['name' => 'Edit Product']
];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?>: <?php echo htmlspecialchars($product_data['name']); ?></h1>
    <a href="<?php echo SITE_URL; ?>admin/products/" class="btn btn-sm btn-outline-secondary">Back to Products</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <strong>Error!</strong> Please correct the following issues:
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

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
                        <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($product_data['slug']); ?>" required>
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
                        <label for="health_benefits_text">Specific Health Concerns Addressed</label>
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
                        <input type="checkbox" class="form-check-input" id="is_on_sale" name="is_on_sale" value="1" <?php echo !empty($product_data['is_on_sale']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_on_sale">Is on Sale?</label>
                    </div>
                    <div class="form-group mb-3" id="sale_price_group" style="<?php echo !empty($product_data['is_on_sale']) ? '' : 'display:none;'; ?>">
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
                        <label for="image_url_main">Main Product Image</label>
                        <input type="file" class="form-control" id="image_url_main" name="image_url_main">
                        <small class="form-text text-muted">Leave blank to keep current image.</small>
                        <?php if (!empty($product_data['image_url_main'])): ?>
                            <div class="mt-2">
                                <img src="<?php echo SITE_URL . 'uploads/' . htmlspecialchars($product_data['image_url_main']); ?>" alt="Current Image" style="max-width: 100px;">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
             <div class="card mb-3">
                <div class="card-header">Visibility</div>
                <div class="card-body">
                    <div class="form-group form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1" <?php echo !empty($product_data['is_featured']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_featured">Feature on homepage</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block w-100">Update Product</button>
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
require_once __DIR__ . '/../includes/footer.php';
?>