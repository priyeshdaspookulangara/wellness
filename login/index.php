<?php
session_start();
include_once '../config.php';
include_once '../includes/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if user exists
    $stmt = $db->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {
        // Password is correct, start a session
        $_SESSION['user_id'] = $user['id'];
        header("Location: " . SITE_URL . "/account");
        exit();
    } else {
        $message = 'Invalid email or password.';
    }
}

$pageTitle = 'Login';
include_once '../templates/header.php';
?>

<h2>Login</h2>

<?php if ($message): ?>
    <div class="alert alert-danger"><?php echo $message; ?></div>
<?php endif; ?>

<form action="" method="POST">
    <div class="form-group">
        <label for="email">Email address</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary">Login</button>
    <a href="<?php echo SITE_URL; ?>/forgot_password" class="btn btn-link">Forgot Password?</a>
</form>

<?php include_once '../templates/footer.php'; ?>
