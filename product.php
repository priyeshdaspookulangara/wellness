<?php
require_once __DIR__ . '/includes/db.php';

// Get product slug from URL
$product_slug = $_GET['slug'] ?? null;
$product = null;

if ($product_slug) {
    // In a real application, you would fetch the product details from the database using the slug
    // For now, let's use a placeholder product
    $product_slug_escaped = escape_string($product_slug); // Assuming escape_string is available from db.php
    if ($product_slug_escaped == 'magnetic-therapy-bracelet-pro') {
        $product = [
            'id' => 101,
            'name' => 'Magnetic Therapy Bracelet Pro',
            'slug' => 'magnetic-therapy-bracelet-pro',
            'description' => 'Experience the next level of magnetic therapy with our Pro Bracelet. Crafted with high-quality materials and powerful neodymium magnets.',
            'how_it_works' => 'The bracelet features strategically placed magnets that create a magnetic field around your wrist. This is believed to improve circulation and promote natural healing processes.',
            'health_benefits_text' => 'Supports Joint Health, Aids in Stress Reduction, May Improve Sleep Quality, Enhances Circulation.',
            'gauss_strength' => '3500 Gauss per magnet',
            'material_quality_design' => 'Made from hypoallergenic titanium, with a sleek, modern design. Durable and comfortable for everyday wear. Features secure clasp mechanism.',
            'usage_guide_text' => 'Wear daily on either wrist. For best results, ensure magnets are close to the skin. Clean with a soft cloth. Avoid direct contact with water if not specified as waterproof.',
            'price' => '79.99',
            'stock' => 50,
            'image_url_main' => 'https://via.placeholder.com/600x400.png?text=Pro+Bracelet+Main',
            'image_gallery_urls' => [
                'https://via.placeholder.com/100x100.png?text=Thumb+1',
                'https://via.placeholder.com/100x100.png?text=Thumb+2',
                'https://via.placeholder.com/100x100.png?text=Thumb+3',
                'https://via.placeholder.com/600x400.png?text=Gallery+Image+1', // For actual zoom
                'https://via.placeholder.com/600x400.png?text=Gallery+Image+2'
            ],
            'category_name' => 'Magnetic Bracelets', // Example
            'category_slug' => 'magnetic-bracelets' // Example
        ];
    } else {
         $product = [ // A more generic fallback
            'id' => 102,
            'name' => 'Sample Wellness Product',
            'slug' => $product_slug_escaped,
            'description' => 'This is a detailed description of the sample wellness product. It highlights its key features and benefits.',
            'how_it_works' => 'The mechanism of action for this product involves natural ingredients / specific design features that contribute to overall well-being.',
            'health_benefits_text' => 'General Wellness Support, Boosts Energy Levels (example).',
            'gauss_strength' => 'N/A',
            'material_quality_design' => 'High-quality, durable materials. Ergonomically designed for comfort and effectiveness.',
            'usage_guide_text' => 'Use as directed. Refer to package insert for detailed instructions.',
            'price' => '49.99',
            'stock' => 100,
            'image_url_main' => 'https://via.placeholder.com/600x400.png?text=Sample+Product',
            'image_gallery_urls' => [],
            'category_name' => 'General Wellness',
            'category_slug' => 'general-wellness'
        ];
    }
}

if (!$product) {
    // Optional: Redirect to 404 page or show an error
    $page_title = "Product Not Found";
    require_once 'templates/header.php';
    echo "<p>The product you are looking for was not found.</p>";
    require_once 'templates/footer.php';
    exit;
}

$page_title = htmlspecialchars($product['name']);
require_once 'templates/header.php';
?>

<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo htmlspecialchars($product['category_slug']); ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
  </ol>
</nav>

