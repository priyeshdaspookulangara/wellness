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

$page_title = "Edit Product";
require_once 'includes/header.php';

$product_id = (int)($_GET['id'] ?? 0);
if (!$product_id) {
    $_SESSION['error_message'] = "Invalid product ID.";
    header("Location: products.php");
    exit;
}

// Fetch existing product data
$sql_product = "SELECT * FROM products WHERE id = $product_id";
$result_product = mysqli_query($conn, $sql_product);
if (!$result_product || mysqli_num_rows($result_product) === 0) {
    $_SESSION['error_message'] = "Product not found.";
    header("Location: products.php");
    exit;
}
$product_data = mysqli_fetch_assoc($result_product);
// Convert NULLs to empty strings for form values if needed, or handle in HTML
foreach ($product_data as $key => $value) {
    if ($value === null) {
        $product_data[$key] = '';
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

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve form data
    $new_product_data = [
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
    // Update product_data with new values for sticky form
    $product_data = array_merge($product_data, $new_product_data);


    // Basic Validation (similar to add product)
    if (empty($new_product_data['name'])) $errors[] = "Product name is required.";
    if (empty($new_product_data['category_id'])) $errors[] = "Category is required.";
    if (empty($new_product_data['description'])) $errors[] = "Description is required.";
    if (!is_numeric($new_product_data['price']) || $new_product_data['price'] < 0) $errors[] = "Valid price is required.";
    if (!is_numeric($new_product_data['stock']) || $new_product_data['stock'] < 0) $errors[] = "Valid stock quantity is required.";
     if ($new_product_data['is_on_sale'] && (empty($new_product_data['sale_price']) || !is_numeric($new_product_data['sale_price']) || $new_product_data['sale_price'] < 0)) {
        $errors[] = "Valid sale price is required if product is marked as on sale.";
    }
    if (empty($new_product_data['slug'])) {
        $new_product_data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $new_product_data['name']), '-'));
    } else {
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $new_product_data['slug'])) {
             $errors[] = "Slug can only contain lowercase letters, numbers, and hyphens, and cannot start or end with a hyphen.";
        }
    }
    $product_data['slug'] = $new_product_data['slug']; // ensure product_data used for form has the potentially corrected slug


    // Image Upload Handling (Simplified)
    $image_file_name = $product_data['image_url_main']; // Keep old image by default
    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0775, true);
    }

    if (isset($_FILES['image_url_main']) && $_FILES['image_url_main']['error'] == UPLOAD_ERR_OK) {
        $file_info = pathinfo($_FILES['image_url_main']['name']);
        $new_image_file_name = uniqid('prod_') . '.' . strtolower($file_info['extension']);
        $target_file = $upload_dir . $new_image_file_name;

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array(strtolower($file_info['extension']), $allowed_types)) {
            $errors[] = "Invalid image file type. Allowed types: " . implode(', ', $allowed_types);
        } elseif ($_FILES['image_url_main']['size'] > 2 * 1024 * 1024) { // Max 2MB
            $errors[] = "Image file size exceeds 2MB limit.";
        } elseif (move_uploaded_file($_FILES['image_url_main']['tmp_name'], $target_file)) {
            // Delete old image if a new one is successfully uploaded and an old one exists
            if ($image_file_name && file_exists($upload_dir . $image_file_name)) {
                unlink($upload_dir . $image_file_name);
            }
            $image_file_name = $new_image_file_name; // Set to new image name
        } else {
            $errors[] = "Failed to upload new image.";
        }
    }
    $product_data['image_url_main'] = $image_file_name; // Update for sticky form


    if (empty($errors)) {
        $name_esc = escape_string($new_product_data['name']);
        $slug_esc = escape_string($new_product_data['slug']);
        $desc_esc = escape_string($new_product_data['description']);
        $how_it_works_esc = escape_string($new_product_data['how_it_works']);
        $health_benefits_esc = escape_string($new_product_data['health_benefits_text']);
        $gauss_esc = escape_string($new_product_data['gauss_strength']);
        $material_esc = escape_string($new_product_data['material_quality_design']);
        $usage_esc = escape_string($new_product_data['usage_guide_text']);
        $price_esc = (float)$new_product_data['price'];
        $stock_esc = (int)$new_product_data['stock'];
        $category_id_esc = (int)$new_product_data['category_id'];
        $is_featured_esc = (int)$new_product_data['is_featured'];
        $is_on_sale_esc = (int)$new_product_data['is_on_sale'];
        $sale_price_sql = ($is_on_sale_esc && !empty($new_product_data['sale_price'])) ? (float)$new_product_data['sale_price'] : 'NULL';
        $image_file_name_sql = $image_file_name ? "'" . escape_string($image_file_name) . "'" : 'NULL';

        // Check if slug already exists for a *different* product
        $sql_check_slug = "SELECT id FROM products WHERE slug = '$slug_esc' AND id != $product_id";
        $res_slug = mysqli_query($conn, $sql_check_slug);
        if (mysqli_num_rows($res_slug) > 0) {
            $errors[] = "Product slug already exists for another product. Please choose a unique slug.";
        } else {
            $sql_update = "UPDATE products SET
                            name = '$name_esc',
                            slug = '$slug_esc',
                            category_id = $category_id_esc,
                            description = '$desc_esc',
                            how_it_works = '$how_it_works_esc',
                            health_benefits_text = '$health_benefits_esc',
                            gauss_strength = '$gauss_esc',
                            material_quality_design = '$material_esc',
                            usage_guide_text = '$usage_esc',
                            price = $price_esc,
                            stock = $stock_esc,
                            image_url_main = $image_file_name_sql,
                            is_featured = $is_featured_esc,
                            is_on_sale = $is_on_sale_esc,
                            sale_price = $sale_price_sql,
                            updated_at = NOW()
                          WHERE id = $product_id";

            if (mysqli_query($conn, $sql_update)) {
                $_SESSION['success_message'] = "Product updated successfully!";
                header("Location: products.php");
                exit;
            } else {
                $errors[] = "Failed to update product: " . mysqli_error($conn);
            }
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?>: <?php echo htmlspecialchars($product_data['name']); ?></h1>
    <a href="products.php" class="btn btn-sm btn-outline-secondary">Back to Products</a>
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

<form action="product_edit.php?id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Product Details</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product_data['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($product_data['slug']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($product_data['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="how_it_works">How It Works</label>
                        <textarea class="form-control" id="how_it_works" name="how_it_works" rows="3"><?php echo htmlspecialchars($product_data['how_it_works']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="health_benefits_text">Specific Health Concerns Addressed (comma-separated)</label>
                        <input type="text" class="form-control" id="health_benefits_text" name="health_benefits_text" value="<?php echo htmlspecialchars($product_data['health_benefits_text']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="material_quality_design">Material Quality & Design Details</label>
                        <textarea class="form-control" id="material_quality_design" name="material_quality_design" rows="3"><?php echo htmlspecialchars($product_data['material_quality_design']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="usage_guide_text">Usage Guides / Best Practices</label>
                        <textarea class="form-control" id="usage_guide_text" name="usage_guide_text" rows="3"><?php echo htmlspecialchars($product_data['usage_guide_text']); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">Pricing & Stock</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="price">Price ($)</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($product_data['price']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="stock">Stock Quantity</label>
                        <input type="number" class="form-control" id="stock" name="stock" value="<?php echo htmlspecialchars($product_data['stock']); ?>" required>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="is_on_sale" name="is_on_sale" value="1" <?php echo ($product_data['is_on_sale'] == 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_on_sale">Is on Sale?</label>
                    </div>
                    <div class="form-group" id="sale_price_group" style="<?php echo ($product_data['is_on_sale'] == 1) ? '' : 'display:none;'; ?>">
                        <label for="sale_price">Sale Price ($)</label>
                        <input type="number" step="0.01" class="form-control" id="sale_price" name="sale_price" value="<?php echo htmlspecialchars($product_data['sale_price'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">Organization</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($product_data['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="gauss_strength">Magnetic Strength (Gauss)</label>
                        <input type="text" class="form-control" id="gauss_strength" name="gauss_strength" value="<?php echo htmlspecialchars($product_data['gauss_strength']); ?>">
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">Media</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="image_url_main">Main Product Image</label>
                        <input type="file" class="form-control-file" id="image_url_main" name="image_url_main">
                        <small class="form-text text-muted">Leave blank to keep current image. Max 2MB. Allowed types: JPG, PNG, GIF, WEBP.</small>
                        <?php if (!empty($product_data['image_url_main'])): ?>
                            <div class="mt-2">
                                <img src="<?php echo SITE_URL . '/uploads/' . htmlspecialchars($product_data['image_url_main']); ?>" alt="Current Image" style="max-width: 100px; max-height: 100px;">
                                <p><small>Current image: <?php echo htmlspecialchars($product_data['image_url_main']); ?></small></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">Visibility</div>
                <div class="card-body">
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1" <?php echo ($product_data['is_featured'] == 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_featured">Feature on homepage</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Update Product</button>
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
require_once 'includes/footer.php';
?>
