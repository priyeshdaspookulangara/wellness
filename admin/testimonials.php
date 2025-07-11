<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

// Admin authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $_SESSION['admin_error'] = "Access denied.";
    header("Location: " . SITE_URL . "/login.php");
    exit;
}

$page_title = "Manage Testimonials/Reviews";
require_once 'includes/header.php';

// Handle actions: approve, reject, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'])) {
    $review_id_action = (int)$_POST['review_id'];
    $action = $_POST['action'] ?? '';

    if ($review_id_action > 0) {
        switch ($action) {
            case 'approve':
                $sql_action = "UPDATE reviews SET status = 'approved', updated_at = NOW() WHERE id = $review_id_action";
                $action_msg = "approved";
                break;
            case 'reject':
                $sql_action = "UPDATE reviews SET status = 'rejected', updated_at = NOW() WHERE id = $review_id_action";
                $action_msg = "rejected";
                break;
            case 'delete':
                $sql_action = "DELETE FROM reviews WHERE id = $review_id_action";
                $action_msg = "deleted";
                break;
            default:
                $sql_action = null;
        }

        if ($sql_action) {
            if (mysqli_query($conn, $sql_action)) {
                $_SESSION['success_message'] = "Review #$review_id_action has been $action_msg.";
            } else {
                $_SESSION['error_message'] = "Failed to $action_msg review #$review_id_action: " . mysqli_error($conn);
            }
        } else {
            $_SESSION['error_message'] = "Invalid action for review #$review_id_action.";
        }
    } else {
        $_SESSION['error_message'] = "Invalid review ID for action.";
    }
    header("Location: testimonials.php" . (isset($_GET['filter_status']) ? "?filter_status=".$_GET['filter_status'] : ""));
    exit;
}


// Filtering
$filter_status_admin = $_GET['filter_status'] ?? 'pending'; // Default to pending
$where_clause_admin = '';
if (!empty($filter_status_admin)) {
    $status_esc_admin = escape_string($filter_status_admin);
    $where_clause_admin = "WHERE r.status = '$status_esc_admin'";
}


// Fetch reviews
$reviews_admin = [];
$sql_reviews_admin = "SELECT r.id, r.rating, r.comment, r.status, r.created_at,
                             u.username AS user_username, p.name AS product_name, p.id as product_id
                      FROM reviews r
                      JOIN users u ON r.user_id = u.id
                      JOIN products p ON r.product_id = p.id
                      $where_clause_admin
                      ORDER BY r.created_at DESC";
// Add LIMIT and pagination later

$result_reviews_admin = mysqli_query($conn, $sql_reviews_admin);
if ($result_reviews_admin) {
    while ($row = mysqli_fetch_assoc($result_reviews_admin)) {
        $reviews_admin[] = $row;
    }
} else {
    echo "<div class='alert alert-danger'>Error fetching reviews: " . mysqli_error($conn) . "</div>";
}

$all_review_statuses = ['pending', 'approved', 'rejected'];

// Handle messages
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
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


<div class="mb-3">
    <form action="testimonials.php" method="GET" class="form-inline">
        <label for="filter_status" class="mr-2">Filter by Status:</label>
        <select name="filter_status" id="filter_status" class="form-control mr-2">
            <option value="">All Statuses</option>
            <?php foreach ($all_review_statuses as $status_opt): ?>
                <option value="<?php echo $status_opt; ?>" <?php echo ($filter_status_admin == $status_opt) ? 'selected' : ''; ?>>
                    <?php echo ucfirst($status_opt); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
         <?php if (!empty($filter_status_admin)): ?>
            <a href="testimonials.php" class="btn btn-outline-secondary ml-2">Clear Filter / Show Pending</a>
        <?php endif; ?>
    </form>
</div>


<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>User</th>
                <th>Rating</th>
                <th>Comment</th>
                <th>Submitted</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($reviews_admin)): ?>
                <?php foreach ($reviews_admin as $review_item): ?>
                    <tr>
                        <td><?php echo $review_item['id']; ?></td>
                        <td><a href="<?php echo SITE_URL . '/product.php?slug=' . urlencode($review_item['product_id']); // Assuming you'll fetch slug or have it ?>" target="_blank"><?php echo htmlspecialchars($review_item['product_name']); ?></a></td>
                        <td><?php echo htmlspecialchars($review_item['user_username']); ?></td>
                        <td><?php echo $review_item['rating']; ?>/5</td>
                        <td><?php echo htmlspecialchars(substr($review_item['comment'], 0, 100)) . (strlen($review_item['comment']) > 100 ? '...' : ''); ?></td>
                        <td><?php echo date("M j, Y", strtotime($review_item['created_at'])); ?></td>
                        <td><span class="badge badge-<?php echo $review_item['status'] == 'approved' ? 'success' : ($review_item['status'] == 'pending' ? 'warning' : 'danger'); ?>"><?php echo ucfirst($review_item['status']); ?></span></td>
                        <td class="action-buttons">
                            <form action="testimonials.php<?php echo !empty($filter_status_admin) ? '?filter_status='.$filter_status_admin : ''; ?>" method="POST" style="display: inline;">
                                <input type="hidden" name="review_id" value="<?php echo $review_item['id']; ?>">
                                <?php if ($review_item['status'] != 'approved'): ?>
                                <button type="submit" name="action" value="approve" class="btn btn-sm btn-success" title="Approve"><i class="fas fa-check"></i></button>
                                <?php endif; ?>
                                <?php if ($review_item['status'] != 'rejected'): ?>
                                <button type="submit" name="action" value="reject" class="btn btn-sm btn-warning" title="Reject"><i class="fas fa-times"></i></button>
                                <?php endif; ?>
                                <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger confirm-delete" title="Delete"><i class="fas fa-trash"></i></button>
                                <!-- Add Edit button later if needed: <a href="testimonial_edit.php?id=<?php echo $review_item['id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a> -->
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No reviews found<?php echo !empty($filter_status_admin) ? ' with status: ' . $filter_status_admin : ''; ?>.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<?php
require_once 'includes/footer.php';
?>
