<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

$page_title = "Wellness Blog";

// Fetch published blog posts
$posts = [];
// Add pagination later: e.g., $limit = 5; $offset = ($current_page - 1) * $limit;
$sql_posts = "SELECT id, title, slug, excerpt, image_url, published_at, user_id
              FROM blog_posts
              WHERE status = 'published'
              ORDER BY published_at DESC";
              // Add " LIMIT $limit OFFSET $offset" for pagination

$result_posts = mysqli_query($conn, $sql_posts);
if ($result_posts) {
    while ($row = mysqli_fetch_assoc($result_posts)) {
        // Optionally fetch author name if needed, or handle in single article view
        $posts[] = $row;
    }
} else {
    // echo "Error fetching blog posts: " . mysqli_error($conn);
}

require_once 'templates/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <h1 class="text-center mb-4"><?php echo $page_title; ?></h1>
            <p class="text-center text-muted mb-5">Insights, tips, and articles on magnetic therapy, general wellness, and product guides.</p>

            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="card blog-post-item mb-4 shadow-sm">
                        <div class="row no-gutters">
                            <?php if (!empty($post['image_url'])): ?>
                            <div class="col-md-4">
                                <a href="<?php echo SITE_URL . '/blog/' . htmlspecialchars($post['slug']); ?>">
                                    <img src="<?php echo SITE_URL . '/uploads/blog/' . htmlspecialchars($post['image_url']); ?>" class="card-img" alt="<?php echo htmlspecialchars($post['title']); ?>" style="object-fit: cover; height: 100%;">
                                </a>
                            </div>
                            <?php endif; ?>
                            <div class="col-md-<?php echo !empty($post['image_url']) ? '8' : '12'; ?>">
                                <div class="card-body d-flex flex-column h-100">
                                    <h3 class="card-title"><a href="<?php echo SITE_URL . '/blog/' . htmlspecialchars($post['slug']); ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                                    <p class="card-text text-muted">
                                        <small>Published on <?php echo date("F j, Y", strtotime($post['published_at'])); ?></small>
                                        <?php /* Add author later if desired: by AuthorName */ ?>
                                    </p>
                                    <p class="card-text flex-grow-1"><?php echo nl2br(htmlspecialchars($post['excerpt'] ?? substr(strip_tags($post['content'] ?? ''), 0, 150) . '...')); ?></p>
                                    <a href="<?php echo SITE_URL . '/blog/' . htmlspecialchars($post['slug']); ?>" class="btn btn-sm btn-outline-primary align-self-start">Read More &raquo;</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Pagination Placeholder -->
                <!--
                <nav aria-label="Blog navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                    </ul>
                </nav>
                -->

            <?php else: ?>
                <div class="alert alert-info text-center">
                    No blog posts found. Please check back later!
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
