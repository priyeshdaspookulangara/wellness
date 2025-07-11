<?php
session_start(); // Start session to manage user login state and messages
require_once __DIR__ . '/includes/db.php'; // For database connection and escape_string

$page_title = "User Login";
$errors = [];

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: account.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_or_username = trim($_POST['email_or_username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email_or_username)) {
        $errors[] = "Email or Username is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        $escaped_input = escape_string($email_or_username);

        // Check if input is email or username
        $field_type = filter_var($escaped_input, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $sql = "SELECT id, username, email, password_hash, is_admin FROM users WHERE $field_type = '$escaped_input'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user['password_hash'])) {
                // Password is correct, regenerate session ID for security
                session_regenerate_id(true);

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['is_admin'] = $user['is_admin']; // Store admin status

                // Redirect to account page or admin dashboard if admin
                if ($user['is_admin']) {
                    header("Location: admin/index.php");
                } else {
                    header("Location: account.php");
                }
                exit;
            } else {
                $errors[] = "Invalid email/username or password.";
            }
        } else {
            $errors[] = "Invalid email/username or password.";
        }
    }
}

require_once 'templates/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h1 class="text-center mb-4"><?php echo $page_title; ?></h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">
                Registration successful! Please login.
            </div>
        <?php endif; ?>
         <?php if (isset($_GET['logged_out'])): ?>
            <div class="alert alert-info">
                You have been successfully logged out.
            </div>
        <?php endif; ?>


        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email_or_username">Email or Username</label>
                <input type="text" class="form-control" id="email_or_username" name="email_or_username" value="<?php echo htmlspecialchars($email_or_username ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        <p class="text-center mt-3">
            <a href="forgot_password.php">Forgot Password?</a>
        </p>
        <p class="text-center mt-2">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
