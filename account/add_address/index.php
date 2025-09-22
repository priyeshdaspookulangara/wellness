<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION["user_id"])) {
    header("location: " . SITE_URL . "login/");
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];
$address_line1 = $address_line2 = $city = $state = $postal_code = $country = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $address_line1 = trim($_POST['address_line1']);
    $address_line2 = trim($_POST['address_line2']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $postal_code = trim($_POST['postal_code']);
    $country = trim($_POST['country']);
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    if (empty($address_line1) || empty($city) || empty($state) || empty($postal_code) || empty($country)) {
        $errors[] = "Please fill in all required fields.";
    }

    if (empty($errors)) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

        // If setting this as default, unset other defaults first
        if ($is_default) {
            $stmt_unset = $conn->prepare("UPDATE addresses SET `is_default` = 0 WHERE user_id = ?");
            $stmt_unset->bind_param("i", $user_id);
            $stmt_unset->execute();
            $stmt_unset->close();
        }

        $sql = "INSERT INTO addresses (user_id, address_line1, address_line2, city, state, postal_code, country, `is_default`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("issssssi", $user_id, $address_line1, $address_line2, $city, $state, $postal_code, $country, $is_default);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Address added successfully!";
                $_SESSION['message_type'] = "success";
                header("location: " . SITE_URL . "account/manage_addresses/");
                exit;
            } else {
                $errors[] = "Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
        $conn->close();
    }
}

$pageTitle = "Add New Address";
include_once '../../templates/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-3">
            <?php include_once '../includes/sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <h2 class="mt-5 mb-4">Add New Address</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
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
                    <input type="checkbox" name="is_default" class="form-check-input" id="is_default">
                    <label class="form-check-label" for="is_default">Set as default address</label>
                </div>
                <button type="submit" class="btn btn-primary">Save Address</button>
                <a href="<?php echo SITE_URL; ?>account/manage_addresses/" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php
include_once '../../templates/footer.php';
?>
