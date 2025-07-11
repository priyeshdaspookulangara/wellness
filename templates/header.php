<?php require_once __DIR__ . '/../config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo $page_title ?? 'Your Journey to Wellness'; ?></title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS (optional) -->
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <!-- Font Awesome for icons (used in cart buttons etc) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script>
        var SITE_URL = "<?php echo SITE_URL; ?>"; // Make SITE_URL available to JavaScript
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="<?php echo SITE_URL; ?>"><?php echo SITE_NAME; ?></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item <?php echo ($page_title ?? '') == 'Homepage' ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>">Home</a> <!-- index.php is at root -->
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownCategories" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Categories
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownCategories">
                        <!-- Categories will be populated dynamically later -->
                        <a class="dropdown-item" href="<?php echo SITE_URL; ?>category/?slug=magnetic-bracelets">Magnetic Bracelets</a>
                        <a class="dropdown-item" href="<?php echo SITE_URL; ?>category/?slug=wellness-supplements">Wellness Supplements</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?php echo SITE_URL; ?>categories/">All Categories</a> <!-- Assuming categories/index.php will be created -->
                    </div>
                </li>
                <li class="nav-item <?php echo ($page_title ?? '') == 'About Us' ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>about/">About Us</a>
                </li>
                 <li class="nav-item <?php echo ($page_title ?? '') == 'Testimonials' ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>testimonials/">Testimonials</a>
                </li>
                <li class="nav-item <?php echo ($page_title ?? '') == 'Blog' ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>blog/">Blog</a>
                </li>
                <li class="nav-item <?php echo ($page_title ?? '') == 'Contact Us' ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>contact/">Contact</a>
                </li>
                <!-- User Account & Cart - to be enhanced with session logic -->
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>login/">Login</a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>register/">Register</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>cart/">
                        <i class="fas fa-shopping-cart"></i> Cart
                        <span class="badge badge-pill badge-primary" id="cart-count-badge">
                            <?php
                            // Display current cart count from session
                            $total_cart_items_header = 0;
                            if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                                foreach ($_SESSION['cart'] as $item_id => $item_data) {
                                    $total_cart_items_header += $item_data['quantity'] ?? 0;
                                }
                            }
                            echo $total_cart_items_header;
                            ?>
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Main content will go here -->
