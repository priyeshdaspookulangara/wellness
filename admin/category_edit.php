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

$page_title = "Edit Category";
require_once 'includes/header.php';

$category_id = (int)($_GET['id'] ?? 0);
if (!$category_id) {
    $_SESSION['error_message'] = "Invalid category ID.";
    header("Location: categories.php");
    exit;
}

// Fetch existing category data
$sql_category = "SELECT * FROM categories WHERE id = $category_id";
$result_category = mysqli_query($conn, $sql_category);
if (!$result_category || mysqli_num_rows($result_category) === 0) {
    $_SESSION['error_message'] = "Category not found.";
    header("Location: categories.php");
    exit;
}
$category_data = mysqli_fetch_assoc($result_category);
// Ensure all expected keys exist for the form
$category_data['name'] = $category_data['name'] ?? '';
$category_data['slug'] = $category_data['slug'] ?? '';
$category_data['description'] = $category_data['description'] ?? '';
$category_data['image_url'] = $category_data['image_url'] ?? null;


$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_category_data = [
        'name' => trim($_POST['name'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'description' => trim($_POST['description'] ?? '')
    ];
    $category_data = array_merge($category_data, $new_category_data);


    // Basic Validation
    if (empty($new_category_data['name'])) {
        $errors[] = "Category name is required.";
    }
    if (empty($new_category_data['slug'])) {
        $new_category_data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $new_category_data['name']), '-'));
    } else {
         if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $new_category_data['slug'])) {
             $errors[] = "Slug can only contain lowercase letters, numbers, and hyphens, and cannot start or end with a hyphen.";
        }
    }
    $category_data['slug'] = $new_category_data['slug'];


    // Image Upload Handling
    $image_file_name = $category_data['image_url']; // Keep old image by default
    $upload_cat_dir = __DIR__ . '/../uploads/categories/';
     if (!is_dir($upload_cat_dir)) {
        mkdir($upload_cat_dir, 0775, true);
    }

    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == UPLOAD_ERR_OK) {
        $file_info = pathinfo($_FILES['image_url']['name']);
        $new_image_file_name = 'cat_' . uniqid() . '.' . strtolower($file_info['extension']);
        $target_file = $upload_cat_dir . $new_image_file_name;

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array(strtolower($file_info['extension']), $allowed_types)) {
            $errors[] = "Invalid image file type. Allowed types: " . implode(', ', $allowed_types);
        } elseif ($_FILES['image_url']['size'] > 1 * 1024 * 1024) { // Max 1MB
            $errors[] = "Image file size exceeds 1MB limit.";
        } elseif (move_uploaded_file($_FILES['image_url']['tmp_name'], $target_file)) {
            // Delete old image if a new one is successfully uploaded
            if ($image_file_name && file_exists($upload_cat_dir . $image_file_name)) {
                unlink($upload_cat_dir . $image_file_name);
            }
            $image_file_name = $new_image_file_name;
        } else {
            $errors[] = "Failed to upload new category image.";
        }
    }
    $category_data['image_url'] = $image_file_name;


    if (empty($errors)) {
        $name_esc = escape_string($new_category_data['name']);
        $slug_esc = escape_string($new_category_data['slug']);
        $desc_esc = escape_string($new_category_data['description']);
        $image_file_name_sql = $image_file_name ? "'" . escape_string($image_file_name) . "'" : 'NULL';

        // Check if slug already exists for a *different* category
        $sql_check_slug = "SELECT id FROM categories WHERE slug = '$slug_esc' AND id != $category_id";
        $res_slug = mysqli_query($conn, $sql_check_slug);
        if (mysqli_num_rows($res_slug) > 0) {
            $errors[] = "Category slug already exists for another category. Please choose a unique slug.";
        } else {
            $sql_update = "UPDATE categories SET
                            name = '$name_esc',
                            slug = '$slug_esc',
                            description = '$desc_esc',
                            image_url = $image_file_name_sql,
                            updated_at = NOW()
                          WHERE id = $category_id";

            if (mysqli_query($conn, $sql_update)) {
                $_SESSION['success_message'] = "Category updated successfully!";
                header("Location: categories.php");
                exit;
            } else {
                $errors[] = "Failed to update category: " . mysqli_error($conn);
            }
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?>: <?php echo htmlspecialchars($category_data['name']); ?></h1>
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

<form action="category_edit.php?id=<?php echo $category_id; ?>" method="POST" enctype="multipart/form-data">
    <div class="card">
        <div class="card-header">Category Details</div>
        <div class="card-body">
            <div class="form-group">
                <label for="name">Category Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($category_data['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($category_data['slug']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($category_data['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="image_url">Category Image (Optional)</label>
                <input type="file" class="form-control-file" id="image_url" name="image_url">
                <small class="form-text text-muted">Leave blank to keep current image. Max 1MB. Allowed types: JPG, PNG, GIF, WEBP.</small>
                <?php if (!empty($category_data['image_url'])): ?>
                    <div class="mt-2">
                        <img src="<?php echo SITE_URL . '/uploads/categories/' . htmlspecialchars($category_data['image_url']); ?>" alt="Current Image" style="max-width: 100px; max-height: 100px;">
                        <p><small>Current image: <?php echo htmlspecialchars($category_data['image_url']); ?></small></p>
                    </div>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Update Category</button>
        </div>
    </div>
</form>

<?php
require_once 'includes/footer.php';
?>
