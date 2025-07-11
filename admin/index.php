<?php
session_start();
require_once __DIR__ . '/../config.php'; // For SITE_URL, etc.
require_once __DIR__ . '/../includes/db.php'; // For db connection if needed directly

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    // If not admin or not logged in, redirect to main site login page or a specific admin login
    $_SESSION['admin_error'] = "You do not have permission to access the admin panel.";
    header("Location: " . SITE_URL . "/login.php"); // Or create admin/login.php and redirect there
    exit;
}

$page_title = "Admin Dashboard";
require_once 'includes/header.php'; // Admin specific header
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?php echo $page_title; ?></h1>
    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-download fa-sm text-white-50"></i> Generate Report</a>
</div>

<!-- Content Row - Example Cards -->
<div class="row">

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Sales (Monthly)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$0.00</div> <!-- Placeholder -->
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Products</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">0</div> <!-- Placeholder -->
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">New Orders (Today)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">0</div> <!-- Placeholder -->
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Reviews</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">0</div> <!-- Placeholder -->
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-comments fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<p>Welcome to the admin panel. Use the navigation on the left to manage your store.</p>
<!-- Further dashboard elements will go here -->

<?php
require_once 'includes/footer.php'; // Admin specific footer
?>
