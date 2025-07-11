<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

$article_slug = $_GET['slug'] ?? null;
$post = null;

if (!$article_slug) {
    // Redirect or show 404 if no slug
    header("Location: " . SITE_URL . "/blog.php");
    exit;
}

$slug_esc = escape_string($article_slug);
$sql_article = "SELECT bp.*, u.username AS author_username
                FROM blog_posts bp
                LEFT JOIN users u ON bp.user_id = u.id
                WHERE bp.slug = '$slug_esc' AND bp.status = 'published'
                LIMIT 1";

$result_article = mysqli_query($conn, $sql_article);
if ($result_article && mysqli_num_rows($result_article) > 0) {
    $post = mysqli_fetch_assoc($result_article);
} else {
    // Article not found or not published, show a 404 or redirect
    http_response_code(404);
    $page_title = "Article Not Found";
    require_once 'templates/header.php';
    echo "<div class='container py-5 text-center'><p class='lead'>Sorry, the article you are looking for was not found or is not available.</p><a href='" . SITE_URL . "/blog.php' class='btn btn-primary'>Back to Blog</a></div>";
    require_once 'templates/footer.php';
    exit;
}

$page_title = htmlspecialchars($post['title']);
require_once 'templates/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-9 offset-lg-1">
            <article class="blog-post-full">
                <header class="mb-4">
                    <h1 class="fw-bolder mb-1"><?php echo htmlspecialchars($post['title']); ?></h1>
                    <div class="text-muted fst-italic mb-2">
                        Published on <?php echo date("F j, Y", strtotime($post['published_at'])); ?>
                        <?php if (!empty($post['author_username'])): ?>
                            by <?php echo htmlspecialchars($post['author_username']); ?>
                        <?php endif; ?>
                    </div>
                    <?php /* Add category links later if blog posts have categories */ ?>
                </header>

                <?php if (!empty($post['image_url'])): ?>
                <figure class="mb-4">
                    <img class="img-fluid rounded" src="<?php echo SITE_URL . '/uploads/blog/' . htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="max-height: 400px; width: auto; display: block; margin-left: auto; margin-right: auto;"/>
                </figure>
                <?php endif; ?>

                <section class="mb-5 article-content">
                    <?php
                    // IMPORTANT: If admin can save HTML, this needs to be properly sanitized on save,
                    // and potentially run through a purifier here too.
                    // For now, assuming admin-inputted HTML is safe or it's mostly plain text.
                    // Using htmlspecialchars_decode because admin might use a WYSIWYG that saves HTML entities.
                    // A better approach for user-generated HTML is a strict sanitizer like HTML Purifier.
                    echo htmlspecialchars_decode($post['content']);
                    ?>
                </section>
            </article>

            <hr>
            <!-- Comments section placeholder - can be added later -->
            <!--
            <div class="card my-4">
                <h5 class="card-header">Leave a Comment:</h5>
                <div class="card-body">
                    <form>
                        <div class="form-group">
                            <textarea class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
            -->

            <div class="text-center mt-4">
                <a href="<?php echo SITE_URL; ?>/blog.php" class="btn btn-outline-secondary">&laquo; Back to Blog</a>
            </div>

        </div>
    </div>
</div>

<style>
/* Basic styling for article content for readability */
.article-content p {
    line-height: 1.7;
    margin-bottom: 1rem;
}
.article-content h2, .article-content h3, .article-content h4 {
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
}
.article-content ul, .article-content ol {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}
.article-content blockquote {
    font-style: italic;
    color: #6c757d;
    border-left: 3px solid #007bff;
    padding-left: 1rem;
    margin-left: 0;
}
</style>

<?php
require_once 'templates/footer.php';
?>
