<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

$slug = $_GET['slug'] ?? 'all';

try {
    if ($slug === 'all') {
        $pageTitle = 'All Products';
        $stmt = $pdo->prepare("SELECT id, name, slug, description, price, image_url FROM products WHERE is_active = 1 ORDER BY created_at DESC");
        $stmt->execute();
    } else {
        // First, get category details
        $cat_stmt = $pdo->prepare("SELECT id, name FROM categories WHERE slug = ?");
        $cat_stmt->execute([$slug]);
        $category = $cat_stmt->fetch(PDO::FETCH_ASSOC);

        if ($category) {
            $pageTitle = 'Products in ' . htmlspecialchars($category['name']);
            $stmt = $pdo->prepare("
                SELECT p.id, p.name, p.slug, p.description, p.price, p.image_url
                FROM products p
                JOIN product_categories pc ON p.id = pc.product_id
                WHERE pc.category_id = ? AND p.is_active = 1
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$category['id']]);
        } else {
            // Category not found, redirect or show error
            header("Location: " . SITE_URL . "/404.php");
            exit;
        }
    }
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Product listing error: " . $e->getMessage());
    $products = [];
    // You might want to set an error message for the user here
}

require_once __DIR__ . '/../templates/header.php';
?>

<h1><?php echo $pageTitle; ?></h1>
<hr>

<div class="row">
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <a href="<?php echo SITE_URL; ?>/product/?slug=<?php echo htmlspecialchars($product['slug']); ?>">
                        <img src="<?php echo htmlspecialchars($product['image_url'] ?: 'https://via.placeholder.com/300x200.png?text=Product+Image'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </a>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <a href="<?php echo SITE_URL; ?>/product/?slug=<?php echo htmlspecialchars($product['slug']); ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </h5>
                        <p class="card-text flex-grow-1"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                        <p class="card-text"><strong>$<?php echo number_format($product['price'], 2); ?></strong></p>
                        <div class="mt-auto">
                           <button class="btn btn-primary add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                               <i class="fas fa-shopping-cart"></i> Add to Cart
                           </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info" role="alert">
                There are no products in this category at the moment.
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
