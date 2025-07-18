<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

// If the user is already logged in, redirect them to the account page
if (isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/account');
    exit;
}

$pageTitle = 'Register';
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = 'Please fill in all fields.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Invalid email format.';
    } else {
        try {
            // Check if email or username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            if ($stmt->fetch()) {
                $error_message = 'Email or username is already taken.';
            } else {
                // Hash the password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, created_at) VALUES (?, ?, ?, NOW())");
                if ($stmt->execute([$username, $email, $password_hash])) {
                    // Automatically log in the user
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    $_SESSION['user_email'] = $email;
                    session_regenerate_id(true);

                    // Redirect to account page
                    header('Location: ' . SITE_URL . '/account');
                    exit;
                } else {
                    $error_message = 'An error occurred during registration. Please try again.';
                }
            }
        } catch (PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            $error_message = 'A database error occurred. Please try again later.';
        }
    }
}

require_once __DIR__ . '/../templates/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2>Register</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                <form action="<?php echo SITE_URL; ?>/register/" method="post">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Register</button>
                </form>
                <div class="mt-3 text-center">
                    <p>Already have an account? <a href="<?php echo SITE_URL; ?>/login/">Login here</a>.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
