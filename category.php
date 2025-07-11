<?php
require_once __DIR__ . '/includes/db.php'; // For escape_string and potentially db connection if not included by header

// Get category slug from URL
$category_slug = $_GET['slug'] ?? null;
$category_name = "Category"; // Default
$products = [];

if ($category_slug) {
    // In a real scenario, you would fetch the category details and products from the database
    // For now, let's use a placeholder name based on the slug
    $category_name = ucwords(str_replace('-', ' ', escape_string($category_slug)));

    // Placeholder products
    $products = [
        ['id' => 1, 'name' => 'Product A in ' . $category_name, 'price' => '25.99', 'image_url_main' => 'https://via.placeholder.com/300x200.png?text=Product+A', 'slug' => 'product-a'],
        ['id' => 2, 'name' => 'Product B in ' . $category_name, 'price' => '35.50', 'image_url_main' => 'https://via.placeholder.com/300x200.png?text=Product+B', 'slug' => 'product-b'],
        ['id' => 3, 'name' => 'Product C in ' . $category_name, 'price' => '45.00', 'image_url_main' => 'https://via.placeholder.com/300x200.png?text=Product+C', 'slug' => 'product-c'],
    ];
} else {
    // Optional: Handle case where no slug is provided, e.g., redirect to a main categories page or show all products
    $category_name = "All Products";
     $products = [
        ['id' => 4, 'name' => 'General Product X', 'price' => '15.99', 'image_url_main' => 'https://via.placeholder.com/300x200.png?text=Product+X', 'slug' => 'product-x'],
        ['id' => 5, 'name' => 'General Product Y', 'price' => '22.50', 'image_url_main' => 'https://via.placeholder.com/300x200.png?text=Product+Y', 'slug' => 'product-y'],
    ];
}

$page_title = $category_name;
require_once 'templates/header.php';
?>

<div class="row mb-3">
    <div class="col-md-6">
        <h1><?php echo htmlspecialchars($category_name); ?></h1>
    </div>
    <div class="col-md-6 text-right">
        <!-- Filtering and Sorting options will go here -->
        <div class="btn-group">
            <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Sort By
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="#">Popularity</a>
                <a class="dropdown-item" href="#">Price: Low to High</a>
                <a class="dropdown-item" href="#">Price: High to Low</a>
                <a class="dropdown-item" href="#">Newness</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <h4>Filter By</h4>
        <!-- Placeholder for filters -->
        <div class="list-group">
            <a href="#" class="list-group-item list-group-item-action">Health Concern 1</a>
            <a href="#" class="list-group-item list-group-item-action">Health Concern 2</a>
            <a href="#" class="list-group-item list-group-item-action">Magnetic Strength X</a>
            <a href="#" class="list-group-item list-group-item-action">Price Range</a>
        </div>
    </div>
    <div class="col-md-9">
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
            <?php else: ?>
                <div class="col">
                    <p>No products found in this category.</p>
                </div>
            <?php endif; ?>
        </div>
        <!-- Pagination placeholder -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">Next</a></li>
            </ul>
        </nav>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
