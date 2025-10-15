<?php
session_start();
require_once '../config.php';
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['cart'])) {
    header('Location: ' . SITE_URL);
    exit();
}

$pdo = db_connect();

// Customer and shipping details from form
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
$city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
$state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING);
$zip = filter_input(INPUT_POST, 'zip', FILTER_SANITIZE_STRING);
$country = filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING);
$payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);

// Calculate total price from cart
$total_price = 0;
$product_ids = implode(',', array_keys($_SESSION['cart']));
$stmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($product_ids)");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

foreach ($_SESSION['cart'] as $product_id => $quantity) {
    if (isset($products[$product_id])) {
        $total_price += $products[$product_id] * $quantity;
    }
}

// Insert into orders table
$order_status = 'pending';
$stmt = $pdo->prepare(
    "INSERT INTO orders (user_id, total_price, status, shipping_name, shipping_address, shipping_city, shipping_state, shipping_zip, shipping_country, payment_method)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->execute([$user_id, $total_price, $order_status, $name, $address, $city, $state, $zip, $country, $payment_method]);
$order_id = $pdo->lastInsertId();

// Insert into order_items table
$stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
foreach ($_SESSION['cart'] as $product_id => $quantity) {
    if (isset($products[$product_id])) {
        $stmt->execute([$order_id, $product_id, $quantity, $products[$product_id]]);
    }
}

// Clear the cart
unset($_SESSION['cart']);

// Redirect to confirmation page
$_SESSION['order_id'] = $order_id;
header('Location: ' . SITE_URL . 'checkout/confirmation.php');
exit();