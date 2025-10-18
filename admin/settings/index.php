<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';

// Admin authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $_SESSION['admin_error'] = "Access denied.";
    header("Location: " . SITE_URL . "login/");
    exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commission_rate = $_POST['commission_rate'] ?? '10';

    // Validate that it's a number
    if (is_numeric($commission_rate) && $commission_rate >= 0 && $commission_rate <= 100) {
        // Use an UPSERT-like query to either update the existing key or insert a new one
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('affiliate_commission_rate', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("ss", $commission_rate, $commission_rate);
        if ($stmt->execute()) {
            $success_message = "Settings updated successfully.";
        } else {
            $error_message = "Failed to update settings.";
        }
        $stmt->close();
    } else {
        $error_message = "Please enter a valid commission rate (a number between 0 and 100).";
    }
}

// Fetch current setting value
$commission_rate_value = '10'; // Default value
$stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'affiliate_commission_rate'");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $commission_rate_value = $result->fetch_assoc()['setting_value'];
}
$stmt->close();


$page_title = "Site Settings";
$breadcrumbs = [ ['name' => 'Settings'] ];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
</div>

<?php if ($success_message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        Affiliate Settings
    </div>
    <div class="card-body">
        <form action="" method="POST">
            <div class="mb-3">
                <label for="commission_rate" class="form-label">Commission Rate (%)</label>
                <input type="number" class="form-control" id="commission_rate" name="commission_rate" value="<?php echo htmlspecialchars($commission_rate_value); ?>" min="0" max="100" step="0.1" required>
                <div class="form-text">The percentage of a sale that affiliates will earn as a commission.</div>
            </div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>