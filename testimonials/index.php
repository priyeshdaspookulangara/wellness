<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../templates/header.php';

$pageTitle = 'Testimonials';

// Handle testimonial submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
    $comment = trim(filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING));

    if ($rating && !empty($comment)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO testimonials (user_id, rating, comment) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $rating, $comment]);
            $_SESSION['message'] = 'Thank you for your feedback!';
            $_SESSION['message_type'] = 'success';
        } catch (PDOException $e) {
            error_log("Testimonial submission error: " . $e->getMessage());
            $_SESSION['message'] = 'We could not save your testimonial at this time. Please try again later.';
            $_SESSION['message_type'] = 'danger';
        }
        // Redirect to the same page to prevent form resubmission
        header('Location: ' . SITE_URL . '/testimonials/');
        exit;
    } else {
        $_SESSION['message'] = 'Please provide a valid rating and comment.';
        $_SESSION['message_type'] = 'danger';
    }
}

// Fetch approved testimonials to display
try {
    $stmt = $pdo->prepare("SELECT t.comment, t.rating, u.username FROM testimonials t JOIN users u ON t.user_id = u.id WHERE t.is_approved = 1 ORDER BY t.created_at DESC");
    $stmt->execute();
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Failed to fetch testimonials: " . $e->getMessage());
    $testimonials = [];
    // Optionally, set a user-facing error message
    // $page_error = "Could not load testimonials.";
}

?>

<div class="row">
    <div class="col-12">
        <h1>Customer Testimonials</h1>
        <p>See what our customers are saying about their experience with <?php echo SITE_NAME; ?>.</p>
    </div>
</div>

<?php if (isset($_SESSION['user_id'])): ?>
<div class="row my-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3>Leave a Testimonial</h3>
            </div>
            <div class="card-body">
                <form action="<?php echo SITE_URL; ?>/testimonials/" method="post">
                    <div class="form-group">
                        <label for="rating">Rating</label>
                        <select class="form-control" id="rating" name="rating" required>
                            <option value="">Choose a rating...</option>
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Very Good</option>
                            <option value="3">3 - Good</option>
                            <option value="2">2 - Fair</option>
                            <option value="1">1 - Poor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="comment">Your Feedback</label>
                        <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-info my-4">
    You must be <a href="<?php echo SITE_URL; ?>/login/">logged in</a> to leave a testimonial.
</div>
<?php endif; ?>

<hr>

<div class="row mt-4">
    <div class="col-12">
        <h2>What Our Customers Say</h2>
        <?php if (!empty($testimonials)): ?>
            <?php foreach ($testimonials as $testimonial): ?>
                <blockquote class="blockquote">
                    <p class="mb-0"><?php echo htmlspecialchars($testimonial['comment']); ?></p>
                    <footer class="blockquote-footer">
                        <?php echo htmlspecialchars($testimonial['username']); ?>
                        <span class="text-warning ml-2">
                            <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                            <?php for ($i = $testimonial['rating']; $i < 5; $i++): ?>
                                <i class="far fa-star"></i>
                            <?php endfor; ?>
                        </span>
                    </footer>
                </blockquote>
                <hr>
            <?php endforeach; ?>
        <?php else: ?>
            <p>There are no testimonials yet. Be the first to share your feedback!</p>
        <?php endif; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
