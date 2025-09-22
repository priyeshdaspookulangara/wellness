<?php
session_start();
require_once '../../config.php';

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["user_id"])) {
    header("location: " . SITE_URL . "login/");
    exit;
}

$pageTitle = "Manage Addresses";
include_once '../../templates/header.php';

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION["user_id"];

// Fetch addresses for the current user
// Quote `is_default` as it can be a reserved keyword
$sql = "SELECT id, address_line1, address_line2, city, state, postal_code, country, `is_default` FROM addresses WHERE user_id = ? ORDER BY `is_default` DESC, id DESC";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    // Handle prepare error, useful for debugging
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$addresses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<div class="container">
    <div class="row">
        <div class="col-md-3">
            <?php include_once '../includes/sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <h2 class="mt-5 mb-4">Manage Addresses</h2>
            <a href="<?php echo SITE_URL; ?>account/add_address/" class="btn btn-primary mb-4">Add New Address</a>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_SESSION['message']); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>

            <?php if (count($addresses) > 0): ?>
                <div class="row">
                    <?php foreach ($addresses as $address): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <?php echo htmlspecialchars($address['address_line1']); ?>
                                        <?php if ($address['is_default']): ?>
                                            <span class="badge bg-success">Default</span>
                                        <?php endif; ?>
                                    </h5>
                                    <p class="card-text">
                                        <?php if(!empty($address['address_line2'])) echo htmlspecialchars($address['address_line2']) . '<br>'; ?>
                                        <?php echo htmlspecialchars($address['city']); ?>, <?php echo htmlspecialchars($address['state']); ?> <?php echo htmlspecialchars($address['postal_code']); ?><br>
                                        <?php echo htmlspecialchars($address['country']); ?>
                                    </p>
                                    <a href="<?php echo SITE_URL; ?>account/edit_address/?id=<?php echo $address['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <a href="<?php echo SITE_URL; ?>account/delete_address/?id=<?php echo $address['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this address? You cannot delete your default address.');">Delete</a>
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
    </div>
</div>

<?php
include_once '../../templates/footer.php';
?>
