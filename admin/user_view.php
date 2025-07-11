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

$user_id_to_view = (int)($_GET['id'] ?? 0);
if (!$user_id_to_view) {
    $_SESSION['error_message'] = "Invalid user ID.";
    header("Location: users.php");
    exit;
}

$page_title = "View/Edit User";
require_once 'includes/header.php';

// Fetch user data
$sql_fetch_user = "SELECT * FROM users WHERE id = $user_id_to_view";
$result_fetch_user = mysqli_query($conn, $sql_fetch_user);
if (!$result_fetch_user || mysqli_num_rows($result_fetch_user) === 0) {
    $_SESSION['error_message'] = "User not found.";
    header("Location: users.php");
    exit;
}
$user_data = mysqli_fetch_assoc($result_fetch_user);

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission for updates
    $new_data = [
        'username' => trim($_POST['username'] ?? $user_data['username']), // Username generally shouldn't be changed by admin easily
        'email' => trim($_POST['email'] ?? $user_data['email']),
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'address_street' => trim($_POST['address_street'] ?? ''),
        'address_city' => trim($_POST['address_city'] ?? ''),
        'address_state' => trim($_POST['address_state'] ?? ''),
        'address_zip' => trim($_POST['address_zip'] ?? ''),
        'address_country' => trim($_POST['address_country'] ?? ''),
        'phone_number' => trim($_POST['phone_number'] ?? ''),
        'is_admin' => (isset($_POST['is_admin']) && $user_id_to_view != $_SESSION['user_id']) ? 1 : ($user_data['is_admin'] ? 1:0) // Prevent self-demotion
    ];

    // Basic validation for editable fields
    if (empty($new_data['email']) || !filter_var($new_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email is required.";
    }
    // Add more validation as needed

    // Check if email or username is being changed and if it conflicts
    if (empty($errors)) {
        if ($new_data['email'] !== $user_data['email']) {
            $email_esc_check = escape_string($new_data['email']);
            $sql_email_conflict = "SELECT id FROM users WHERE email = '$email_esc_check' AND id != $user_id_to_view";
            $res_email_conflict = mysqli_query($conn, $sql_email_conflict);
            if (mysqli_num_rows($res_email_conflict) > 0) {
                $errors[] = "The new email address is already in use by another account.";
            }
        }
        // Similar check for username if it were editable by admin
    }


    if (empty($errors)) {
        $email_esc = escape_string($new_data['email']);
        $fname_esc = escape_string($new_data['first_name']);
        $lname_esc = escape_string($new_data['last_name']);
        $street_esc = escape_string($new_data['address_street']);
        $city_esc = escape_string($new_data['address_city']);
        $state_esc = escape_string($new_data['address_state']);
        $zip_esc = escape_string($new_data['address_zip']);
        $country_esc = escape_string($new_data['address_country']);
        $phone_esc = escape_string($new_data['phone_number']);
        $is_admin_sql = (int)$new_data['is_admin'];

        $sql_update_user = "UPDATE users SET
                                email = '$email_esc',
                                first_name = '$fname_esc',
                                last_name = '$lname_esc',
                                address_street = '$street_esc',
                                address_city = '$city_esc',
                                address_state = '$state_esc',
                                address_zip = '$zip_esc',
                                address_country = '$country_esc',
                                phone_number = '$phone_esc',
                                is_admin = $is_admin_sql,
                                updated_at = NOW()
                            WHERE id = $user_id_to_view";

        if (mysqli_query($conn, $sql_update_user)) {
            $success_message = "User details updated successfully.";
            // Re-fetch data to show updated values
            $result_fetch_user = mysqli_query($conn, $sql_fetch_user);
            $user_data = mysqli_fetch_assoc($result_fetch_user);
        } else {
            $errors[] = "Failed to update user details: " . mysqli_error($conn);
        }
    }
     // Update $user_data with $new_data for sticky form fields in case of error
    $user_data = array_merge($user_data, $new_data);
}

// Fetch user's order history (summary)
$user_orders = [];
$sql_user_orders = "SELECT id, order_date, total_amount, status FROM orders WHERE user_id = $user_id_to_view ORDER BY order_date DESC LIMIT 10";
$res_user_orders = mysqli_query($conn, $sql_user_orders);
if($res_user_orders) {
    while($order_row = mysqli_fetch_assoc($res_user_orders)) {
        $user_orders[] = $order_row;
    }
}

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?>: <?php echo htmlspecialchars($user_data['username']); ?></h1>
    <a href="users.php" class="btn btn-sm btn-outline-secondary">Back to Users</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <strong>Error!</strong> Please correct the following issues:
        <ul><?php foreach ($errors as $error) echo "<li>" . htmlspecialchars($error) . "</li>"; ?></ul>
    </div>
