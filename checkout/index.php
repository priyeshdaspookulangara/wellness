<?php
session_start();
require_once __DIR__ . '/../config.php'; // Adjusted path
require_once __DIR__ . '/../includes/db.php'; // Adjusted path

$page_title = "Checkout";

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    $_SESSION['cart_message'] = "Your cart is empty. Please add products before proceeding to checkout.";
    $_SESSION['cart_message_type'] = "warning";
    header("Location: " . SITE_URL . "cart/"); // Updated redirect
    exit;
}

// Initialize user data and form fields
$user_id = $_SESSION['user_id'] ?? null;
$checkout_data = [
    'email' => '', 'first_name' => '', 'last_name' => '',
    'address_street' => '', 'address_city' => '', 'address_state' => '',
    'address_zip' => '', 'address_country' => 'USA', // Default country
    'phone_number' => '', 'notes' => '',
    'shipping_method' => 'flat_rate', // Hardcoded for now
    'payment_method' => 'placeholder_cod' // Placeholder
];
$errors = [];

// If user is logged in, pre-fill their details
if ($user_id) {
    $sql_user = "SELECT email, first_name, last_name, address_street, address_city, address_state, address_zip, address_country, phone_number FROM users WHERE id = " . (int)$user_id;
    $res_user = mysqli_query($conn, $sql_user);
    if ($res_user && mysqli_num_rows($res_user) > 0) {
        $user_db_data = mysqli_fetch_assoc($res_user);
        foreach ($user_db_data as $key => $value) {
            if (!empty($value)) { // Only fill if DB value is not empty
                $checkout_data[$key] = $value;
            }
        }
    }
}


// Calculate cart totals again
$cart_items = $_SESSION['cart'];
$subtotal = 0;
$total_items_in_cart = 0;
$product_details_for_order = [];

if (!empty($cart_items)) {
    $product_ids = array_keys($cart_items);
    $ids_string = implode(',', array_map('intval', $product_ids));
    if(!empty($ids_string)) {
        $sql_cart_prods = "SELECT id, name, price, stock FROM products WHERE id IN ($ids_string)";
        $res_cart_prods = mysqli_query($conn, $sql_cart_prods);
        while ($prod = mysqli_fetch_assoc($res_cart_prods)) {
            $quantity = $cart_items[$prod['id']]['quantity'];
             if ($quantity > $prod['stock']) {
                $errors[] = "Not enough stock for " . htmlspecialchars($prod['name']) . ". Available: " . $prod['stock'] . ", Requested: " . $quantity . ". Please update your cart.";
            }
            $subtotal += $prod['price'] * $quantity;
            $total_items_in_cart += $quantity;
            $product_details_for_order[$prod['id']] = [
                'name' => $prod['name'],
                'price_at_purchase' => $prod['price'],
                'quantity' => $quantity
            ];
        }
    }
}

if ($total_items_in_cart == 0 && empty($errors)) {
    $_SESSION['cart_message'] = "Your cart is effectively empty or items are out of stock.";
    $_SESSION['cart_message_type'] = "warning";
    header("Location: " . SITE_URL . "cart/"); // Updated redirect
    exit;
}


