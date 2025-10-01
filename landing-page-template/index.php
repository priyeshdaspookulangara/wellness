<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

// This is a placeholder for the product-specific logic.
// When a landing page is created, a product ID will need to be associated with it.
// For now, we'll assume a static product ID for template purposes.
$product_id = 1; // Example product ID

// Fetch product details from the database
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    // Handle product not found, maybe redirect to a 404 page
    echo "Product not found.";
    exit;
}

$page_title = $product['name'] . " - Landing Page";
// Assuming a simple header and footer structure
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Adjust path as needed -->
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        <p><?php echo htmlspecialchars($product['description']); ?></p>
        <div>
            <img src="<?php echo SITE_URL . 'uploads/' . htmlspecialchars($product['image_url_main']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="max-width: 500px;">
        </div>
        <h2>$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></h2>
        <button>Buy Now</button>
    </div>
</body>
</html>