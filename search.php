<?php
require_once __DIR__ . '/includes/db.php';

// Get search query
$query = $_GET['q'] ?? '';
$page_title = 'Search Results for "' . htmlspecialchars($query) . '"';
$products = [];

if (!empty($query)) {
    // In a real application, you would search the database for products matching the query
    // For now, some placeholder products if query is 'pain relief'
    if (strtolower($query) == 'pain relief') {
        $products = [
            ['id' => 10, 'name' => 'Magnetic Back Brace for Pain Relief', 'price' => '49.99', 'image_url_main' => 'https://via.placeholder.com/300x200.png?text=Back+Brace', 'slug' => 'magnetic-back-brace'],
            ['id' => 11, 'name' => 'Pain Relief Magnetic Insoles', 'price' => '24.50', 'image_url_main' => 'https://via.placeholder.com/300x200.png?text=Insoles', 'slug' => 'magnetic-insoles'],
        ];
    } else {
         $products = [
            ['id' => 12, 'name' => 'Generic Search Result 1', 'price' => '19.99', 'image_url_main' => 'https://via.placeholder.com/300x200.png?text=Search+1', 'slug' => 'search-result-1'],
            ['id' => 13, 'name' => 'Generic Search Result 2', 'price' => '29.99', 'image_url_main' => 'https://via.placeholder.com/300x200.png?text=Search+2', 'slug' => 'search-result-2'],
        ];
    }
}

require_once 'templates/header.php';
?>

<div class="row mb-3">
    <div class="col">
        <h1>Search Results</h1>
        <?php if (!empty($query)): ?>
            <p class="lead">Showing results for: <strong>"<?php echo htmlspecialchars($query); ?>"</strong></p>
        <?php else: ?>
            <p class="lead">Please enter a search term.</p>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                     <a href="<?php echo SITE_URL . '/product.php?slug=' . htmlspecialchars($product['slug']); ?>">
                        <img src="<?php echo htmlspecialchars($product['image_url_main']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </a>
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="<?php echo SITE_URL . '/product.php?slug=' . htmlspecialchars($product['slug']); ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                        </h5>
                        <p class="card-text"><strong>$<?php echo htmlspecialchars($product['price']); ?></strong></p>
                        <button class="btn btn-primary btn-block add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">Add to Cart</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php elseif (!empty($query)): ?>
        <div class="col">
            <p>No products found matching your search criteria.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Placeholder for a search form if not in header -->
<form action="<?php echo SITE_URL; ?>/search.php" method="GET" class="mt-4">
    <div class="input-group mb-3">
        <input type="text" class="form-control" name="q" placeholder="Search for products..." value="<?php echo htmlspecialchars($query); ?>">
        <div class="input-group-append">
            <button class="btn btn-outline-secondary" type="submit">Search</button>
        </div>
    </div>
</form>

<?php
require_once 'templates/footer.php';
?>
