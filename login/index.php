<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

$errors = [];
$email = '';

if (isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "account/");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email is required.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT id, username, password_hash, is_admin FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = (bool)$user['is_admin'];

            if ($_SESSION['is_admin']) {
                header("Location: " . SITE_URL . "admin/");
            } else {
                header("Location: " . SITE_URL . "account/");
            }
            exit;
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}

$page_title = "Login";
require_once __DIR__ . '/../templates/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-center">Login</h2>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success">
                            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['admin_error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['admin_error']; unset($_SESSION['admin_error']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <p><?php echo $error; ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <p>Don't have an account? <a href="<?php echo SITE_URL; ?>register/">Register here</a>.</p>
                    <p><a href="<?php echo SITE_URL; ?>forgot_password/">Forgot your password?</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