<?php endif; ?>
<?php if ($success_message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<form action="user_view.php?id=<?php echo $user_id_to_view; ?>" method="POST">
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Account Information</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" readonly>
                        <small class="form-text text-muted">Username cannot be changed by admin.</small>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                    </div>
                     <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user_data['first_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user_data['last_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="is_admin">User Role</label>
                        <select class="form-control" id="is_admin" name="is_admin" <?php echo ($user_id_to_view == $_SESSION['user_id']) ? 'disabled' : ''; ?>>
                            <option value="0" <?php echo (!$user_data['is_admin']) ? 'selected' : ''; ?>>Customer</option>
                            <option value="1" <?php echo ($user_data['is_admin']) ? 'selected' : ''; ?>>Admin</option>
                        </select>
                        <?php if ($user_id_to_view == $_SESSION['user_id']): ?>
                        <small class="form-text text-muted">You cannot change your own role.</small>
                        <?php endif; ?>
                    </div>
                    <p>Registered: <?php echo date("M j, Y, g:i a", strtotime($user_data['created_at'])); ?></p>
                    <p>Last Updated: <?php echo date("M j, Y, g:i a", strtotime($user_data['updated_at'])); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Address & Contact</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="address_street">Street Address</label>
                        <input type="text" class="form-control" id="address_street" name="address_street" value="<?php echo htmlspecialchars($user_data['address_street'] ?? ''); ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label for="address_city">City</label>
                            <input type="text" class="form-control" id="address_city" name="address_city" value="<?php echo htmlspecialchars($user_data['address_city'] ?? ''); ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="address_state">State/Province</label>
                            <input type="text" class="form-control" id="address_state" name="address_state" value="<?php echo htmlspecialchars($user_data['address_state'] ?? ''); ?>">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="address_zip">ZIP/Postal</label>
                            <input type="text" class="form-control" id="address_zip" name="address_zip" value="<?php echo htmlspecialchars($user_data['address_zip'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address_country">Country</label>
                        <input type="text" class="form-control" id="address_country" name="address_country" value="<?php echo htmlspecialchars($user_data['address_country'] ?? ''); ?>">
                    </div>
                     <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input type="tel" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user_data['phone_number'] ?? ''); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
             <button type="submit" class="btn btn-primary">Save User Details</button>
             <!-- Add "Reset Password" button later if needed - requires careful implementation -->
        </div>
    </div>
</form>

<hr class="my-4">

<div class="row mt-4">
    <div class="col-md-12">
        <h4>Recent Order History (Last 10)</h4>
        <?php if(!empty($user_orders)): ?>
        <div class="table-responsive">
        <table class="table table-sm table-striped">
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
                <?php foreach($user_orders as $uo): ?>
                <tr>
                    <td>#<?php echo $uo['id']; ?></td>
                    <td><?php echo date("M j, Y", strtotime($uo['order_date'])); ?></td>
                    <td>$<?php echo number_format($uo['total_amount'], 2); ?></td>
                    <td><?php echo ucfirst(htmlspecialchars($uo['status'])); ?></td>
                    <td><a href="order_view.php?id=<?php echo $uo['id']; ?>" class="btn btn-xs btn-info">View Order</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
        <p>No orders found for this user.</p>
        <?php endif; ?>
    </div>
</div>

<hr class="my-4">

<div class="row mt-4">
    <div class="col-md-12">
        <h4>Liked Products by This User</h4>
        <?php
        $user_liked_products = [];
        $sql_user_likes = "SELECT p.id, p.name, p.slug, upl.created_at as liked_at
                           FROM user_product_likes upl
                           JOIN products p ON upl.product_id = p.id
                           WHERE upl.user_id = $user_id_to_view
                           ORDER BY upl.created_at DESC";
        $res_user_likes = mysqli_query($conn, $sql_user_likes);
        if($res_user_likes) {
            while($like_row = mysqli_fetch_assoc($res_user_likes)) {
                $user_liked_products[] = $like_row;
            }
        }
        ?>
        <?php if(!empty($user_liked_products)): ?>
        <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Liked On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($user_liked_products as $liked_prod): ?>
                <tr>
                    <td><?php echo $liked_prod['id']; ?></td>
                    <td><a href="<?php echo SITE_URL . '/product.php?slug=' . htmlspecialchars($liked_prod['slug']); ?>" target="_blank"><?php echo htmlspecialchars($liked_prod['name']); ?></a></td>
                    <td><?php echo date("M j, Y, g:i a", strtotime($liked_prod['liked_at'])); ?></td>
                    <td>
                        <a href="product_edit.php?id=<?php echo $liked_prod['id']; ?>" class="btn btn-xs btn-outline-secondary">View Product</a>
                        <!-- Option to remove like by admin? Maybe too much for this scope -->
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
        <p>This user has not liked any products yet.</p>
        <?php endif; ?>
    </div>
</div>


<?php
require_once 'includes/footer.php';
?>
