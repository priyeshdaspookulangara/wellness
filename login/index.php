<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

// If the user is already logged in, redirect them to the account page
if (isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/account');
    exit;
}

$pageTitle = 'Login';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_message = 'Please fill in both fields.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, password_hash, is_active FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['is_active']) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $email; // Store email for convenience

                    // Regenerate session ID to prevent session fixation
                    session_regenerate_id(true);

                    // Redirect to a protected page, e.g., their account dashboard
                    header('Location: ' . SITE_URL . '/account');
                    exit;
                } else {
                    $error_message = 'Your account is not active. Please contact support.';
                }
            } else {
                $error_message = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            $error_message = 'An error occurred during login. Please try again.';
        }
    }
}

require_once __DIR__ . '/../templates/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h2>Login</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                <form action="<?php echo SITE_URL; ?>/login/" method="post">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </form>
                <div class="mt-3 text-center">
                    <p>Don't have an account? <a href="<?php echo SITE_URL; ?>/register/">Register here</a>.</p>
                    <p><a href="<?php echo SITE_URL; ?>/forgot_password/">Forgot your password?</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
