<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php'; // For product stock check

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? $_GET['action'] ?? null;
$product_id = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? $_GET['quantity'] ?? 1);

$response = ['success' => false, 'message' => 'Invalid action.', 'cart_count' => count($_SESSION['cart'])];

if (!$product_id || $product_id <= 0) {
    $_SESSION['cart_message'] = 'Invalid product specified.';
    $_SESSION['cart_message_type'] = 'danger';
    if (isset($_POST['is_ajax'])) {
        $response['message'] = 'Invalid product specified.';
        echo json_encode($response);
        exit;
    }
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? SITE_URL . 'cart/')); // Updated default redirect
    exit;
}

// Fetch product stock for validation
$product_stock = 0;
$sql_stock = "SELECT stock FROM products WHERE id = $product_id";
$res_stock = mysqli_query($conn, $sql_stock);
if ($res_stock && mysqli_num_rows($res_stock) > 0) {
    $product_stock_data = mysqli_fetch_assoc($res_stock);
    $product_stock = (int)$product_stock_data['stock'];
} else {
    $_SESSION['cart_message'] = 'Product not found.';
    $_SESSION['cart_message_type'] = 'danger';
     if (isset($_POST['is_ajax'])) {
        $response['message'] = 'Product not found.';
        echo json_encode($response);
        exit;
    }
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? SITE_URL . 'cart/')); // Updated default redirect
    exit;
}


switch ($action) {
    case 'add':
        if ($quantity <= 0) {
            $message = "Quantity must be at least 1.";
            $message_type = 'danger';
        } elseif ($quantity > $product_stock) {
            $message = "Cannot add $quantity items. Only $product_stock available in stock.";
            $message_type = 'warning';
            $quantity = $product_stock; // Adjust to max available if user tries to add more
        }

        if ($quantity > 0) { // Proceed if adjusted quantity is valid
            if (isset($_SESSION['cart'][$product_id])) {
                 $new_quantity = $_SESSION['cart'][$product_id]['quantity'] + $quantity;
                 if ($new_quantity > $product_stock) {
                    $message = "Cannot add more. Total quantity would exceed stock ($product_stock). Your cart has not been updated for this item.";
                    $message_type = 'warning';
                 } else {
                    $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
                    $message = "Product quantity updated in cart.";
                    $message_type = 'success';
                    $response['success'] = true;
                 }
            } else {
                $_SESSION['cart'][$product_id] = ['quantity' => $quantity];
                $message = "Product added to cart.";
                $message_type = 'success';
                $response['success'] = true;
            }
        } else if (!isset($message)) { // if quantity was adjusted to 0 due to stock
             $message = "This product is currently out of stock and cannot be added to cart.";
             $message_type = 'warning';
        }

        $_SESSION['cart_message'] = $message;
        $_SESSION['cart_message_type'] = $message_type;
        $response['message'] = $message;
        break;

    case 'update':
        if ($quantity <= 0) {
            // If quantity is 0 or less, remove the item
            unset($_SESSION['cart'][$product_id]);
            $message = "Product removed from cart.";
            $message_type = 'info';
        } elseif ($quantity > $product_stock) {
            $_SESSION['cart'][$product_id]['quantity'] = $product_stock; // Set to max available
            $message = "Quantity adjusted to available stock ($product_stock).";
            $message_type = 'warning';
        } else {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            $message = "Cart updated.";
            $message_type = 'success';
        }
        $_SESSION['cart_message'] = $message;
        $_SESSION['cart_message_type'] = $message_type;
        $response['success'] = true;
        $response['message'] = $message;
        break;

    case 'remove':
        unset($_SESSION['cart'][$product_id]);
        $_SESSION['cart_message'] = "Product removed from cart.";
        $_SESSION['cart_message_type'] = 'info';
        $response['success'] = true;
        $response['message'] = "Product removed from cart.";
        break;

    default:
        $_SESSION['cart_message'] = 'Invalid cart action.';
        $_SESSION['cart_message_type'] = 'danger';
        $response['message'] = 'Invalid cart action.';
}

// Calculate total items in cart for AJAX response
$total_cart_items = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_cart_items += $item['quantity'];
}
$response['cart_count'] = $total_cart_items;


if (isset($_POST['is_ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    // Redirect back to the cart page or the page the user came from
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? SITE_URL . 'cart/')); // Updated default redirect
    exit;
}
?>
