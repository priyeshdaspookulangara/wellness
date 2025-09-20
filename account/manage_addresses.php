<?php
session_start();
require_once '../config.php';
require_once '../includes/db.php';

if (!isset($_SESSION["user_id"])) {
    header("location: " . SITE_URL . "/login");
    exit;
}

$pageTitle = "Manage Addresses";
include_once '../templates/header.php';

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION["user_id"];

// Fetch addresses for the current user
$sql = "SELECT id, address_line1, address_line2, city, state, postal_code, country, is_default FROM addresses WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$addresses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<div class="container">
    <h2 class="mt-5 mb-4">Manage Addresses</h2>
    <a href="<?php echo SITE_URL; ?>/account/add_address.php" class="btn btn-primary mb-3">Add New Address</a>

    <?php if (count($addresses) > 0): ?>
        <div class="row">
            <?php foreach ($addresses as $address): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?php echo htmlspecialchars($address['address_line1']); ?>
                                <?php if ($address['is_default']): ?>
                                    <span class="badge badge-success">Default</span>
                                <?php endif; ?>
                            </h5>
                            <p class="card-text">
                                <?php echo htmlspecialchars($address['address_line2']); ?><br>
                                <?php echo htmlspecialchars($address['city']); ?>, <?php echo htmlspecialchars($address['state']); ?> <?php echo htmlspecialchars($address['postal_code']); ?><br>
                                <?php echo htmlspecialchars($address['country']); ?>
                            </p>
                            <a href="<?php echo SITE_URL; ?>/account/edit_address.php?id=<?php echo $address['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                            <a href="<?php echo SITE_URL; ?>/account/delete_address.php?id=<?php echo $address['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this address?');">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            You have not saved any addresses yet.
        </div>
    <?php endif; ?>
</div>

<?php
include_once '../templates/footer.php';
?>
