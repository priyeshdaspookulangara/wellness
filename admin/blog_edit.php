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

$post_id_edit = (int)($_GET['id'] ?? 0);
if (!$post_id_edit) {
    $_SESSION['error_message'] = "Invalid blog post ID.";
    header("Location: blog_posts.php");
    exit;
}

$page_title = "Edit Blog Post";
require_once 'includes/header.php';

// Fetch existing post data
$sql_fetch_post = "SELECT * FROM blog_posts WHERE id = $post_id_edit";
$result_fetch_post = mysqli_query($conn, $sql_fetch_post);
if (!$result_fetch_post || mysqli_num_rows($result_fetch_post) === 0) {
    $_SESSION['error_message'] = "Blog post not found.";
    header("Location: blog_posts.php");
    exit;
}
$post_data = mysqli_fetch_assoc($result_fetch_post);
// Format published_at for date and time inputs
$post_data['published_at_date'] = $post_data['published_at'] ? date('Y-m-d', strtotime($post_data['published_at'])) : '';
$post_data['published_at_time'] = $post_data['published_at'] ? date('H:i', strtotime($post_data['published_at'])) : '';


$errors = [];
$author_id = $_SESSION['user_id']; // Or keep original author: $post_data['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_data = [
        'title' => trim($_POST['title'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'content' => trim($_POST['content'] ?? ''),
        'excerpt' => trim($_POST['excerpt'] ?? ''),
        'status' => in_array($_POST['status'] ?? 'draft', ['draft', 'published', 'archived']) ? $_POST['status'] : 'draft',
        'published_at_date' => trim($_POST['published_at_date'] ?? ''),
        'published_at_time' => trim($_POST['published_at_time'] ?? '')
    ];
    $post_data = array_merge($post_data, $new_data); // Update for sticky form

    if (empty($new_data['title'])) $errors[] = "Post title is required.";
    if (empty($new_data['content'])) $errors[] = "Post content is required.";

    if (empty($new_data['slug'])) {
        $new_data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $new_data['title']), '-'));
    } else {
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $new_data['slug'])) {
             $errors[] = "Slug can only contain lowercase letters, numbers, and hyphens.";
        }
    }
    $post_data['slug'] = $new_data['slug'];

    $published_at_sql = $post_data['published_at'] ? "'" . $post_data['published_at'] . "'" : 'NULL'; // Keep old if not changed
    if ($new_data['status'] == 'published') {
        if (!empty($new_data['published_at_date'])) {
            $datetime_str = $new_data['published_at_date'];
            $datetime_str .= !empty($new_data['published_at_time']) ? ' ' . $new_data['published_at_time'] : ' 00:00:00';
            $published_timestamp = strtotime($datetime_str);
            if ($published_timestamp) {
                $published_at_sql = "'" . date('Y-m-d H:i:s', $published_timestamp) . "'";
            } else {
                $errors[] = "Invalid published date/time format.";
            }
        } elseif (!$post_data['published_at']) { // If it was not published before and no new date is set
             $published_at_sql = "NOW()";
        }
        // If status is published and date is empty, but was previously published, keep old date.
    } elseif ($new_data['status'] != 'published' && $post_data['status'] == 'published') {
        // If changing status from published to draft/archived, clear published_at unless a new date is set for future publishing.
        // For simplicity, let's say changing from published to draft/archived keeps the published_at if it exists, or sets to NULL if it was NOW()
        // This logic can be fine-tuned. For now, if it was published, it might retain its published_at date.
        // $published_at_sql = 'NULL'; // Or keep for historical record.
    }


    $image_file_name = $post_data['image_url']; // Keep old image by default
    $upload_blog_dir = __DIR__ . '/../uploads/blog/';
    if (!is_dir($upload_blog_dir)) mkdir($upload_blog_dir, 0775, true);

    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == UPLOAD_ERR_OK) {
        $file_info = pathinfo($_FILES['image_url']['name']);
        $new_image_file_name = 'blog_' . uniqid() . '.' . strtolower($file_info['extension']);
        $target_file = $upload_blog_dir . $new_image_file_name;
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array(strtolower($file_info['extension']), $allowed_types)) {
            $errors[] = "Invalid image file type.";
        } elseif ($_FILES['image_url']['size'] > 2 * 1024 * 1024) {
            $errors[] = "Image file size exceeds 2MB limit.";
        } elseif (move_uploaded_file($_FILES['image_url']['tmp_name'], $target_file)) {
            if ($image_file_name && file_exists($upload_blog_dir . $image_file_name)) {
                unlink($upload_blog_dir . $image_file_name);
            }
            $image_file_name = $new_image_file_name;
        } else {
            $errors[] = "Failed to upload new image.";
        }
    }
    $post_data['image_url'] = $image_file_name;


    if (empty($errors)) {
        $title_esc = escape_string($new_data['title']);
        $slug_esc = escape_string($new_data['slug']);
        $content_esc = escape_string($new_data['content']);
        $excerpt_esc = escape_string($new_data['excerpt']);
        $status_esc = escape_string($new_data['status']);
        $image_file_name_sql = $image_file_name ? "'" . escape_string($image_file_name) . "'" : 'NULL';
        // user_id can be updated if you want to change author, or keep $post_data['user_id']
        // $author_id_sql = (int)$author_id; // current session admin
        $author_id_sql = (int)$post_data['user_id']; // keep original author

        $sql_check_slug = "SELECT id FROM blog_posts WHERE slug = '$slug_esc' AND id != $post_id_edit";
        $res_slug = mysqli_query($conn, $sql_check_slug);
        if (mysqli_num_rows($res_slug) > 0) {
            $errors[] = "Blog post slug already exists for another post.";
        } else {
            $sql_update = "UPDATE blog_posts SET
                            user_id = $author_id_sql,
                            title = '$title_esc',
                            slug = '$slug_esc',
                            content = '$content_esc',
                            excerpt = '$excerpt_esc',
                            image_url = $image_file_name_sql,
                            status = '$status_esc',
                            published_at = $published_at_sql,
                            updated_at = NOW()
                          WHERE id = $post_id_edit";

            if (mysqli_query($conn, $sql_update)) {
                $_SESSION['success_message'] = "Blog post updated successfully!";
                header("Location: blog_posts.php");
                exit;
            } else {
                $errors[] = "Failed to update blog post: " . mysqli_error($conn);
            }
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?>: <?php echo htmlspecialchars($post_data['title']); ?></h1>
    <a href="blog_posts.php" class="btn btn-sm btn-outline-secondary">Back to Blog Posts</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <strong>Error!</strong> Please correct the following issues:
        <ul><?php foreach ($errors as $error) echo "<li>" . htmlspecialchars($error) . "</li>"; ?></ul>
    </div>
<?php endif; ?>

<form action="blog_edit.php?id=<?php echo $post_id_edit; ?>" method="POST" enctype="multipart/form-data">
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
                        <label for="slug">Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($post_data['slug']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="15" required><?php echo htmlspecialchars($post_data['content']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="excerpt">Excerpt</label>
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
                        <label for="published_at_date">Publish Date</label>
                        <input type="date" class="form-control" id="published_at_date" name="published_at_date" value="<?php echo htmlspecialchars($post_data['published_at_date']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="published_at_time">Publish Time</label>
                        <input type="time" class="form-control" id="published_at_time" name="published_at_time" value="<?php echo htmlspecialchars($post_data['published_at_time']); ?>">
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">Featured Image</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="image_url">Image</label>
                        <input type="file" class="form-control-file" id="image_url" name="image_url">
                        <small class="form-text text-muted">Leave blank to keep current. Max 2MB.</small>
                        <?php if (!empty($post_data['image_url'])): ?>
                            <div class="mt-2">
                                <img src="<?php echo SITE_URL . '/uploads/blog/' . htmlspecialchars($post_data['image_url']); ?>" alt="Current Image" style="max-width: 100px; max-height: 100px;">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Update Post</button>
        </div>
    </div>
</form>

<?php
require_once 'includes/footer.php';
?>
