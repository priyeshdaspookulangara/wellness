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

// CSRF token validation would be good here

$post_id_delete = (int)($_GET['id'] ?? 0);

if (!$post_id_delete) {
    $_SESSION['error_message'] = "Invalid blog post ID for deletion.";
    header("Location: blog_posts.php");
    exit;
}

// Get the image filename to delete it from the server
$sql_get_post_image = "SELECT image_url FROM blog_posts WHERE id = $post_id_delete";
$result_post_image = mysqli_query($conn, $sql_get_post_image);
$image_to_delete_blog = null;
if ($result_post_image && mysqli_num_rows($result_post_image) > 0) {
    $row = mysqli_fetch_assoc($result_post_image);
    $image_to_delete_blog = $row['image_url'];
}

// Attempt to delete the blog post
$sql_delete_post = "DELETE FROM blog_posts WHERE id = $post_id_delete";

if (mysqli_query($conn, $sql_delete_post)) {
    if (mysqli_affected_rows($conn) > 0) {
        // If post deleted successfully, try to delete its image
        if ($image_to_delete_blog) {
            $upload_blog_dir_del = __DIR__ . '/../uploads/blog/';
            $image_path_blog = $upload_blog_dir_del . $image_to_delete_blog;
            if (file_exists($image_path_blog)) {
                unlink($image_path_blog); // Delete the file
            }
        }
        $_SESSION['success_message'] = "Blog post (ID: $post_id_delete) deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Blog post (ID: $post_id_delete) not found or already deleted.";
    }
} else {
    $_SESSION['error_message'] = "Failed to delete blog post (ID: $post_id_delete). Error: " . mysqli_error($conn);
}

header("Location: blog_posts.php");
exit;
?>
