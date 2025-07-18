<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

// Fetch all categories for display
try {
    $stmt = $pdo->query("SELECT name, slug, description FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Failed to fetch categories: " . $e->getMessage());
    $categories = [];
}

$pageTitle = 'Product Categories';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1>Product Categories</h1>
        <p>Browse our products by category to find exactly what you need.</p>
    </div>
</div>

<div class="row mt-4">
    <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $category): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="<?php echo SITE_URL; ?>/category/?slug=<?php echo htmlspecialchars($category['slug']); ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </h5>
                        <p class="card-text"><?php echo htmlspecialchars($category['description']); ?></p>
                        <a href="<?php echo SITE_URL; ?>/category/?slug=<?php echo htmlspecialchars($category['slug']); ?>" class="btn btn-primary">View Products</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info">
                No product categories are available at the moment. Please check back soon.
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