<div class="row">
    <!-- Product Image Gallery -->
    <div class="col-md-6">
        <img src="<?php echo htmlspecialchars($product['image_url_main']); ?>" class="img-fluid mb-3 main-product-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
        <div class="row product-thumbnails">
            <?php if (!empty($product['image_gallery_urls']) && count($product['image_gallery_urls']) > 3) : // Basic check for gallery thumbs vs main images for zoom ?>
                <?php for($i = 0; $i < 3; $i++): ?>
                     <div class="col-3">
                        <a href="<?php echo htmlspecialchars($product['image_gallery_urls'][$i+3]); /* Assumes first 3 are thumbs, next are large */ ?>" data-gallery="product-gallery">
                            <img src="<?php echo htmlspecialchars($product['image_gallery_urls'][$i]); ?>" class="img-thumbnail" alt="Thumbnail <?php echo $i+1; ?>">
                        </a>
                    </div>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
        <!-- Basic JS for swapping main image - will need enhancement for real zoom/gallery -->
        <script>
            $(document).ready(function(){
                $('.product-thumbnails img').on('click', function(e){
                    e.preventDefault();
                    var newImageSrc = $(this).parent('a').attr('href'); // Assuming href has the large image
                    if(newImageSrc) { // Basic check
                         $('.main-product-image').attr('src', newImageSrc);
                    }
                });
            });
        </script>
    </div>

    <!-- Product Details -->
    <div class="col-md-6">
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        <p class="lead text-muted">SKU: PROD<?php echo htmlspecialchars($product['id']); ?></p>

        <h3>Price: <span class="text-success">$<?php echo htmlspecialchars($product['price']); ?></span></h3>

        <?php if ($product['stock'] > 0): ?>
            <p class="text-success">In Stock (<?php echo $product['stock']; ?> available)</p>
        <?php else: ?>
            <p class="text-danger">Out of Stock</p>
        <?php endif; ?>

        <div class="form-group">
            <label for="quantity">Quantity:</label>
            <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock'] > 0 ? $product['stock'] : 1; ?>" style="width: 80px;">
        </div>

        <button class="btn btn-primary btn-lg mr-2 add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
            <i class="fas fa-shopping-cart"></i> Add to Cart
        </button>
        <button class="btn btn-success btn-lg mr-2 buy-now-btn" data-product-id="<?php echo $product['id']; ?>" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
            Buy Now
        </button>
        <?php
        $is_liked_by_user = false;
        $total_likes = 0;
        if (isset($_SESSION['user_id']) && isset($product['id'])) {
            $sql_check_like = "SELECT id FROM user_product_likes WHERE user_id = " . (int)$_SESSION['user_id'] . " AND product_id = " . (int)$product['id'];
            $res_check_like = mysqli_query($conn, $sql_check_like);
            if ($res_check_like && mysqli_num_rows($res_check_like) > 0) {
                $is_liked_by_user = true;
            }
        }
        // Get total likes for the product
        if (isset($product['id'])) {
            $sql_total_likes = "SELECT COUNT(id) as count FROM user_product_likes WHERE product_id = " . (int)$product['id'];
            $res_total_likes = mysqli_query($conn, $sql_total_likes);
            if ($res_total_likes) {
                $total_likes_data = mysqli_fetch_assoc($res_total_likes);
                $total_likes = (int)$total_likes_data['count'];
            }
        }
        ?>
        <button class="btn btn-outline-danger btn-lg like-product-btn <?php echo $is_liked_by_user ? 'active' : ''; ?>"
                data-product-id="<?php echo $product['id']; ?>"
                data-action="<?php echo $is_liked_by_user ? 'unlike' : 'like'; ?>"
                title="<?php echo $is_liked_by_user ? 'Unlike Product' : 'Like Product'; ?>"
                <?php echo !isset($_SESSION['user_id']) ? 'disabled' : ''; ?>>
            <i class="fas fa-heart"></i> <span class="like-text"><?php echo $is_liked_by_user ? 'Liked' : 'Like'; ?></span>
            (<span class="like-count"><?php echo $total_likes; ?></span>)
        </button>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <small class="form-text text-muted d-block">Login to like products.</small>
        <?php endif; ?>

        <hr>

        <h4>Product Description</h4>
        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

        <?php if (!empty($product['how_it_works'])): ?>
        <h4>How It Works</h4>
        <p><?php echo nl2br(htmlspecialchars($product['how_it_works'])); ?></p>
        <?php endif; ?>

        <?php if (!empty($product['health_benefits_text'])): ?>
        <h4>Specific Health Concerns Addressed</h4>
        <ul class="list-styled">
            <?php $benefits = explode(',', $product['health_benefits_text']); ?>
            <?php foreach ($benefits as $benefit): ?>
                <li><?php echo htmlspecialchars(trim($benefit)); ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <?php if (!empty($product['gauss_strength'])): ?>
        <h4>Magnetic Strength (Gauss)</h4>
        <p><strong><?php echo htmlspecialchars($product['gauss_strength']); ?></strong></p>
        <?php endif; ?>

        <?php if (!empty($product['material_quality_design'])): ?>
        <h4>Material Quality & Design Details</h4>
        <p><?php echo nl2br(htmlspecialchars($product['material_quality_design'])); ?></p>
        <?php endif; ?>

        <?php if (!empty($product['usage_guide_text'])): ?>
        <h4>Usage Guides / Best Practices</h4>
        <p><?php echo nl2br(htmlspecialchars($product['usage_guide_text'])); ?></p>
        <?php endif; ?>

        <div class="mt-3">
             <p><strong>Category:</strong> <a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo htmlspecialchars($product['category_slug']); ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></p>
        </div>
         <div class="mt-3">
            <p class="text-muted"><small>Disclaimer: This product is not intended to diagnose, treat, cure, or prevent any disease. Consult with a healthcare professional for any health concerns.</small></p>
        </div>
    </div>
