<?php
session_start();
require_once '../config.php';
require_once '../includes/db.php';

$pageTitle = "Shopping Cart";
include_once '../templates/header.php';

$cart = $_SESSION['cart'] ?? [];
$products_in_cart = [];
$total = 0;

if (!empty($cart)) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $product_ids = array_keys($cart);
    $in = str_repeat('?,', count($product_ids) - 1) . '?';
    $stmt = $conn->prepare("SELECT id, name, price, image_url_main as image FROM products WHERE id IN ($in)");
    $stmt->bind_param(str_repeat('i', count($product_ids)), ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $products_in_cart[] = $row;
    }
    $stmt->close();

    foreach ($products_in_cart as $product) {
        $total += $product['price'] * $cart[$product['id']];
    }
    $conn->close();
}

?>

<div class="container my-5">
    <h1>Shopping Cart</h1>
    <?php if (empty($products_in_cart)): ?>
        <div class="alert alert-info mt-4">
            Your cart is empty. <a href="<?php echo SITE_URL; ?>">Continue shopping</a>.
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col" colspan="2">Product</th>
                                <th scope="col">Price</th>
                                <th scope="col">Quantity</th>
                                <th scope="col">Total</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products_in_cart as $product): ?>
                                <tr>
                                    <td style="width: 100px;">
                                        <img src="<?php echo SITE_URL . 'uploads/' . htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid">
                                    </td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                                    <td>
                                        <form action="<?php echo SITE_URL; ?>cart_actions.php" method="POST" class="d-flex">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="number" name="quantity" value="<?php echo $cart[$product['id']]; ?>" min="1" class="form-control" style="width: 70px;">
                                            <button type="submit" class="btn btn-sm btn-primary ms-2">Update</button>
                                        </form>
                                    </td>
                                    <td>$<?php echo number_format($product['price'] * $cart[$product['id']], 2); ?></td>
                                    <td>
                                        <a href="<?php echo SITE_URL; ?>cart_actions.php?action=remove&product_id=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger">&times;</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Cart Summary</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Subtotal
                                <span>$<?php echo number_format($total, 2); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>Total</strong>
                                <strong>$<?php echo number_format($total, 2); ?></strong>
                            </li>
                        </ul>
                        <a href="<?php echo SITE_URL; ?>checkout/" class="btn btn-primary btn-block mt-3">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
include_once '../templates/footer.php';
?>