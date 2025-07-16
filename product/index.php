<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

if (!isset($_GET['id'])) {
    header("Location: " . SITE_URL);
    exit;
}

$db = db_connect();
$stmt = $db->prepare("SELECT * FROM products WHERE id = :id");
$stmt->bindValue(':id', $_GET['id']);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    // Redirect to a 404 page or home
    header("Location: " . SITE_URL . "/404.php");
    exit;
}

require_once __DIR__ . '/../templates/header.php';
?>

<div class="row">
    <div class="col-md-6">
        <img src="https://via.placeholder.com/500x500.png?text=Product+Image" class="img-fluid" alt="<?php echo htmlspecialchars($product['name']); ?>">
    </div>
    <div class="col-md-6">
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        <p class="lead"><?php echo htmlspecialchars($product['description']); ?></p>
        <h3>$<?php echo number_format($product['price'], 2); ?></h3>

        <form action="<?php echo SITE_URL; ?>/cart_actions.php" method="post">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1">
            </div>
            <button type="submit" class="btn btn-primary">Add to Cart</button>
        </form>
    </div>
</div>

<div class="row mt-5">
    <div class="col-12">
        <h3>Product Reviews</h3>
        <!-- Reviews section placeholder -->
    </div>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
