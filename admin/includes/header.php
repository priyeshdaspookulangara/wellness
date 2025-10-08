<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config.php'; // Path from admin/includes/ to root

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: " . SITE_URL . "login/");
    exit;
}

// Determine active page for sidebar
$current_page_path = $_SERVER['PHP_SELF'];
// Construct the base path of the admin directory from SITE_URL to correctly calculate the relative path
$admin_base_url_path = parse_url(SITE_URL, PHP_URL_PATH) . 'admin/';
$relative_path = str_replace($admin_base_url_path, '', $current_page_path);
$path_parts = explode('/', trim($relative_path, '/'));
$active_section = $path_parts[0] ?: 'dashboard';
if ($active_section == 'index.php') $active_section = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Panel'; ?> - <?php echo SITE_NAME; ?></title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CDN (for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Using an external stylesheet for admin styles now -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>admin/assets/css/admin-style.css">
</head>
<body>

<div id="admin-wrapper">
    <nav id="sidebar">
        <div class="sidebar-header">
            <h3><a href="<?php echo SITE_URL; ?>admin/" class="text-white text-decoration-none"><?php echo SITE_NAME; ?></a></h3>
        </div>

        <ul class="list-unstyled components accordion" id="sidebarAccordion">
            <li class="nav-item <?php echo ($active_section == 'dashboard') ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo SITE_URL; ?>admin/"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>

            <li class="nav-item">
                <a href="#storeSubmenu" data-bs-toggle="collapse" aria-expanded="<?php echo in_array($active_section, ['products', 'categories', 'orders']) ? 'true' : 'false'; ?>" class="nav-link dropdown-toggle <?php echo in_array($active_section, ['products', 'categories', 'orders']) ? '' : 'collapsed'; ?>">
                    <i class="fas fa-store"></i> Store
                </a>
                <ul class="collapse list-unstyled <?php echo in_array($active_section, ['products', 'categories', 'orders']) ? 'show' : ''; ?>" id="storeSubmenu" data-bs-parent="#sidebarAccordion">
                    <li><a class="nav-link <?php echo ($active_section == 'products') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/products/">Products</a></li>
                    <li><a class="nav-link <?php echo ($active_section == 'categories') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/categories/">Categories</a></li>
                    <li><a class="nav-link <?php echo ($active_section == 'orders') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/orders/">Orders</a></li>
                </ul>
            </li>

            <li class="nav-item <?php echo ($active_section == 'users') ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo SITE_URL; ?>admin/users/"><i class="fas fa-users"></i> Users</a>
            </li>

            <li class="nav-item <?php echo ($active_section == 'affiliates') ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo SITE_URL; ?>admin/affiliates/"><i class="fas fa-handshake"></i> Affiliates</a>
            </li>

            <li class="nav-item">
                <a href="#contentSubmenu" data-bs-toggle="collapse" aria-expanded="<?php echo in_array($active_section, ['blog_posts', 'testimonials', 'content_edit']) ? 'true' : 'false'; ?>" class="nav-link dropdown-toggle <?php echo in_array($active_section, ['blog_posts', 'testimonials', 'content_edit']) ? '' : 'collapsed'; ?>">
                    <i class="fas fa-file-alt"></i> Content
                </a>
                <ul class="collapse list-unstyled <?php echo in_array($active_section, ['blog_posts', 'testimonials', 'content_edit']) ? 'show' : ''; ?>" id="contentSubmenu" data-bs-parent="#sidebarAccordion">
                    <li><a class="nav-link <?php echo ($active_section == 'blog_posts') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/blog_posts/">Blog Posts</a></li>
                    <li><a class="nav-link <?php echo ($active_section == 'testimonials') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/testimonials/">Testimonials</a></li>
                    <li><a class="nav-link <?php echo ($active_section == 'content_edit' && ($_GET['page'] ?? '') == 'about') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/content_edit/?page=about">About Us Page</a></li>
                </ul>
            </li>

            <li class="nav-item">
                <a href="#reportsSubmenu" data-bs-toggle="collapse" aria-expanded="<?php echo in_array($active_section, ['reports_sales', 'reports_inventory', 'reports_customers']) ? 'true' : 'false'; ?>" class="nav-link dropdown-toggle <?php echo in_array($active_section, ['reports_sales', 'reports_inventory', 'reports_customers']) ? '' : 'collapsed'; ?>">
                    <i class="fas fa-chart-pie"></i> Reports
                </a>
                <ul class="collapse list-unstyled <?php echo in_array($active_section, ['reports_sales', 'reports_inventory', 'reports_customers']) ? 'show' : ''; ?>" id="reportsSubmenu" data-bs-parent="#sidebarAccordion">
                    <li><a class="nav-link <?php echo ($active_section == 'reports_sales') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/reports_sales/">Sales Reports</a></li>
                    <li><a class="nav-link <?php echo ($active_section == 'reports_inventory') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/reports_inventory/">Inventory Reports</a></li>
                    <li><a class="nav-link <?php echo ($active_section == 'reports_customers') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/reports_customers/">Customer Reports</a></li>
                </ul>
            </li>
            <hr class="sidebar-divider">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo SITE_URL; ?>logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
             <li class="nav-item">
                <a class="nav-link" href="<?php echo SITE_URL; ?>" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a>
            </li>
        </ul>
    </nav>

    <div id="content-wrapper">
        <nav class="top-navbar navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapseBtn" class="btn btn-outline-secondary me-3">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Breadcrumb Nav -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>admin/">Admin</a></li>
                        <?php if(isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                            <?php foreach($breadcrumbs as $i => $crumb): ?>
                                <?php if($i == count($breadcrumbs) - 1): // Last item is active ?>
                                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($crumb['name']); ?></li>
                                <?php else: ?>
                                    <li class="breadcrumb-item"><a href="<?php echo htmlspecialchars($crumb['link']); ?>"><?php echo htmlspecialchars($crumb['name']); ?></a></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php elseif(!empty($page_title) && $active_section != 'dashboard'): ?>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($page_title); ?></li>
                        <?php endif; ?>
                    </ol>
                </nav>

                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="navbar-text">
                            Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong>
                        </span>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="main-content">
        <!-- Main page content starts here -->