$shipping_cost = 5.00;
$grand_total = $subtotal + $shipping_cost;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    // Sanitize and validate input
    $checkout_data['email'] = trim($_POST['email'] ?? '');
    $checkout_data['first_name'] = trim($_POST['first_name'] ?? '');
    // ... (rest of sanitization as before) ...
    $checkout_data['last_name'] = trim($_POST['last_name'] ?? '');
    $checkout_data['address_street'] = trim($_POST['address_street'] ?? '');
    $checkout_data['address_city'] = trim($_POST['address_city'] ?? '');
    $checkout_data['address_state'] = trim($_POST['address_state'] ?? '');
    $checkout_data['address_zip'] = trim($_POST['address_zip'] ?? '');
    $checkout_data['address_country'] = trim($_POST['address_country'] ?? '');
    $checkout_data['phone_number'] = trim($_POST['phone_number'] ?? '');
    $checkout_data['notes'] = trim($_POST['notes'] ?? '');
    $checkout_data['payment_method'] = trim($_POST['payment_method'] ?? 'placeholder_cod');


    if (!filter_var($checkout_data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($checkout_data['first_name'])) $errors[] = "First name is required.";
    // ... (rest of validation as before) ...
    if (empty($checkout_data['last_name'])) $errors[] = "Last name is required.";
    if (empty($checkout_data['address_street'])) $errors[] = "Street address is required.";
    if (empty($checkout_data['address_city'])) $errors[] = "City is required.";
    if (empty($checkout_data['address_zip'])) $errors[] = "ZIP / Postal code is required.";
    if (empty($checkout_data['address_country'])) $errors[] = "Country is required.";


    if (empty($errors)) {
        mysqli_autocommit($conn, false);
        $order_placed_successfully = false;

        $user_id_sql = $user_id ? (int)$user_id : 'NULL';
        // ... (all escape_string calls as before) ...
        $email_esc = escape_string($checkout_data['email']);
        $fname_esc = escape_string($checkout_data['first_name']);
        $lname_esc = escape_string($checkout_data['last_name']);
        $street_esc = escape_string($checkout_data['address_street']);
        $city_esc = escape_string($checkout_data['address_city']);
        $state_esc = escape_string($checkout_data['address_state']);
        $zip_esc = escape_string($checkout_data['address_zip']);
        $country_esc = escape_string($checkout_data['address_country']);
        $phone_esc = escape_string($checkout_data['phone_number']);
        $notes_esc = escape_string($checkout_data['notes']);
        $shipping_method_esc = escape_string($checkout_data['shipping_method']);
        $payment_method_esc = escape_string($checkout_data['payment_method']);

        $sql_insert_order = "INSERT INTO orders (user_id, order_date, total_amount, status,
                                shipping_first_name, shipping_last_name, shipping_address_street,
                                shipping_address_city, shipping_address_state, shipping_address_zip, shipping_address_country,
                                shipping_method, shipping_cost, payment_method, payment_status,
                                customer_email, customer_phone, notes, created_at, updated_at)
                             VALUES ($user_id_sql, NOW(), $grand_total, 'pending',
                                     '$fname_esc', '$lname_esc', '$street_esc',
                                     '$city_esc', '$state_esc', '$zip_esc', '$country_esc',
                                     '$shipping_method_esc', $shipping_cost, '$payment_method_esc', 'pending',
                                     '$email_esc', '$phone_esc', '$notes_esc', NOW(), NOW())";

        if (mysqli_query($conn, $sql_insert_order)) {
            $order_id = mysqli_insert_id($conn);
            $order_items_inserted_successfully = true;

            foreach ($product_details_for_order as $product_id_ordered => $item) {
                // ... (stock update and item insert logic as before) ...
                $prod_id_esc = (int)$product_id_ordered;
                $qty_esc = (int)$item['quantity'];
                $price_esc = (float)$item['price_at_purchase'];
                $prod_name_esc = escape_string($item['name']);

                $sql_update_stock = "UPDATE products SET stock = stock - $qty_esc WHERE id = $prod_id_esc AND stock >= $qty_esc";
                if (!mysqli_query($conn, $sql_update_stock) || mysqli_affected_rows($conn) == 0) {
                    $errors[] = "Failed to update stock for product " . htmlspecialchars($item['name']) . ". Order cancelled.";
                    $order_items_inserted_successfully = false;
                    break;
                }

                $sql_insert_item = "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase, product_name_at_purchase, created_at)
                                    VALUES ($order_id, $prod_id_esc, $qty_esc, $price_esc, '$prod_name_esc', NOW())";
                if (!mysqli_query($conn, $sql_insert_item)) {
                    $errors[] = "Failed to save order item: " . htmlspecialchars($item['name']) . ". Error: " . mysqli_error($conn);
                    $order_items_inserted_successfully = false;
                    break;
                }
            }

            if ($order_items_inserted_successfully) {
                mysqli_commit($conn);
                $order_placed_successfully = true;
            } else {
                mysqli_rollback($conn);
            }
        } else {
            $errors[] = "Failed to create order. Error: " . mysqli_error($conn);
            mysqli_rollback($conn);
        }
        mysqli_autocommit($conn, true);

        if ($order_placed_successfully) {
            $_SESSION['cart'] = [];
            header("Location: " . SITE_URL . "order_success/?order_id=" . $order_id); // Updated redirect
            exit;
        }
    }
}

