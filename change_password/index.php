<?php
session_start();
include_once '../config.php';
include_once '../includes/db.php';

$token = $_GET['token'] ?? '';
$message = '';
$show_form = false;

if ($token) {
    // Check if token is valid
    $stmt = $db->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $reset_request = $result->fetch_assoc();
    $stmt->close();

    if ($reset_request) {
        if (date("U") < $reset_request['expires_at']) {
            $show_form = true;
            $email = $reset_request['email'];
        } else {
            $message = 'This password reset link has expired.';
        }
    } else {
        $message = 'Invalid password reset link.';
    }
} else {
    $message = 'No password reset token provided.';
}

if ($show_form && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if ($password === $password_confirm) {
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update the user's password
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        $stmt->execute();
        $stmt->close();

        // Delete the token
        $stmt = $db->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->close();

        $message = 'Your password has been successfully updated. You can now <a href="' . SITE_URL . '/login">login</a> with your new password.';
        $show_form = false;
    } else {
        $message = 'The passwords do not match.';
    }
}

$pageTitle = 'Change Password';
include_once '../templates/header.php';
?>

<h2>Change Password</h2>

<?php if ($message): ?>
    <div class="alert alert-info"><?php echo $message; ?></div>
<?php endif; ?>

<?php if ($show_form): ?>
    <form action="" method="POST">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <div class="form-group">
            <label for="password">New Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="password_confirm">Confirm New Password</label>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
        </div>
        <button type="submit" class="btn btn-primary">Change Password</button>
    </form>
<?php endif; ?>

<?php include_once '../templates/footer.php'; ?>
