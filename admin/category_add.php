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

$page_title = "Add New Category";
require_once 'includes/header.php';

$errors = [];
$category_data = ['name' => '', 'slug' => '', 'description' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_data['name'] = trim($_POST['name'] ?? '');
    $category_data['slug'] = trim($_POST['slug'] ?? '');
    $category_data['description'] = trim($_POST['description'] ?? '');

    // Basic Validation
    if (empty($category_data['name'])) {
        $errors[] = "Category name is required.";
    }

    if (empty($category_data['slug'])) {
        // Basic slug generation from name
        $category_data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $category_data['name']), '-'));
    } else {
        // Validate provided slug
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $category_data['slug'])) {
             $errors[] = "Slug can only contain lowercase letters, numbers, and hyphens, and cannot start or end with a hyphen.";
        }
    }

    // Image Upload Handling (Optional for categories)
    $image_file_name = null;
    $upload_cat_dir = __DIR__ . '/../uploads/categories/'; // Create this directory
    if (!is_dir($upload_cat_dir)) {
        mkdir($upload_cat_dir, 0775, true);
    }

    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == UPLOAD_ERR_OK) {
        $file_info = pathinfo($_FILES['image_url']['name']);
        $image_file_name = 'cat_' . uniqid() . '.' . strtolower($file_info['extension']);
        $target_file = $upload_cat_dir . $image_file_name;

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array(strtolower($file_info['extension']), $allowed_types)) {
            $errors[] = "Invalid image file type. Allowed types: " . implode(', ', $allowed_types);
        } elseif ($_FILES['image_url']['size'] > 1 * 1024 * 1024) { // Max 1MB for category images
            $errors[] = "Image file size exceeds 1MB limit.";
        } elseif (!move_uploaded_file($_FILES['image_url']['tmp_name'], $target_file)) {
            $errors[] = "Failed to upload category image.";
            $image_file_name = null;
        }
    }


    if (empty($errors)) {
        $name_esc = escape_string($category_data['name']);
        $slug_esc = escape_string($category_data['slug']);
        $desc_esc = escape_string($category_data['description']);
        $image_file_name_esc = $image_file_name ? "'" . escape_string($image_file_name) . "'" : 'NULL';

        // Check if slug already exists
        $sql_check_slug = "SELECT id FROM categories WHERE slug = '$slug_esc'";
        $res_slug = mysqli_query($conn, $sql_check_slug);
        if (mysqli_num_rows($res_slug) > 0) {
            $errors[] = "Category slug already exists. Please choose a unique slug.";
        } else {
            $sql_insert = "INSERT INTO categories (name, slug, description, image_url, created_at, updated_at)
                           VALUES ('$name_esc', '$slug_esc', '$desc_esc', $image_file_name_esc, NOW(), NOW())";

            if (mysqli_query($conn, $sql_insert)) {
                $_SESSION['success_message'] = "Category added successfully!";
                header("Location: categories.php");
                exit;
            } else {
                $errors[] = "Failed to add category: " . mysqli_error($conn);
                if ($image_file_name && file_exists($upload_cat_dir . $image_file_name)) {
                    unlink($upload_cat_dir . $image_file_name);
                }
            }
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
    <a href="categories.php" class="btn btn-sm btn-outline-secondary">Back to Categories</a>
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

<form action="category_add.php" method="POST" enctype="multipart/form-data">
    <div class="card">
        <div class="card-header">Category Details</div>
        <div class="card-body">
            <div class="form-group">
                <label for="name">Category Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($category_data['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="slug">Slug (URL-friendly name, auto-generated if empty)</label>
                <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($category_data['slug']); ?>" placeholder="e.g., magnetic-bracelets">
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($category_data['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="image_url">Category Image (Optional)</label>
                <input type="file" class="form-control-file" id="image_url" name="image_url">
                <small class="form-text text-muted">Max 1MB. Allowed types: JPG, PNG, GIF, WEBP.</small>
            </div>
            <button type="submit" class="btn btn-primary">Add Category</button>
        </div>
    </div>
</form>

<?php
require_once 'includes/footer.php';
?>
