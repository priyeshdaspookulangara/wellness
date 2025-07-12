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
    header("Location: " . SITE_URL . "login/"); // Updated
    exit;
}

// Determine active section for sidebar
$current_page_path = $_SERVER['PHP_SELF'];
$admin_base_url_path = parse_url(SITE_URL . 'admin/', PHP_URL_PATH);
$relative_path = str_replace($admin_base_url_path, '', $current_page_path);
$path_parts = explode('/', trim($relative_path, '/'));
$active_section = $path_parts[0] ?: 'dashboard';
if ($active_section == 'index.php' || $active_section == '') $active_section = 'dashboard';
$active_sub_section = $path_parts[1] ?? '';
if ($active_sub_section == 'index.php') $active_sub_section = '';


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Panel'; ?> - <?php echo SITE_NAME; ?></title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CDN (for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- CSS Custom Properties -->
    <style>
        :root {
            --primary-color: #0d6efd; /* Bootstrap Primary */
            --secondary-color: #6c757d; /* Bootstrap Secondary */
            --success-color: #198754; /* Bootstrap Success */
            --danger-color: #dc3545; /* Bootstrap Danger */
            --warning-color: #ffc107; /* Bootstrap Warning */
            --info-color: #0dcaf0; /* Bootstrap Info */
            --light-color: #f8f9fa;
            --dark-color: #212529; /* Bootstrap Dark */

            --text-color: var(--dark-color);
            --text-muted-color: var(--secondary-color);
            --link-color: var(--primary-color);
            --link-hover-color: #0a58ca; /* Darker primary */

            --body-bg: #f0f2f5; /* Light grey background */
            --header-bg: #ffffff;
            --header-text-color: var(--dark-color);
            --header-border-color: #dee2e6;

            --sidebar-width: 250px;
            --sidebar-collapsed-width: 0px; /* Fully collapse */
            --sidebar-bg: #343a40; /* Dark sidebar */
            --sidebar-text-color: rgba(255, 255, 255, 0.8);
            --sidebar-link-active-bg: var(--primary-color);
            --sidebar-link-active-color: #ffffff;
            --sidebar-link-hover-bg: #495057;
            --sidebar-link-hover-color: #ffffff;
            --sidebar-group-header-color: rgba(255, 255, 255, 0.9);
            --sidebar-border-color: #4b545c;

            --breadcrumb-bg: #e9ecef; /* Lighter than body for contrast */
            --breadcrumb-link-color: var(--primary-color);
            --breadcrumb-active-color: var(--secondary-color);
            --breadcrumb-padding: 0.75rem 1rem;

            --font-family-base: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            --font-size-base: 1rem; /* 16px */
            --font-size-sm: .875rem;
            --font-size-lg: 1.125rem;

            --main-content-padding: 1.5rem;
            --card-bg: #ffffff;
            --card-border-color: #dee2e6;
            --card-header-bg: #f8f9fa;
        }

        body {
            font-family: var(--font-family-base);
            font-size: var(--font-size-base);
            background-color: var(--body-bg);
            color: var(--text-color);
            display: flex; /* Use flex for layout */
            min-height: 100vh;
            overflow-x: hidden;
        }

        #admin-wrapper {
            display: flex;
            width: 100%;
        }

        #sidebar {
            width: var(--sidebar-width);
            min-width: var(--sidebar-width); /* Prevent shrinking below this */
            background: var(--sidebar-bg);
            color: var(--sidebar-text-color);
            transition: margin-left 0.3s ease-in-out, width 0.3s ease-in-out;
            position: fixed; /* Fixed position */
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100; /* Keep sidebar on top */
            overflow-y: auto; /* Scroll sidebar content if it overflows */
        }

        #sidebar.collapsed {
            margin-left: calc(-1 * var(--sidebar-width));
        }

        #sidebar .sidebar-header {
            padding: 1.25rem 1.5rem;
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid var(--sidebar-border-color);
            text-align: center;
        }
        #sidebar .sidebar-header h3 {
            color: #fff;
            font-size: 1.25rem;
            margin-bottom: 0;
        }

        #sidebar ul.components {
            padding: 0;
            list-style: none;
        }
        #sidebar ul li a {
            padding: 12px 20px;
            font-size: 0.95rem;
            display: block;
            color: var(--sidebar-text-color);
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: background 0.2s ease-in-out, border-left-color 0.2s ease-in-out;
        }
        #sidebar ul li a:hover {
            color: var(--sidebar-link-hover-color);
            background: var(--sidebar-link-hover-bg);
            border-left-color: var(--primary-color);
        }
        #sidebar ul li.active > a,
        #sidebar ul li a[aria-expanded="true"] { /* For active group headers */
            color: var(--sidebar-link-active-color);
            background: var(--sidebar-link-active-bg);
            border-left-color: var(--warning-color); /* Or a different highlight */
        }
        #sidebar ul li a .fas {
            margin-right: 10px;
            width: 20px; /* For icon alignment */
            text-align: center;
        }

        #sidebar ul ul a { /* Submenu items */
            font-size: 0.9rem;
            padding-left: 45px; /* Indent submenu */
            background: rgba(0,0,0,0.1);
        }
         #sidebar ul ul a:hover {
            background: rgba(0,0,0,0.2);
        }
        .dropdown-toggle::after { /* Bootstrap 5 accordion icon styling */
            margin-left: auto; /* Pushes icon to the right */
        }

        #content-wrapper {
            width: 100%;
            padding-left: var(--sidebar-width); /* Space for sidebar */
            transition: padding-left 0.3s ease-in-out;
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Full height */
        }
        #sidebar.collapsed + #content-wrapper {
            padding-left: 0;
        }

        .top-navbar {
            background-color: var(--header-bg);
            border-bottom: 1px solid var(--header-border-color);
            padding: 0.5rem 1rem;
            color: var(--header-text-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
            min-height: 56px; /* Standard navbar height */
            display: flex;
            align-items: center;
        }
        .top-navbar .navbar-brand-admin-sm { /* For small logo when sidebar is collapsed */
            display: none;
            color: var(--primary-color);
            font-weight: bold;
        }

        .breadcrumb-bar {
            padding: var(--breadcrumb-padding);
            background-color: var(--breadcrumb-bg);
            border-bottom: 1px solid var(--header-border-color);
        }
        .breadcrumb {
            margin-bottom: 0;
        }
        .breadcrumb-item a {
            color: var(--breadcrumb-link-color);
            text-decoration: none;
        }
        .breadcrumb-item.active {
            color: var(--breadcrumb-active-color);
        }

        .main-content {
            flex-grow: 1; /* Takes remaining vertical space */
            padding: var(--main-content-padding);
            overflow-y: auto;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) { /* Adjust breakpoint as needed, e.g., Bootstrap's lg */
            #sidebar {
                margin-left: calc(-1 * var(--sidebar-width)); /* Hide sidebar by default */
                z-index: 1035; /* Ensure it's above content but potentially below modals */
            }
            #sidebar.active { /* When toggled open on smaller screens */
                margin-left: 0;
            }
            #content-wrapper {
                padding-left: 0; /* Full width content when sidebar is hidden */
            }
            /* Optional: if sidebar overlays content on small screens when active */
            /* #sidebar.active + #content-wrapper { margin-left: 0; } */

            .top-navbar .navbar-brand-admin-sm {
                display: inline-block;
            }
        }
    </style>
