<?php
// This check should ideally be at the top of every admin page
// For simplicity in includes, we assume it's done in the calling script (e.g., admin/index.php)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php'; // Access to SITE_URL etc.

// Double check admin status for include files as well, though primary check is in main page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    // Redirect to a login page if not admin.
    // This path might need adjustment if admin has its own login.
    header("Location: " . SITE_URL . "/login.php?auth_required=admin");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Panel'; ?> - <?php echo SITE_NAME; ?></title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CDN (for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Custom Admin CSS (optional) -->
    <link href="<?php echo SITE_URL; ?>/admin/assets/css/admin-style.css" rel="stylesheet">
    <style>
        body {
            display: flex;
        }
        #sidebar {
            min-width: 250px;
            max-width: 250px;
            background: #343a40; /* Dark sidebar */
            color: #fff;
            transition: all 0.3s;
            min-height: 100vh;
        }
        #sidebar.active {
            margin-left: -250px;
        }
        #sidebar .sidebar-header {
            padding: 20px;
            background: #2c3136;
        }
        #sidebar ul.components {
            padding: 20px 0;
            border-bottom: 1px solid #47748b;
        }
        #sidebar ul p {
            color: #fff;
            padding: 10px;
        }
        #sidebar ul li a {
            padding: 10px;
            font-size: 1.1em;
            display: block;
            color: #adb5bd;
            text-decoration: none;
        }
        #sidebar ul li a:hover {
            color: #fff;
            background: #495057;
        }
        #sidebar ul li.active > a, a[aria-expanded="true"] {
            color: #fff;
            background: #007bff; /* Active link color */
        }
        #content {
            width: 100%;
            padding: 20px;
            min-height: 100vh;
            transition: all 0.3s;
        }
    </style>
</head>
<body>

<nav id="sidebar">
    <div class="sidebar-header">
        <h3><?php echo SITE_NAME; ?> Admin</h3>
    </div>

    <ul class="list-unstyled components">
        <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
            <a href="<?php echo SITE_URL; ?>/admin/index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        </li>
        <li class="<?php echo ($current_page == 'products.php' || $current_page == 'product_add.php' || $current_page == 'product_edit.php') ? 'active' : ''; ?>">
            <a href="#productSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fas fa-box-open"></i> Products</a>
            <ul class="collapse list-unstyled <?php echo ($current_page == 'products.php' || $current_page == 'product_add.php' || $current_page == 'product_edit.php') ? 'show' : ''; ?>" id="productSubmenu">
                <li><a href="<?php echo SITE_URL; ?>/admin/products.php">All Products</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/product_add.php">Add Product</a></li>
            </ul>
        </li>
        <li class="<?php echo ($current_page == 'categories.php' || $current_page == 'category_add.php' || $current_page == 'category_edit.php') ? 'active' : ''; ?>">
            <a href="#categorySubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fas fa-list-alt"></i> Categories</a>
            <ul class="collapse list-unstyled <?php echo ($current_page == 'categories.php' || $current_page == 'category_add.php' || $current_page == 'category_edit.php') ? 'show' : ''; ?>" id="categorySubmenu">
                <li><a href="<?php echo SITE_URL; ?>/admin/categories.php">All Categories</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/category_add.php">Add Category</a></li>
            </ul>
        </li>
        <li class="<?php echo ($current_page == 'orders.php') ? 'active' : ''; ?>">
            <a href="<?php echo SITE_URL; ?>/admin/orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
        </li>
        <li class="<?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
            <a href="<?php echo SITE_URL; ?>/admin/users.php"><i class="fas fa-users"></i> Users</a>
        </li>
        <li class="<?php echo ($current_page == 'testimonials.php') ? 'active' : ''; ?>">
            <a href="<?php echo SITE_URL; ?>/admin/testimonials.php"><i class="fas fa-comment-dots"></i> Testimonials</a>
        </li>
        <li class="<?php echo (strpos($current_page, 'blog_') !== false) ? 'active' : ''; ?>">
            <a href="#blogSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fas fa-newspaper"></i> Blog/Articles</a>
            <ul class="collapse list-unstyled <?php echo (strpos($current_page, 'blog_') !== false) ? 'show' : ''; ?>" id="blogSubmenu">
                <li><a href="<?php echo SITE_URL; ?>/admin/blog_posts.php">All Posts</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/blog_add.php">Add New Post</a></li>
            </ul>
        </li>
        <li class="<?php echo (strpos($current_page, 'content_') !== false || $current_page == 'settings.php') ? 'active' : ''; ?>">
            <a href="#contentMgmtSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fas fa-file-alt"></i> Content Mgmt</a>
            <ul class="collapse list-unstyled <?php echo (strpos($current_page, 'content_') !== false) ? 'show' : ''; ?>" id="contentMgmtSubmenu">
                <li><a href="<?php echo SITE_URL; ?>/admin/content_edit.php?page=about">About Us Page</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/settings.php">Site Settings</a></li>
                <!-- Add more static pages if needed -->
            </ul>
        </li>
         <li class="<?php echo (strpos($current_page, 'report_') !== false) ? 'active' : ''; ?>">
            <a href="#reportsSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fas fa-chart-line"></i> Reports</a>
            <ul class="collapse list-unstyled <?php echo (strpos($current_page, 'report_') !== false) ? 'show' : ''; ?>" id="reportsSubmenu">
                <li><a href="<?php echo SITE_URL; ?>/admin/reports_sales.php">Sales Reports</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/reports_inventory.php">Inventory Reports</a></li>
                <li><a href="<?php echo SITE_URL; ?>/admin/reports_customers.php">Customer Reports</a></li>
            </ul>
        </li>
        <li>
            <a href="<?php echo SITE_URL; ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </li>
         <li>
            <a href="<?php echo SITE_URL; ?>" target="_blank"><i class="fas fa-external-link-alt"></i> View Frontend</a>
        </li>
    </ul>
</nav>

<div id="content">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <button type="button" id="sidebarCollapse" class="btn btn-info">
                <i class="fas fa-align-left"></i>
                <span>Toggle Sidebar</span>
            </button>
            <!-- Optional Navbar items on the right -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <span class="navbar-text">Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong></span>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container-fluid pt-3">
    <!-- Main page content starts here -->
