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

$affiliate_id = $_GET['id'] ?? null;
if (!$affiliate_id) {
    header("Location: " . SITE_URL . "admin/affiliates/");
    exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch affiliate data first, as it's needed for both POST handling and form display
$stmt_affiliate = $conn->prepare("SELECT a.id, a.referral_code, a.status, u.name, u.email FROM affiliates a JOIN users u ON a.user_id = u.id WHERE a.id = ?");
$stmt_affiliate->bind_param("i", $affiliate_id);
$stmt_affiliate->execute();
$result_affiliate = $stmt_affiliate->get_result();
if ($result_affiliate->num_rows === 0) {
    $_SESSION['error_message'] = "Affiliate not found.";
    header("Location: " . SITE_URL . "admin/affiliates/");
    exit;
}
$affiliate = $result_affiliate->fetch_assoc();
$stmt_affiliate->close();


$error_message = '';
$success_message = '';

// Handle form submission before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? 'inactive';
    $referral_code = $_POST['referral_code'] ?? $affiliate['referral_code'];

    if (empty($referral_code)) {
        $error_message = "Referral code cannot be empty.";
    } else {
        // Check if referral code is unique (if changed)
        if ($referral_code !== $affiliate['referral_code']) {
            $stmt_check = $conn->prepare("SELECT id FROM affiliates WHERE referral_code = ? AND id != ?");
            $stmt_check->bind_param("si", $referral_code, $affiliate_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            if ($result_check->num_rows > 0) {
                $error_message = "This referral code is already in use.";
            }
        }

        if (empty($error_message)) {
            $stmt_update = $conn->prepare("UPDATE affiliates SET referral_code = ?, status = ? WHERE id = ?");
            $stmt_update->bind_param("ssi", $referral_code, $status, $affiliate_id);
            if ($stmt_update->execute()) {
                $_SESSION['success_message'] = "Affiliate updated successfully.";
                header("Location: " . SITE_URL . "admin/affiliates/");
                exit;
            } else {
                $error_message = "Failed to update affiliate. Please try again.";
            }
        }
    }
}


$page_title = "Edit Affiliate";
$breadcrumbs = [
    ['name' => 'Affiliates', 'url' => SITE_URL . 'admin/affiliates/'],
    ['name' => 'Edit Affiliate']
];
require_once __DIR__ . '/../includes/header.php';

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
</div>

<?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<form action="" method="POST">
    <div class="mb-3">
        <label class="form-label">User</label>
        <input type="text" class="form-control" value="<?php echo htmlspecialchars($affiliate['name']); ?> (<?php echo htmlspecialchars($affiliate['email']); ?>)" readonly>
    </div>
    <div class="mb-3">
        <label for="referral_code" class="form-label">Referral Code</label>
        <input type="text" class="form-control" id="referral_code" name="referral_code" value="<?php echo htmlspecialchars($affiliate['referral_code']); ?>" required>
    </div>
    <div class="mb-3">
        <label for="status" class="form-label">Status</label>
        <select class="form-control" id="status" name="status" required>
            <option value="active" <?php echo ($affiliate['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?php echo ($affiliate['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Update Affiliate</button>
</form>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>