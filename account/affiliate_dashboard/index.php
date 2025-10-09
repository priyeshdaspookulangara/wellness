<?php
session_start();
require_once '../../config.php';
require_once '../../includes/db.php';

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("location: " . SITE_URL . "login/");
    exit;
}

$user_id = $_SESSION["user_id"];
$db = db_connect();

// Check if the user is an affiliate
$stmt = $db->prepare("SELECT id, referral_code FROM affiliates WHERE user_id = ? AND status = 'active'");
$stmt->execute([$user_id]);
$affiliate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$affiliate) {
    // If not an affiliate, redirect to account dashboard
    $_SESSION['message'] = "You do not have an active affiliate account.";
    $_SESSION['message_type'] = "warning";
    header("location: " . SITE_URL . "account/");
    exit;
}

$affiliate_id = $affiliate['id'];
$referral_code = $affiliate['referral_code'];

// Fetch summary data
// Total Referrals
$stmt_referrals = $db->prepare("SELECT COUNT(*) as total_referrals FROM referrals WHERE affiliate_id = ?");
$stmt_referrals->execute([$affiliate_id]);
$total_referrals = $stmt_referrals->fetchColumn();

// Commission stats
$stmt_commissions = $db->prepare("
    SELECT
        SUM(commission_amount) as total_earnings,
        SUM(CASE WHEN status = 'paid' THEN commission_amount ELSE 0 END) as paid_earnings,
        SUM(CASE WHEN status = 'pending' THEN commission_amount ELSE 0 END) as pending_earnings
    FROM commissions
    WHERE affiliate_id = ?
");
$stmt_commissions->execute([$affiliate_id]);
$commission_stats = $stmt_commissions->fetch(PDO::FETCH_ASSOC);

// Fetch detailed commission history
$stmt_history = $db->prepare("SELECT order_id, commission_amount, status, created_at FROM commissions WHERE affiliate_id = ? ORDER BY created_at DESC");
$stmt_history->execute([$affiliate_id]);
$commissions = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Affiliate Dashboard";
include_once '../../templates/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-3">
            <?php include_once '../includes/sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <h2 class="mt-5 mb-4">Affiliate Dashboard</h2>

            <h4>Your Referral Code</h4>
            <div class="alert alert-info">
                Your unique referral code is: <strong><?php echo htmlspecialchars($referral_code); ?></strong>
                <p>Share this code with others. When they use it to register, you'll earn a commission on their purchases!</p>
            </div>

            <h4>Performance Overview</h4>
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total Referrals</h5>
                            <p class="card-text display-4"><?php echo $total_referrals; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Pending Commissions</h5>
                            <p class="card-text display-4">$<?php echo number_format($commission_stats['pending_earnings'] ?? 0, 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total Paid</h5>
                            <p class="card-text display-4">$<?php echo number_format($commission_stats['paid_earnings'] ?? 0, 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <h4>Commission History</h4>
            <?php if (count($commissions) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Commission Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($commissions as $commission): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($commission['order_id']); ?></td>
                                    <td>$<?php echo htmlspecialchars(number_format($commission['commission_amount'], 2)); ?></td>
                                    <td>
                                        <?php if ($commission['status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php elseif ($commission['status'] === 'paid'): ?>
                                            <span class="badge bg-success">Paid</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(date("F j, Y", strtotime($commission['created_at']))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    You have not earned any commissions yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include_once '../../templates/footer.php';
?>