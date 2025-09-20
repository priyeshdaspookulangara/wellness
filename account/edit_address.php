<?php
session_start();
require_once '../config.php';
require_once '../includes/db.php';

if (!isset($_SESSION["user_id"])) {
    header("location: " . SITE_URL . "/login");
    exit;
}

$user_id = $_SESSION['user_id'];
$address_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$errors = [];
$address_line1 = $address_line2 = $city = $state = $postal_code = $country = "";
$is_default = 0;

if ($address_id == 0) {
    header("location: " . SITE_URL . "/account/manage_addresses.php");
    exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the address to edit
$stmt = $conn->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $address_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 1) {
    $address = $result->fetch_assoc();
    $address_line1 = $address['address_line1'];
    $address_line2 = $address['address_line2'];
    $city = $address['city'];
    $state = $address['state'];
    $postal_code = $address['postal_code'];
    $country = $address['country'];
    $is_default = $address['is_default'];
} else {
    // Address not found or doesn't belong to the user
    header("location: " . SITE_URL . "/account/manage_addresses.php");
    exit;
}
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $address_line1 = trim($_POST['address_line1']);
    $address_line2 = trim($_POST['address_line2']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $postal_code = trim($_POST['postal_code']);
    $country = trim($_POST['country']);
    $is_default_post = isset($_POST['is_default']) ? 1 : 0;

    if (empty($address_line1) || empty($city) || empty($state) || empty($postal_code) || empty($country)) {
        $errors[] = "Please fill in all required fields.";
    }

    if (empty($errors)) {
        // If setting this as default, unset other defaults first
        if ($is_default_post) {
            $stmt = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
        }

        $sql = "UPDATE addresses SET address_line1 = ?, address_line2 = ?, city = ?, state = ?, postal_code = ?, country = ?, is_default = ? WHERE id = ? AND user_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssssiii", $address_line1, $address_line2, $city, $state, $postal_code, $country, $is_default_post, $address_id, $user_id);
            if ($stmt->execute()) {
                header("location: " . SITE_URL . "/account/manage_addresses.php");
                exit;
            } else {
                $errors[] = "Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
}
$conn->close();

$pageTitle = "Edit Address";
include_once '../templates/header.php';
?>

<div class="container">
    <h2 class="mt-5 mb-4">Edit Address</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post">
        <div class="form-group">
            <label>Address Line 1</label>
            <input type="text" name="address_line1" class="form-control" value="<?php echo htmlspecialchars($address_line1); ?>" required>
        </div>
        <div class="form-group">
            <label>Address Line 2 (Optional)</label>
            <input type="text" name="address_line2" class="form-control" value="<?php echo htmlspecialchars($address_line2); ?>">
        </div>
        <div class="form-group">
            <label>City</label>
            <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($city); ?>" required>
        </div>
        <div class="form-group">
            <label>State/Province</label>
            <input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($state); ?>" required>
        </div>
        <div class="form-group">
            <label>Postal Code</label>
            <input type="text" name="postal_code" class="form-control" value="<?php echo htmlspecialchars($postal_code); ?>" required>
        </div>
        <div class="form-group">
            <label>Country</label>
            <input type="text" name="country" class="form-control" value="<?php echo htmlspecialchars($country); ?>" required>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" name="is_default" class="form-check-input" id="is_default" <?php echo $is_default ? 'checked' : ''; ?>>
            <label class="form-check-label" for="is_default">Set as default address</label>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="<?php echo SITE_URL; ?>/account/manage_addresses.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php
include_once '../templates/footer.php';
?>
