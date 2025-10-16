<?php
session_start();
require_once __DIR__ . '/../../config.php'; // Corrected
require_once __DIR__ . '/../../includes/db.php'; // Corrected

// Admin authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $_SESSION['admin_error'] = "Access denied.";
    header("Location: " . SITE_URL . "login/"); // Corrected redirect
    exit;
}

$page_title = "Add New Product";
$breadcrumbs = [
    ['name' => 'Products', 'link' => SITE_URL . 'admin/products/'],
    ['name' => 'Add New Product']
];
require_once __DIR__ . '/../includes/header.php'; // Corrected

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
$product_data = [
    'name' => '', 'slug' => '', 'category_id' => '', 'description' => '', 'how_it_works' => '',
    'health_benefits_text' => '', 'gauss_strength' => '', 'material_quality_design' => '',
    'usage_guide_text' => '', 'price' => '', 'stock' => 0, 'is_featured' => 0, 'is_on_sale' => 0,
    'sale_price' => null
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve form data
    $product_data['name'] = trim($_POST['name'] ?? '');
    $product_data['slug'] = trim($_POST['slug'] ?? '');
    $product_data['category_id'] = (int)($_POST['category_id'] ?? 0);
    $product_data['description'] = trim($_POST['description'] ?? '');
    $product_data['how_it_works'] = trim($_POST['how_it_works'] ?? '');
    $product_data['health_benefits_text'] = trim($_POST['health_benefits_text'] ?? '');
    $product_data['gauss_strength'] = trim($_POST['gauss_strength'] ?? '');
    $product_data['material_quality_design'] = trim($_POST['material_quality_design'] ?? '');
    $product_data['usage_guide_text'] = trim($_POST['usage_guide_text'] ?? '');
    $product_data['price'] = trim($_POST['price'] ?? '');
    $product_data['stock'] = (int)($_POST['stock'] ?? 0);
    $product_data['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
    $product_data['is_on_sale'] = isset($_POST['is_on_sale']) ? 1 : 0;
    $product_data['is_cod_available'] = isset($_POST['is_cod_available']) ? 1 : 0;
    $product_data['sale_price'] = !empty($_POST['sale_price']) ? trim($_POST['sale_price']) : null;

    // Validation (same as before)
    if (empty($product_data['name'])) $errors[] = "Product name is required.";
    // ... (other validations remain the same) ...
    if (empty($product_data['category_id'])) $errors[] = "Category is required.";
    if (empty($product_data['description'])) $errors[] = "Description is required.";
    if (!is_numeric($product_data['price']) || $product_data['price'] < 0) $errors[] = "Valid price is required.";
    if (!is_numeric($product_data['stock']) || $product_data['stock'] < 0) $errors[] = "Valid stock quantity is required.";
    if ($product_data['is_on_sale'] && (empty($product_data['sale_price']) || !is_numeric($product_data['sale_price']) || $product_data['sale_price'] < 0)) {
        $errors[] = "Valid sale price is required if product is marked as on sale.";
    }
     if (empty($product_data['slug'])) {
        $product_data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $product_data['name']), '-'));
    } else {
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $product_data['slug'])) {
             $errors[] = "Slug can only contain lowercase letters, numbers, and hyphens, and cannot start or end with a hyphen.";
        }
    }

    $image_file_name = null;
    if (isset($_FILES['image_url_main']) && $_FILES['image_url_main']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../uploads/'; // Corrected path to root uploads
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0775, true);
        }
        $file_info = pathinfo($_FILES['image_url_main']['name']);
        $image_file_name = uniqid('prod_') . '.' . strtolower($file_info['extension']);
        $target_file = $upload_dir . $image_file_name;
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array(strtolower($file_info['extension']), $allowed_types)) {
            $errors[] = "Invalid image file type. Allowed types: " . implode(', ', $allowed_types);
        } elseif ($_FILES['image_url_main']['size'] > 2 * 1024 * 1024) {
            $errors[] = "Image file size exceeds 2MB limit.";
        } elseif (!move_uploaded_file($_FILES['image_url_main']['tmp_name'], $target_file)) {
            $errors[] = "Failed to upload image.";
            $image_file_name = null;
        }
    }

    if (empty($errors)) {
        // Escaping and SQL insertion logic (same as before)
        $name_esc = escape_string($product_data['name']);
        $slug_esc = escape_string($product_data['slug']);
        // ... (all other escapes) ...
        $desc_esc = escape_string($product_data['description']);
        $how_it_works_esc = escape_string($product_data['how_it_works']);
        $health_benefits_esc = escape_string($product_data['health_benefits_text']);
        $gauss_esc = escape_string($product_data['gauss_strength']);
        $material_esc = escape_string($product_data['material_quality_design']);
        $usage_esc = escape_string($product_data['usage_guide_text']);
        $price_esc = (float)$product_data['price'];
        $stock_esc = (int)$product_data['stock'];
        $category_id_esc = (int)$product_data['category_id'];
        $is_featured_esc = (int)$product_data['is_featured'];
        $is_on_sale_esc = (int)$product_data['is_on_sale'];
        $is_cod_available_esc = (int)$product_data['is_cod_available'];
        $sale_price_esc = $product_data['is_on_sale'] && !empty($product_data['sale_price']) ? (float)$product_data['sale_price'] : 'NULL';
        $image_file_name_esc = $image_file_name ? "'" . escape_string($image_file_name) . "'" : 'NULL';

        $sql_check_slug = "SELECT id FROM products WHERE slug = '$slug_esc'";
        $res_slug = mysqli_query($conn, $sql_check_slug);
        if (mysqli_num_rows($res_slug) > 0) {
            $errors[] = "Product slug already exists. Please choose a unique slug.";
        } else {
            $sql_insert = "INSERT INTO products (name, slug, category_id, description, how_it_works, health_benefits_text, gauss_strength, material_quality_design, usage_guide_text, price, stock, image_url_main, is_featured, is_on_sale, is_cod_available, sale_price, created_at, updated_at)
                           VALUES ('$name_esc', '$slug_esc', $category_id_esc, '$desc_esc', '$how_it_works_esc', '$health_benefits_esc', '$gauss_esc', '$material_esc', '$usage_esc', $price_esc, $stock_esc, $image_file_name_esc, $is_featured_esc, $is_on_sale_esc, $is_cod_available_esc, $sale_price_esc, NOW(), NOW())";

            if (mysqli_query($conn, $sql_insert)) {
                $_SESSION['success_message'] = "Product added successfully!";
                header("Location: " . SITE_URL . "admin/products/"); // Corrected redirect
                exit;
            } else {
                $errors[] = "Failed to add product: " . mysqli_error($conn);
                if ($image_file_name && file_exists($upload_dir . $image_file_name)) {
                    unlink($upload_dir . $image_file_name);
                }
            }
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
    <a href="<?php echo SITE_URL; ?>admin/products/" class="btn btn-sm btn-outline-secondary">Back to Products</a> <!-- Corrected link -->
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

<form action="<?php echo SITE_URL; ?>admin/product_add/" method="POST" enctype="multipart/form-data"> <!-- Corrected form action -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Product Details</div>
                <div class="card-body">
                    <!-- Form fields as before, no changes to field names or values needed here for this step -->
                    <div class="form-group mb-3">
                        <label for="name">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product_data['name']); ?>" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="slug">Slug (URL-friendly name, auto-generated if empty)</label>
                        <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($product_data['slug']); ?>" placeholder="e.g., magnetic-therapy-bracelet">
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
                        <label for="health_benefits_text">Specific Health Concerns Addressed (comma-separated)</label>
                        <input type="text" class="form-control" id="health_benefits_text" name="health_benefits_text" value="<?php echo htmlspecialchars($product_data['health_benefits_text']); ?>" placeholder="e.g., Supports Joint Health, Aids Stress Reduction">
                    </div>
                    <div class="form-group mb-3">
                        <label for="material_quality_design">Material Quality & Design Details</label>
                        <textarea class="form-control" id="material_quality_design" name="material_quality_design" rows="3"><?php echo htmlspecialchars($product_data['material_quality_design']); ?></textarea>
                    </div>
                    <div class="form-group mb-3">
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
                    <div class="form-group form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="is_cod_available" name="is_cod_available" value="1" checked>
                        <label class="form-check-label" for="is_cod_available">Pay on Delivery</label>
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
                        <input type="text" class="form-control" id="gauss_strength" name="gauss_strength" value="<?php echo htmlspecialchars($product_data['gauss_strength']); ?>" placeholder="e.g., 3000 Gauss">
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">Media</div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label for="image_url_main">Main Product Image</label>
                        <input type="file" class="form-control" id="image_url_main" name="image_url_main"> <!-- BS5 uses form-control for file inputs -->
                        <small class="form-text text-muted">Max 2MB. Allowed types: JPG, PNG, GIF, WEBP.</small>
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
            <button type="submit" class="btn btn-primary btn-block w-100">Add Product</button>
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
require_once __DIR__ . '/../includes/footer.php'; // Corrected
?>
