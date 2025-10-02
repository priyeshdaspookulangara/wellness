<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

// Get product slug from URL
$product_slug = $_GET['slug'] ?? '';
if (empty($product_slug)) {
    // Redirect to homepage or a 404 page if no slug is provided
    header("Location: " . SITE_URL);
    exit;
}

// Fetch product details from the database
$stmt = $conn->prepare("SELECT * FROM products WHERE slug = ?");
$stmt->bind_param("s", $product_slug);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    // If no product is found, show a 404 error or redirect
    http_response_code(404);
    echo "Product not found."; // Replace with a proper 404 page include
    exit;
}

$page_title = $product['name'];

// Fetch like count
$stmt_likes = $conn->prepare("SELECT COUNT(*) as like_count FROM product_likes WHERE product_id = ?");
$stmt_likes->bind_param("i", $product['id']);
$stmt_likes->execute();
$likes_result = $stmt_likes->get_result()->fetch_assoc();
$like_count = $likes_result['like_count'] ?? 0;

// Check if current user/session has liked this product
$user_has_liked = false;
$user_id = $_SESSION['user_id'] ?? null;
$session_id = session_id();

if ($user_id) {
    $stmt_user_like = $conn->prepare("SELECT id FROM product_likes WHERE user_id = ? AND product_id = ?");
    $stmt_user_like->bind_param("ii", $user_id, $product['id']);
    $stmt_user_like->execute();
    if ($stmt_user_like->get_result()->num_rows > 0) {
        $user_has_liked = true;
    }
} else {
    $stmt_session_like = $conn->prepare("SELECT id FROM product_likes WHERE session_id = ? AND product_id = ?");
    $stmt_session_like->bind_param("si", $session_id, $product['id']);
    $stmt_session_like->execute();
    if ($stmt_session_like->get_result()->num_rows > 0) {
        $user_has_liked = true;
    }
}

require_once __DIR__ . '/../templates/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <img src="<?php echo SITE_URL . 'uploads/' . htmlspecialchars($product['image_url_main']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="col-md-6">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="lead"><?php echo htmlspecialchars($product['description']); ?></p>
            <h3>$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></h3>

            <div class="d-flex justify-content-start align-items-center">
                 <button class="btn btn-primary add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                    <i class="fas fa-shopping-cart"></i> Add to Cart
                 </button>
                 <button class="btn btn-sm <?php echo $user_has_liked ? 'btn-danger' : 'btn-outline-danger'; ?> like-product-btn ml-2"
                        data-product-id="<?php echo $product['id']; ?>" data-action="like" title="<?php echo $user_has_liked ? 'Unlike' : 'Like'; ?> Product">
                    <i class="fas fa-heart"></i> (<span class="like-count"><?php echo $like_count; ?></span>)
                 </button>
            </div>

            <div class="mt-4">
                <h4>Product Details</h4>
                <p><strong>How it works:</strong> <?php echo htmlspecialchars($product['how_it_works']); ?></p>
                <p><strong>Health Benefits:</strong> <?php echo htmlspecialchars($product['health_benefits_text']); ?></p>
                <p><strong>Material & Design:</strong> <?php echo htmlspecialchars($product['material_quality_design']); ?></p>
                <p><strong>Usage Guide:</strong> <?php echo htmlspecialchars($product['usage_guide_text']); ?></p>
                <p><strong>Magnetic Strength:</strong> <?php echo htmlspecialchars($product['gauss_strength']); ?></p>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>