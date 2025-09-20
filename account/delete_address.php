<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION["user_id"])) {
    header("location: " . SITE_URL . "/login");
    exit;
}

$user_id = $_SESSION['user_id'];
$address_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($address_id > 0) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // You can't delete a default address. User should change default first.
    // Or, we can add logic to auto-assign a new default, but for simplicity, we prevent it.
    $stmt = $conn->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ? AND is_default = 0");
    $stmt->bind_param("ii", $address_id, $user_id);

    if ($stmt->execute()) {
        // If no rows were affected, it could be because it was a default address
        if ($stmt->affected_rows == 0) {
            $_SESSION['message'] = "Cannot delete the default address. Please set another address as default first.";
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "Error deleting address.";
        $_SESSION['message_type'] = "danger";
    }

    $stmt->close();
    $conn->close();
}

header("location: " . SITE_URL . "/account/manage_addresses.php");
exit;
?>
