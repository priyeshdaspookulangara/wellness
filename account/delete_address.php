<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION["user_id"])) {
    header("location: " . SITE_URL . "login/");
    exit;
}

$user_id = $_SESSION['user_id'];
$address_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($address_id > 0) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prevent deletion of the default address
    $stmt_check = $conn->prepare("SELECT `is_default` FROM addresses WHERE id = ? AND user_id = ?");
    $stmt_check->bind_param("ii", $address_id, $user_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    if ($result->num_rows === 1) {
        $address = $result->fetch_assoc();
        if ($address['is_default']) {
            $_SESSION['message'] = "You cannot delete your default address. Please set another address as default first.";
            $_SESSION['message_type'] = "danger";
        } else {
            $stmt_delete = $conn->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
            $stmt_delete->bind_param("ii", $address_id, $user_id);
            if ($stmt_delete->execute()) {
                $_SESSION['message'] = "Address deleted successfully.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error deleting address.";
                $_SESSION['message_type'] = "danger";
            }
            $stmt_delete->close();
        }
    }
    $stmt_check->close();
    $conn->close();
}

header("location: " . SITE_URL . "account/manage_addresses.php");
exit;
?>
