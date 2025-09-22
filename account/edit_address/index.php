<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION["user_id"])) {
    header("location: " . SITE_URL . "login/");
    exit;
}

$user_id = $_SESSION['user_id'];
$address_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$errors = [];

if ($address_id === 0) {
    header("location: " . SITE_URL . "account/manage_addresses/");
    exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// Fetch the address to edit, ensuring it belongs to the user
$stmt = $conn->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $address_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    header("location: " . SITE_URL . "account/manage_addresses/");
    exit;
}
$address = $result->fetch_assoc();
$stmt->close();

// Initialize form variables with existing data
$address_line1 = $address['address_line1'];
$address_line2 = $address['address_line2'];
$city = $address['city'];
$state = $address['state'];
$postal_code = $address['postal_code'];
$country = $address['country'];
$is_default = $address['is_default'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Overwrite with POST data
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
        if ($is_default_post) {
            $stmt_unset = $conn->prepare("UPDATE addresses SET `is_default` = 0 WHERE user_id = ?");
            $stmt_unset->bind_param("i", $user_id);
            $stmt_unset->execute();
            $stmt_unset->close();
        }

        $sql = "UPDATE addresses SET address_line1 = ?, address_line2 = ?, city = ?, state = ?, postal_code = ?, country = ?, `is_default` = ? WHERE id = ? AND user_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssssiii", $address_line1, $address_line2, $city, $state, $postal_code, $country, $is_default_post, $address_id, $user_id);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Address updated successfully!";
                $_SESSION['message_type'] = "success";
                header("location: " . SITE_URL . "account/manage_addresses/");
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
include_once '../../templates/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-3">
            <?php include_once '../includes/sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <h2 class="mt-5 mb-4">Edit Address</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post">
                <div class="form-group mb-3">
                    <label for="address_line1">Address Line 1</label>
                    <input type="text" name="address_line1" id="address_line1" class="form-control" value="<?php echo htmlspecialchars($address_line1); ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label for="address_line2">Address Line 2 (Optional)</label>
                    <input type="text" name="address_line2" id="address_line2" class="form-control" value="<?php echo htmlspecialchars($address_line2); ?>">
                </div>
                <div class="form-group mb-3">
                    <label for="city">City</label>
                    <input type="text" name="city" id="city" class="form-control" value="<?php echo htmlspecialchars($city); ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label for="state">State/Province</label>
                    <input type="text" name="state" id="state" class="form-control" value="<?php echo htmlspecialchars($state); ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label for="postal_code">Postal Code</label>
                    <input type="text" name="postal_code" id="postal_code" class="form-control" value="<?php echo htmlspecialchars($postal_code); ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label for="country">Country</label>
                    <input type="text" name="country" id="country" class="form-control" value="<?php echo htmlspecialchars($country); ?>" required>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="is_default" class="form-check-input" id="is_default" <?php if($is_default) echo 'checked'; ?>>
                    <label class="form-check-label" for="is_default">Set as default address</label>
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?php echo SITE_URL; ?>account/manage_addresses/" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php
include_once '../../templates/footer.php';
?>
