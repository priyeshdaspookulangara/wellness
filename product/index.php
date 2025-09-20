<?php
session_start();
require_once '../config.php';
require_once '../includes/db.php';

// Function to parse YouTube URL and get embeddable URL
function get_youtube_embed_url($url) {
    $shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_-]+)/i';
    $longUrlRegex = '/youtube.com\/watch\?v=([a-zA-Z0-9_-]+)/i';

    if (preg_match($longUrlRegex, $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }
    if (preg_match($shortUrlRegex, $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }
    return null;
}


$product_slug = isset($_GET['slug']) ? $_GET['slug'] : '';
if (empty($product_slug)) {
    // Redirect to a 404 page or homepage
    header("Location: " . SITE_URL . "404.php");
    exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE slug = ?");
$stmt->bind_param("s", $product_slug);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: " . SITE_URL . "404.php");
    exit;
}
$product = $result->fetch_assoc();
$stmt->close();

// Fetch product media
$media_stmt = $conn->prepare("SELECT * FROM product_media WHERE product_id = ? ORDER BY media_type");
$media_stmt->bind_param("i", $product['id']);
$media_stmt->execute();
$media_result = $media_stmt->get_result();
$product_media = [];
$video_url = null;
while ($row = $media_result->fetch_assoc()) {
    if ($row['media_type'] == 'video') {
        $video_url = get_youtube_embed_url($row['path_or_url']);
    } else {
        $product_media[] = $row;
    }
}
$media_stmt->close();
$conn->close();

$pageTitle = $product['name'];
include_once '../templates/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-lg-6">
            <!-- Main Image -->
            <img src="<?php echo SITE_URL . 'uploads/' . htmlspecialchars($product['image_url_main']); ?>" class="img-fluid mb-3" alt="<?php echo htmlspecialchars($product['name']); ?>">

            <!-- Additional Images Gallery -->
            <div class="row">
                <?php foreach ($product_media as $media): ?>
                    <div class="col-4 mb-3">
                        <img src="<?php echo SITE_URL . 'uploads/' . htmlspecialchars($media['path_or_url']); ?>" class="img-fluid" alt="Additional product image">
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Video Player -->
            <?php if ($video_url): ?>
                <div class="embed-responsive embed-responsive-16by9 mt-4">
                    <iframe class="embed-responsive-item" src="<?php echo htmlspecialchars($video_url); ?>" allowfullscreen></iframe>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-6">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <h3 class="text-danger">$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></h3>
            <p class="lead"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

            <div class="mt-4">
                <h4>Product Details</h4>
                <ul>
                    <li><strong>Magnetic Strength:</strong> <?php echo htmlspecialchars($product['gauss_strength']); ?></li>
                    <li><strong>Materials:</strong> <?php echo htmlspecialchars($product['material_quality_design']); ?></li>
                </ul>
            </div>

            <div class="mt-4">
                <h4>How to Use</h4>
                <p><?php echo nl2br(htmlspecialchars($product['usage_guide_text'])); ?></p>
            </div>

            <div class="mt-4">
                <form action="<?php echo SITE_URL; ?>cart_actions.php" method="post">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="input-group mb-3" style="max-width: 200px;">
                        <input type="number" name="quantity" class="form-control" value="1" min="1">
                        <button class="btn btn-primary" type="submit" name="add_to_cart">Add to Cart</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include_once '../templates/footer.php';
?>
