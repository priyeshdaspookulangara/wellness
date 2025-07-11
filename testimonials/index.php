<?php
session_start();
require_once __DIR__ . '/../config.php'; // Corrected
require_once __DIR__ . '/../includes/db.php'; // Corrected

$page_title = "Customer Testimonials";

// Fetch Testimonials Page Header from settings table
$testimonials_header_content = SITE_NAME . ' Testimonials'; // Default
$sql_header = "SELECT setting_value FROM settings WHERE setting_key = 'testimonials_page_header' LIMIT 1";
$result_header = mysqli_query($conn, $sql_header);
if ($result_header && mysqli_num_rows($result_header) > 0) {
    $row = mysqli_fetch_assoc($result_header);
    $testimonials_header_content = !empty($row['setting_value']) ? $row['setting_value'] : $testimonials_header_content;
}


// Fetch approved reviews/testimonials
$reviews = [];
$sql_reviews = "SELECT r.id, r.rating, r.comment, r.created_at, u.username AS user_username, p.name AS product_name, p.slug AS product_slug
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                JOIN products p ON r.product_id = p.id
                WHERE r.status = 'approved'
                ORDER BY r.created_at DESC";
// Add LIMIT and pagination later if needed

$result_reviews = mysqli_query($conn, $sql_reviews);
if ($result_reviews) {
    while ($row = mysqli_fetch_assoc($result_reviews)) {
        $reviews[] = $row;
    }
} else {
    // Handle error if necessary
    // echo "Error fetching reviews: " . mysqli_error($conn);
}

require_once __DIR__ . '/../templates/header.php'; // Corrected
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <h1 class="text-center mb-4"><?php echo htmlspecialchars($testimonials_header_content); ?></h1>
            <p class="text-center text-muted mb-5">Read what our valued customers have to say about their experience with our products and services.</p>

            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <i class="fas fa-star <?php echo ($i < $review['rating']) ? 'text-warning' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </h5>
                                <small class="text-muted"><?php echo date("F j, Y", strtotime($review['created_at'])); ?></small>
                            </div>
                            <p class="card-text">"<?php echo nl2br(htmlspecialchars($review['comment'])); ?>"</p>
                            <footer class="blockquote-footer">
                                <?php echo htmlspecialchars($review['user_username']); ?>
                                <?php if (!empty($review['product_name'])): ?>
                                    regarding <cite title="<?php echo htmlspecialchars($review['product_name']); ?>"><a href="<?php echo SITE_URL . 'product/?slug=' . htmlspecialchars($review['product_slug']); ?>"><?php echo htmlspecialchars($review['product_name']); ?></a></cite> <!-- Updated link -->
                                <?php endif; ?>
                            </footer>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    No testimonials available at the moment. Check back soon!
                </div>
            <?php endif; ?>
             <div class="text-center mt-5">
                <p>Want to share your experience? Purchase a product and leave a review on the product page!</p>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php'; // Corrected
?>
