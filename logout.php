<?php
session_start();
require_once __DIR__ . '/config.php';

// Unset all of the session variables
$_SESSION = [];

// Destroy the session.
session_destroy();

// Redirect to login page
header("Location: " . SITE_URL . "login/");
exit;
?>
