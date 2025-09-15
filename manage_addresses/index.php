<?php
session_start();
include_once '../config.php';

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "/login");
    exit();
}

$pageTitle = 'Manage Addresses';
include_once '../templates/header.php';
?>

<h2>Manage Addresses</h2>
<p>This page will allow you to manage your shipping and billing addresses.</p>
<p><em>This feature is not yet implemented.</em></p>

<?php include_once '../templates/footer.php'; ?>