</head>
<body>

<div id="admin-wrapper">
    <nav id="sidebar">
        <div class="sidebar-header">
            <h3><?php echo SITE_NAME; ?></h3>
        </div>

        <ul class="list-unstyled components">
            <li class="<?php echo ($active_section == 'dashboard') ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>admin/"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>

            <li>
                <a href="#storeSubmenu" data-bs-toggle="collapse" aria-expanded="<?php echo in_array($active_section, ['products', 'categories', 'orders']) ? 'true' : 'false'; ?>" class="dropdown-toggle <?php echo in_array($active_section, ['products', 'categories', 'orders']) ? '' : 'collapsed'; ?>">
                    <i class="fas fa-store"></i> Store
                </a>
                <ul class="collapse list-unstyled <?php echo in_array($active_section, ['products', 'categories', 'orders']) ? 'show' : ''; ?>" id="storeSubmenu" data-bs-parent="#sidebar">
                    <li class="<?php echo ($active_section == 'products' || $active_sub_section == 'product_add' || $active_sub_section == 'product_edit') ? 'active' : ''; ?>"><a href="<?php echo SITE_URL; ?>admin/products/">Products</a></li>
                    <li class="<?php echo ($active_section == 'categories' || $active_sub_section == 'category_add' || $active_sub_section == 'category_edit') ? 'active' : ''; ?>"><a href="<?php echo SITE_URL; ?>admin/categories/">Categories</a></li>
                    <li class="<?php echo ($active_section == 'orders' || $active_sub_section == 'order_view') ? 'active' : ''; ?>"><a href="<?php echo SITE_URL; ?>admin/orders/">Orders</a></li>
                </ul>
            </li>

            <li class="<?php echo ($active_section == 'users' || $active_sub_section == 'user_view') ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>admin/users/"><i class="fas fa-users"></i> Users</a>
            </li>

            <li>
                <a href="#contentSubmenu" data-bs-toggle="collapse" aria-expanded="<?php echo in_array($active_section, ['blog_posts', 'testimonials', 'content_edit']) ? 'true' : 'false'; ?>" class="dropdown-toggle <?php echo in_array($active_section, ['blog_posts', 'testimonials', 'content_edit']) ? '' : 'collapsed'; ?>">
                    <i class="fas fa-file-alt"></i> Content
                </a>
                <ul class="collapse list-unstyled <?php echo in_array($active_section, ['blog_posts', 'testimonials', 'content_edit']) ? 'show' : ''; ?>" id="contentSubmenu" data-bs-parent="#sidebar">
                    <li class="<?php echo ($active_section == 'blog_posts' || $active_sub_section == 'blog_add' || $active_sub_section == 'blog_edit') ? 'active' : ''; ?>"><a href="<?php echo SITE_URL; ?>admin/blog_posts/">Blog Posts</a></li>
                    <li class="<?php echo ($active_section == 'testimonials') ? 'active' : ''; ?>"><a href="<?php echo SITE_URL; ?>admin/testimonials/">Testimonials</a></li>
                    <li class="<?php echo ($active_section == 'content_edit' && ($_GET['page'] ?? '') == 'about') ? 'active' : ''; ?>"><a href="<?php echo SITE_URL; ?>admin/content_edit/?page=about">About Us Page</a></li>
                    <li class="<?php echo ($active_section == 'content_edit' && ($_GET['page'] ?? '') == 'testimonials_header') ? 'active' : ''; ?>"><a href="<?php echo SITE_URL; ?>admin/content_edit/?page=testimonials_header">Testimonials Header</a></li>
                    <!-- Add more static page links here if needed -->
                </ul>
            </li>

            <li>
                <a href="#reportsSubmenu" data-bs-toggle="collapse" aria-expanded="<?php echo in_array($active_section, ['reports_sales', 'reports_inventory', 'reports_customers']) ? 'true' : 'false'; ?>" class="dropdown-toggle <?php echo in_array($active_section, ['reports_sales', 'reports_inventory', 'reports_customers']) ? '' : 'collapsed'; ?>">
                    <i class="fas fa-chart-pie"></i> Reports
                </a>
                <ul class="collapse list-unstyled <?php echo in_array($active_section, ['reports_sales', 'reports_inventory', 'reports_customers']) ? 'show' : ''; ?>" id="reportsSubmenu" data-bs-parent="#sidebar">
                    <li class="<?php echo ($active_section == 'reports_sales') ? 'active' : ''; ?>"><a href="<?php echo SITE_URL; ?>admin/reports_sales/">Sales Reports</a></li>
                    <li class="<?php echo ($active_section == 'reports_inventory') ? 'active' : ''; ?>"><a href="<?php echo SITE_URL; ?>admin/reports_inventory/">Inventory Reports</a></li>
                    <li class="<?php echo ($active_section == 'reports_customers') ? 'active' : ''; ?>"><a href="<?php echo SITE_URL; ?>admin/reports_customers/">Customer Reports</a></li>
                </ul>
            </li>
            <!-- Placeholder for general settings if needed
            <li class="<?php echo ($active_section == 'settings') ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>admin/settings/"><i class="fas fa-cog"></i> Settings</a>
            </li>
            -->
            <hr style="border-color: var(--sidebar-border-color);">
            <li>
                <a href="<?php echo SITE_URL; ?>logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
             <li>
                <a href="<?php echo SITE_URL; ?>" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a>
            </li>
        </ul>
    </nav>

    <div id="content-wrapper">
        <nav class="top-navbar navbar navbar-expand-md navbar-light">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapseBtn" class="btn btn-outline-secondary me-3">
                    <i class="fas fa-bars"></i>
                </button>
                 <a class="navbar-brand-admin-sm" href="<?php echo SITE_URL; ?>admin/"><?php echo SITE_NAME; ?> Admin</a>

                <div class="collapse navbar-collapse">
                    <!-- Optional: Add any top-right navbar items here, like user profile dropdown -->
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <span class="navbar-text">
                                Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="breadcrumb-bar">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>admin/">Admin</a></li>
                    <?php
                    // Basic breadcrumb generation - can be made more sophisticated
                    if ($active_section != 'dashboard' && !empty($page_title)) {
                        // Attempt to create a link for the section
                        $section_link = SITE_URL . 'admin/' . $active_section . '/';
                        // Check if a file exists at that section's index.php to make it clickable
                        // This is a simplified check; a more robust system might use a routes array or sitemap.
                        $section_page_exists = file_exists(BASE_PATH . 'admin/' . $active_section . '/index.php');

                        if ($active_sub_section || (!$section_page_exists && $active_section != str_replace('.php', '', basename($current_page_path))) ) {
                            // If there's a sub-section or the active section isn't a direct page title match
                            if ($section_page_exists) {
                                echo '<li class="breadcrumb-item"><a href="' . $section_link . '">' . ucfirst(str_replace('_', ' ', $active_section)) . '</a></li>';
                            } else {
                                 echo '<li class="breadcrumb-item">' . ucfirst(str_replace('_', ' ', $active_section)) . '</li>';
                            }

                        }
                         echo '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars($page_title) . '</li>';
                    } elseif ($active_section == 'dashboard') {
                         echo '<li class="breadcrumb-item active" aria-current="page">Dashboard</li>';
                    }
                    ?>
                </ol>
            </nav>
        </div>

        <main class="main-content">
        <!-- Main page content starts here -->
