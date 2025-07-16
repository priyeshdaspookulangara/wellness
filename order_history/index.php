<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "/login");
    exit;
}

$db = db_connect();
$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("
    SELECT * FROM orders
    WHERE user_id = :user_id
    ORDER BY order_date DESC
");
$stmt->bindValue(':user_id', $user_id);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../templates/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2>Order History</h2>
        <?php if (empty($orders)): ?>
            <div class="alert alert-info">You have no past orders.</div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo $order['order_date']; ?></td>
                            <td>$<?php echo number_format($order['total'], 2); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td>
                                <a href="<?php echo SITE_URL; ?>/order_details?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
