<?php
session_start();
require_once 'config.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_REQUEST['action'] ?? null;
$product_id = $_REQUEST['product_id'] ?? null;
$quantity = $_POST['quantity'] ?? 1;

if (!$product_id) {
    header('Location: ' . SITE_URL . 'cart/');
    exit;
}

switch ($action) {
    case 'add':
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        $_SESSION['message'] = 'Product added to cart.';
        $_SESSION['message_type'] = 'success';
        break;

    case 'update':
        if (isset($_SESSION['cart'][$product_id]) && $quantity > 0) {
            $_SESSION['cart'][$product_id] = $quantity;
            $_SESSION['message'] = 'Cart updated.';
            $_SESSION['message_type'] = 'success';
        }
        break;

    case 'remove':
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            $_SESSION['message'] = 'Product removed from cart.';
            $_SESSION['message_type'] = 'success';
        }
        break;
}

// Redirect back to the cart page or the previous page
$redirect_url = $_SERVER['HTTP_REFERER'] ?? SITE_URL . 'cart/';
header('Location: ' . $redirect_url);
exit;
?>