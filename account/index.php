<?php
// This file serves as a redirect to the new dashboard location.
require_once '../config.php';
header("Location: " . SITE_URL . "account/dashboard/");
exit;
?>
