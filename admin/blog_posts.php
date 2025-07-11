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

$page_title = "Manage Blog Posts";
require_once 'includes/header.php';

// Fetch blog posts
$sql_blog_posts = "SELECT bp.id, bp.title, bp.slug, bp.status, bp.published_at, u.username AS author_username
                   FROM blog_posts bp
                   LEFT JOIN users u ON bp.user_id = u.id
                   ORDER BY bp.created_at DESC";
$result_blog_posts = mysqli_query($conn, $sql_blog_posts);
$blog_posts_list = [];
if ($result_blog_posts) {
    while ($row = mysqli_fetch_assoc($result_blog_posts)) {
        $blog_posts_list[] = $row;
    }
} else {
    echo "<div class='alert alert-danger'>Error fetching blog posts: " . mysqli_error($conn) . "</div>";
}

// Handle messages
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="blog_add.php" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-plus"></i> Add New Post
        </a>
    </div>
</div>

<?php if ($success_message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Slug</th>
                <th>Author</th>
                <th>Status</th>
                <th>Published At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($blog_posts_list)): ?>
                <?php foreach ($blog_posts_list as $post_item): ?>
                    <tr>
                        <td><?php echo $post_item['id']; ?></td>
                        <td><?php echo htmlspecialchars($post_item['title']); ?></td>
                        <td><?php echo htmlspecialchars($post_item['slug']); ?></td>
                        <td><?php echo htmlspecialchars($post_item['author_username'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="badge badge-<?php
                                switch($post_item['status']) {
                                    case 'published': echo 'success'; break;
                                    case 'draft': echo 'secondary'; break;
                                    case 'archived': echo 'warning'; break;
                                    default: echo 'light';
                                }
                            ?>"><?php echo ucfirst(htmlspecialchars($post_item['status'])); ?></span>
                        </td>
                        <td><?php echo $post_item['published_at'] ? date("M j, Y", strtotime($post_item['published_at'])) : 'Not Published'; ?></td>
                        <td class="action-buttons">
                            <a href="blog_edit.php?id=<?php echo $post_item['id']; ?>" class="btn btn-sm btn-info" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="<?php echo SITE_URL . '/blog/' . htmlspecialchars($post_item['slug']); ?>" target="_blank" class="btn btn-sm btn-success" title="View Post"><i class="fas fa-eye"></i></a>
                            <a href="blog_delete.php?id=<?php echo $post_item['id']; ?>" class="btn btn-sm btn-danger confirm-delete" title="Delete"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No blog posts found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once 'includes/footer.php';
?>
