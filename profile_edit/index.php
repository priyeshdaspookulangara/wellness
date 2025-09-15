<?php
session_start();
include_once '../config.php';

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "/login");
    exit();
}

$pageTitle = 'Edit Profile';
include_once '../templates/header.php';
?>

<h2>Edit Profile</h2>
<p>This page will allow you to edit your profile information.</p>
<p><em>This feature is not yet implemented.</em></p>

<?php include_once '../templates/footer.php'; ?>