require_once __DIR__ . '/../templates/header.php'; // Adjusted path
?>

<div class="container py-5">
    <h1 class="mb-4">Checkout</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Please correct the following errors:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo SITE_URL; ?>checkout/" method="POST"> <!-- Updated form action -->
        <div class="row">
            <div class="col-md-7">
                <h4>Shipping Address</h4>
                <hr>
                <!-- Form fields as before -->
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="first_name">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($checkout_data['first_name']); ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="last_name">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($checkout_data['last_name']); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($checkout_data['email']); ?>" required <?php echo $user_id ? 'readonly' : ''; ?>>
                     <?php if ($user_id): ?> <small class="form-text text-muted">Email is tied to your account.</small><?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="address_street">Street Address</label>
                    <input type="text" class="form-control" id="address_street" name="address_street" value="<?php echo htmlspecialchars($checkout_data['address_street']); ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="address_city">City</label>
                        <input type="text" class="form-control" id="address_city" name="address_city" value="<?php echo htmlspecialchars($checkout_data['address_city']); ?>" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="address_state">State / Province</label>
                        <input type="text" class="form-control" id="address_state" name="address_state" value="<?php echo htmlspecialchars($checkout_data['address_state']); ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="address_zip">ZIP / Postal</label>
                        <input type="text" class="form-control" id="address_zip" name="address_zip" value="<?php echo htmlspecialchars($checkout_data['address_zip']); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="address_country">Country</label>
                    <input type="text" class="form-control" id="address_country" name="address_country" value="<?php echo htmlspecialchars($checkout_data['address_country']); ?>" required>
                </div>
                 <div class="form-group">
                    <label for="phone_number">Phone Number (Optional)</label>
                    <input type="tel" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($checkout_data['phone_number']); ?>">
                </div>
                <div class="form-group">
                    <label for="notes">Order Notes (Optional)</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($checkout_data['notes']); ?></textarea>
                </div>

                <h4 class="mt-4">Shipping Method</h4>
                <hr>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="shipping_method" id="flat_rate" value="flat_rate" checked>
                    <label class="form-check-label" for="flat_rate">
                        Flat Rate: $<?php echo number_format($shipping_cost, 2); ?>
                    </label>
                </div>

                <h4 class="mt-4">Payment Method</h4>
                <hr>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_method" id="placeholder_cod" value="placeholder_cod" checked>
                    <label class="form-check-label" for="placeholder_cod">
                        Cash on Delivery (Placeholder)
                    </label>
                    <small class="form-text text-muted">This is a dummy payment method. No actual payment will be processed.</small>
                </div>
            </div>

            <div class="col-md-5">
                <h4>Your Order</h4>
                <hr>
                <!-- Order summary table as before -->
                <table class="table table-sm">
                    <tbody>
                        <?php foreach ($product_details_for_order as $item_id => $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></td>
                            <td class="text-right">$<?php echo number_format($item['price_at_purchase'] * $item['quantity'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="font-weight-bold">
                            <td>Subtotal</td>
                            <td class="text-right">$<?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                        <tr>
                            <td>Shipping (Flat Rate)</td>
                            <td class="text-right">$<?php echo number_format($shipping_cost, 2); ?></td>
                        </tr>
                        <tr class="font-weight-bold h5">
                            <td>Total</td>
                            <td class="text-right">$<?php echo number_format($grand_total, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
                <p class="text-muted small">
                    Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our privacy policy.
                </p>
                <button type="submit" class="btn btn-primary btn-lg btn-block" <?php echo $total_items_in_cart == 0 ? 'disabled' : ''; ?>>Place Order</button>
            </div>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php'; // Adjusted path
?>