</div>

<!-- Customer Reviews/Testimonials Section -->
<div class="row mt-5">
    <div class="col-md-12">
        <h3>Customer Reviews</h3>
        <!-- Placeholder for reviews -->
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Jane D. - <small class="text-muted">Verified Buyer</small></h5>
                <p class="card-text">★★★★☆</p>
                <p class="card-text">This bracelet is fantastic! I've noticed a real difference in my joint comfort since wearing it.</p>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">John S. - <small class="text-muted">Verified Buyer</small></h5>
                <p class="card-text">★★★★★</p>
                <p class="card-text">High quality and looks great. Arrived quickly. Highly recommend!</p>
            </div>
        </div>
        <!-- Form for submitting review (placeholder, to be implemented with user auth) -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Leave a Review</h5>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php
                    // Check if user has purchased this product - Basic check, can be enhanced
                    $user_has_purchased = false;
                    if (isset($product['id'])) { // Ensure $product is set
                        $sql_check_purchase = "SELECT o.id FROM orders o
                                               JOIN order_items oi ON o.id = oi.order_id
                                               WHERE o.user_id = " . (int)$_SESSION['user_id'] . "
                                               AND oi.product_id = " . (int)$product['id'] . "
                                               AND o.status = 'delivered' LIMIT 1"; // Example: only after delivery
                        $res_check_purchase = mysqli_query($conn, $sql_check_purchase);
                        if ($res_check_purchase && mysqli_num_rows($res_check_purchase) > 0) {
                            $user_has_purchased = true;
                        }
                    }

                    // Check if user has already reviewed this product
                    $user_has_reviewed = false;
                     if (isset($product['id'])) { // Ensure $product is set
                        $sql_check_review = "SELECT id FROM reviews WHERE user_id = ".(int)$_SESSION['user_id']." AND product_id = ".(int)$product['id']." LIMIT 1";
                        $res_check_review = mysqli_query($conn, $sql_check_review);
                        if($res_check_review && mysqli_num_rows($res_check_review) > 0) {
                            $user_has_reviewed = true;
                        }
                    }

                    if ($user_has_reviewed): ?>
                        <div class="alert alert-info">You have already submitted a review for this product.</div>
                    <?php elseif ($user_has_purchased || (isset($product['id']) && $product['id'] == 101 ) /* temp allow review for specific product for demo */ ): // Loosen for demo if needed, or remove demo part ?>
                        <form action="submit_review.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                            <input type="hidden" name="product_slug" value="<?php echo htmlspecialchars($product['slug']); ?>"> <!-- For redirect -->
                            <div class="form-group">
                                <label for="rating">Rating</label>
                                <select class="form-control" id="rating" name="rating" required>
                                    <option value="">Select Rating</option>
                                    <option value="5">5 Stars - Excellent</option>
                                    <option value="4">4 Stars - Very Good</option>
                                    <option value="3">3 Stars - Good</option>
                                    <option value="2">2 Stars - Fair</option>
                                    <option value="1">1 Star - Poor</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="comment">Your Review</label>
                                <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Review</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            You must purchase this product (and have it delivered) to leave a review.
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p>Please <a href="<?php echo SITE_URL; ?>/login.php?redirect=<?php echo urlencode(SITE_URL . '/product/' . $product['slug']); ?>">login</a> to leave a review.</p>
                <?php endif; ?>

                <?php if(isset($_SESSION['review_message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['review_message_type'] ?? 'info'; ?> mt-3">
                        <?php echo htmlspecialchars($_SESSION['review_message']); ?>
                    </div>
                    <?php unset($_SESSION['review_message'], $_SESSION['review_message_type']); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Related Products Section -->
<div class="row mt-5">
    <div class="col-md-12">
        <h3>Related Products</h3>
        <div class="row">
            <!-- Placeholder for related products -->
            <div class="col-md-3">
                <div class="card">
                    <img src="https://via.placeholder.com/300x200.png?text=Related+1" class="card-img-top" alt="Related Product 1">
                    <div class="card-body">
                        <h5 class="card-title">Related Product 1</h5>
                        <p class="card-text">$39.99</p>
                        <a href="#" class="btn btn-sm btn-outline-primary">View</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <img src="https://via.placeholder.com/300x200.png?text=Related+2" class="card-img-top" alt="Related Product 2">
                    <div class="card-body">
                        <h5 class="card-title">Related Product 2</h5>
                        <p class="card-text">$45.00</p>
                        <a href="#" class="btn btn-sm btn-outline-primary">View</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
