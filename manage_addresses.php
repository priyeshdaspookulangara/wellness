<?php
session_start();
require_once __DIR__ . '/includes/db.php';

$page_title = "Manage Addresses";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Placeholder for fetching and managing user addresses
$user_id = $_SESSION['user_id'];

require_once 'templates/header.php';
?>

<h1><?php echo $page_title; ?></h1>
<p>Manage your shipping and billing addresses.</p>

<!-- Placeholder for address display and forms -->
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Default Shipping Address</h5>
        <p>
            123 Wellness Lane<br>
            Healthville, ST 54321<br>
            USA
        </p>
        <button class="btn btn-secondary">Edit</button>
    </div>
</div>

<button class="btn btn-primary mt-3">Add New Address</button>

<?php
require_once 'templates/footer.php';
?>
