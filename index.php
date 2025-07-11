<?php
$page_title = "Homepage";
require_once 'templates/header.php';
?>

<div class="jumbotron">
    <h1 class="display-4">Welcome to <?php echo SITE_NAME; ?>!</h1>
    <p class="lead">Your one-stop shop for premium wellness products designed to enhance your well-being.</p>
    <hr class="my-4">
    <p>Explore our range of magnetic therapy items, natural supplements, and more.</p>
    <a class="btn btn-primary btn-lg" href="#" role="button">Shop Now</a>
</div>

<section id="new-arrivals" class="my-5">
    <h2>New Arrivals</h2>
    <div class="row">
        <!-- Placeholder for new arrival products -->
        <div class="col-md-4">
            <div class="card mb-4">
                <a href="<?php echo SITE_URL; ?>/product.php?slug=new-product-1-slug"> <!-- Example Link -->
                    <img src="https://via.placeholder.com/300x200.png?text=Product+Image" class="card-img-top" alt="New Product 1">
                </a>
                <div class="card-body">
                    <h5 class="card-title"><a href="<?php echo SITE_URL; ?>/product.php?slug=new-product-1-slug">New Product 1</a></h5>
                    <p class="card-text">Brief description of the new product.</p>
                    <p class="card-text"><strong>$19.99</strong></p>
                    <div class="d-flex justify-content-between align-items-center">
                         <button class="btn btn-primary add-to-cart-btn" data-product-id="1"> <!-- Placeholder ID -->
                            <i class="fas fa-shopping-cart"></i> Cart
                         </button>
                         <button class="btn btn-sm btn-outline-danger like-product-btn"
                                data-product-id="1" data-action="like" title="Like Product"
                                <?php echo !isset($_SESSION['user_id']) ? 'disabled' : ''; ?>>
                            <i class="fas fa-heart"></i> (<span class="like-count">0</span>) <!-- Placeholder count -->
                         </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <a href="<?php echo SITE_URL; ?>/product.php?slug=new-product-2-slug"> <!-- Example Link -->
                    <img src="https://via.placeholder.com/300x200.png?text=Product+Image" class="card-img-top" alt="New Product 2">
                </a>
                <div class="card-body">
                    <h5 class="card-title"><a href="<?php echo SITE_URL; ?>/product.php?slug=new-product-2-slug">New Product 2</a></h5>
                    <p class="card-text">Brief description of the new product.</p>
                    <p class="card-text"><strong>$29.99</strong></p>
                     <div class="d-flex justify-content-between align-items-center">
                         <button class="btn btn-primary add-to-cart-btn" data-product-id="2"> <!-- Placeholder ID -->
                            <i class="fas fa-shopping-cart"></i> Cart
                         </button>
                         <button class="btn btn-sm btn-outline-danger like-product-btn"
                                data-product-id="2" data-action="like" title="Like Product"
                                <?php echo !isset($_SESSION['user_id']) ? 'disabled' : ''; ?>>
                            <i class="fas fa-heart"></i> (<span class="like-count">0</span>)
                         </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                 <a href="<?php echo SITE_URL; ?>/product.php?slug=new-product-3-slug"> <!-- Example Link -->
                    <img src="https://via.placeholder.com/300x200.png?text=Product+Image" class="card-img-top" alt="New Product 3">
                </a>
                <div class="card-body">
                    <h5 class="card-title"><a href="<?php echo SITE_URL; ?>/product.php?slug=new-product-3-slug">New Product 3</a></h5>
                    <p class="card-text">Brief description of the new product.</p>
                    <p class="card-text"><strong>$39.99</strong></p>
                     <div class="d-flex justify-content-between align-items-center">
                         <button class="btn btn-primary add-to-cart-btn" data-product-id="3"> <!-- Placeholder ID -->
                            <i class="fas fa-shopping-cart"></i> Cart
                         </button>
                         <button class="btn btn-sm btn-outline-danger like-product-btn"
                                data-product-id="3" data-action="like" title="Like Product"
                                <?php echo !isset($_SESSION['user_id']) ? 'disabled' : ''; ?>>
                            <i class="fas fa-heart"></i> (<span class="like-count">0</span>)
                         </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="bestsellers" class="my-5 bg-light py-5">
    <div class="container">
        <h2>Bestsellers</h2>
        <div class="row">
            <!-- Placeholder for bestseller products -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <img src="https://via.placeholder.com/300x200.png?text=Bestseller+1" class="card-img-top" alt="Bestseller Product 1">
                    <div class="card-body">
                        <h5 class="card-title">Bestseller 1</h5>
                        <p class="card-text">Description of bestseller product.</p>
                        <p class="card-text"><strong>$49.99</strong></p>
                        <a href="#" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-4">
                    <img src="https://via.placeholder.com/300x200.png?text=Bestseller+2" class="card-img-top" alt="Bestseller Product 2">
                    <div class="card-body">
                        <h5 class="card-title">Bestseller 2</h5>
                        <p class="card-text">Description of bestseller product.</p>
                        <p class="card-text"><strong>$59.99</strong></p>
                        <a href="#" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="promotions" class="my-5">
    <h2>Promotions</h2>
    <div class="row">
        <!-- Placeholder for promotions -->
        <div class="col-md-6">
            <div class="alert alert-info" role="alert">
                <strong>Special Offer!</strong> Get 10% off on all magnetic bracelets this week. Use code: WELLNESS10
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-success" role="alert">
                <strong>Free Shipping!</strong> On orders over $100.
            </div>
        </div>
    </div>
</section>

<section id="mission" class="my-5 text-center">
    <h2>Our Mission</h2>
    <p class="lead">We are committed to providing high-quality wellness products that support your journey to a healthier and more balanced life. Our focus is on trust, education, and the tangible benefits our products offer.</p>
</section>

<?php
require_once 'templates/footer.php';
?>
