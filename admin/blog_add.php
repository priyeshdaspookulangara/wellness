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

$page_title = "Add New Blog Post";
require_once 'includes/header.php';

$errors = [];
$post_data = [
    'title' => '', 'slug' => '', 'content' => '', 'excerpt' => '',
    'status' => 'draft', 'published_at_date' => '', 'published_at_time' => ''
];
$author_id = $_SESSION['user_id']; // Current admin is the author

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_data['title'] = trim($_POST['title'] ?? '');
    $post_data['slug'] = trim($_POST['slug'] ?? '');
    $post_data['content'] = trim($_POST['content'] ?? ''); // Consider using a WYSIWYG editor and sanitize HTML
    $post_data['excerpt'] = trim($_POST['excerpt'] ?? '');
    $post_data['status'] = in_array($_POST['status'] ?? 'draft', ['draft', 'published', 'archived']) ? $_POST['status'] : 'draft';
    $post_data['published_at_date'] = trim($_POST['published_at_date'] ?? '');
    $post_data['published_at_time'] = trim($_POST['published_at_time'] ?? '');


    // Basic Validation
    if (empty($post_data['title'])) $errors[] = "Post title is required.";
    if (empty($post_data['content'])) $errors[] = "Post content is required.";

    if (empty($post_data['slug'])) {
        $post_data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $post_data['title']), '-'));
    } else {
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $post_data['slug'])) {
             $errors[] = "Slug can only contain lowercase letters, numbers, and hyphens.";
        }
    }

    $published_at_sql = 'NULL';
    if ($post_data['status'] == 'published') {
        if (!empty($post_data['published_at_date'])) {
            $datetime_str = $post_data['published_at_date'];
            if (!empty($post_data['published_at_time'])) {
                $datetime_str .= ' ' . $post_data['published_at_time'];
            } else {
                $datetime_str .= ' 00:00:00'; // Default time if not provided
            }
            $published_timestamp = strtotime($datetime_str);
            if ($published_timestamp) {
                $published_at_sql = "'" . date('Y-m-d H:i:s', $published_timestamp) . "'";
            } else {
                $errors[] = "Invalid published date/time format. Please use YYYY-MM-DD and HH:MM (optional).";
            }
        } else {
             $published_at_sql = "NOW()"; // Default to now if status is published and no date set
        }
    }


    // Image Upload Handling
    $image_file_name = null;
    $upload_blog_dir = __DIR__ . '/../uploads/blog/';
    if (!is_dir($upload_blog_dir)) mkdir($upload_blog_dir, 0775, true);

    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == UPLOAD_ERR_OK) {
        $file_info = pathinfo($_FILES['image_url']['name']);
        $image_file_name = 'blog_' . uniqid() . '.' . strtolower($file_info['extension']);
        $target_file = $upload_blog_dir . $image_file_name;
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array(strtolower($file_info['extension']), $allowed_types)) {
            $errors[] = "Invalid image file type.";
        } elseif ($_FILES['image_url']['size'] > 2 * 1024 * 1024) { // Max 2MB
            $errors[] = "Image file size exceeds 2MB limit.";
        } elseif (!move_uploaded_file($_FILES['image_url']['tmp_name'], $target_file)) {
            $errors[] = "Failed to upload image.";
            $image_file_name = null;
        }
    }

    if (empty($errors)) {
        $title_esc = escape_string($post_data['title']);
        $slug_esc = escape_string($post_data['slug']);
        // IMPORTANT: If content can be HTML from a WYSIWYG, use a proper HTML sanitizer here before saving.
        // For this project, we assume admin-inputted HTML is trusted or it's mostly plain text.
        // mysqli_real_escape_string is for SQL injection, not XSS from HTML.
        $content_esc = escape_string($post_data['content']);
        $excerpt_esc = escape_string($post_data['excerpt']);
        $status_esc = escape_string($post_data['status']);
        $image_file_name_sql = $image_file_name ? "'" . escape_string($image_file_name) . "'" : 'NULL';

        $sql_check_slug = "SELECT id FROM blog_posts WHERE slug = '$slug_esc'";
        $res_slug = mysqli_query($conn, $sql_check_slug);
        if (mysqli_num_rows($res_slug) > 0) {
            $errors[] = "Blog post slug already exists. Please choose a unique slug.";
        } else {
            $sql_insert = "INSERT INTO blog_posts (user_id, title, slug, content, excerpt, image_url, status, published_at, created_at, updated_at)
                           VALUES ($author_id, '$title_esc', '$slug_esc', '$content_esc', '$excerpt_esc', $image_file_name_sql, '$status_esc', $published_at_sql, NOW(), NOW())";

            if (mysqli_query($conn, $sql_insert)) {
                $_SESSION['success_message'] = "Blog post added successfully!";
                header("Location: blog_posts.php");
                exit;
            } else {
                $errors[] = "Failed to add blog post: " . mysqli_error($conn);
                if ($image_file_name) unlink($upload_blog_dir . $image_file_name);
            }
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
    <a href="blog_posts.php" class="btn btn-sm btn-outline-secondary">Back to Blog Posts</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <strong>Error!</strong> Please correct the following issues:
        <ul><?php foreach ($errors as $error) echo "<li>" . htmlspecialchars($error) . "</li>"; ?></ul>
    </div>
<?php endif; ?>

<form action="blog_add.php" method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">Post Content</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($post_data['title']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="slug">Slug (URL-friendly, auto-generated if empty)</label>
                        <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($post_data['slug']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="15" required><?php echo htmlspecialchars($post_data['content']); ?></textarea>
                        <small class="form-text text-muted">You can use basic HTML. For complex layouts, a WYSIWYG editor would be integrated here.</small>
                    </div>
                    <div class="form-group">
                        <label for="excerpt">Excerpt (Short summary)</label>
                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?php echo htmlspecialchars($post_data['excerpt']); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-header">Publish Settings</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="draft" <?php echo ($post_data['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo ($post_data['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                            <option value="archived" <?php echo ($post_data['status'] == 'archived') ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="published_at_date">Publish Date (Optional)</label>
                        <input type="date" class="form-control" id="published_at_date" name="published_at_date" value="<?php echo htmlspecialchars($post_data['published_at_date']); ?>">
                    </div>
                     <div class="form-group">
                        <label for="published_at_time">Publish Time (Optional)</label>
                        <input type="time" class="form-control" id="published_at_time" name="published_at_time" value="<?php echo htmlspecialchars($post_data['published_at_time']); ?>">
                         <small class="form-text text-muted">If status is 'Published' and date is empty, it will publish now.</small>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">Featured Image</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="image_url">Image</label>
                        <input type="file" class="form-control-file" id="image_url" name="image_url">
                        <small class="form-text text-muted">Max 2MB. JPG, PNG, GIF, WEBP.</small>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save Post</button>
        </div>
    </div>
</form>

<?php
// Simple script for a basic rich text editor or markdown editor could be added later.
// For now, it's a plain textarea.
require_once 'includes/footer.php';
?>
