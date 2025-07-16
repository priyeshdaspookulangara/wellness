<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

// Redirect if cart is empty or user is not logged in
if (empty($_SESSION['cart']) || !isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "/cart");
    exit;
}

$db = db_connect();
$user_id = $_SESSION['user_id'];

// Fetch user's addresses
$stmt = $db->prepare("SELECT * FROM addresses WHERE user_id = :user_id");
$stmt->bindValue(':user_id', $user_id);
$stmt->execute();
$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../templates/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <h2>Checkout</h2>
        <form action="place_order.php" method="post">
            <h4>Shipping Address</h4>
            <?php if (empty($addresses)): ?>
                <div class="alert alert-warning">
                    You have no saved addresses. <a href="<?php echo SITE_URL; ?>/manage_addresses">Add an address</a>.
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label for="address_id">Select an Address</label>
                    <select class="form-control" id="address_id" name="address_id" required>
                        <?php foreach ($addresses as $address): ?>
                            <option value="<?php echo $address['id']; ?>">
                                <?php echo htmlspecialchars($address['address_line_1'] . ', ' . $address['city']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <h4 class="mt-4">Payment Information</h4>
            <p>This is a placeholder for a payment gateway integration (e.g., Stripe, PayPal).</p>

            <button type="submit" class="btn btn-primary" <?php echo empty($addresses) ? 'disabled' : ''; ?>>
                Place Order
            </button>
        </form>
    </div>
    <div class="col-md-4">
        <h4>Order Summary</h4>
        <!-- Order summary can be loaded here -->
    </div>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
