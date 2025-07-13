<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

// If user is already logged in, redirect to account page or admin dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['is_admin']) {
        header("Location: " . SITE_URL . "admin/");
    } else {
        header("Location: " . SITE_URL . "account/");
    }
    exit;
}

$page_title = "Login";
$error_message = '';
$success_message = '';

// Check for messages from other pages (e.g., registration)
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['admin_error'])) {
    $error_message = $_SESSION['admin_error'];
    unset($_SESSION['admin_error']);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_or_username = $_POST['email_or_username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email_or_username) || empty($password)) {
        $error_message = "Please enter both email/username and password.";
    } else {
        // Prepare statement to prevent SQL injection
        $sql = "SELECT id, username, email, password, is_admin FROM users WHERE email = ? OR username = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $email_or_username, $email_or_username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($user = mysqli_fetch_assoc($result)) {
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Password is correct, start session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_admin'] = (bool)$user['is_admin'];

                    // Redirect based on role
                    if ($_SESSION['is_admin']) {
                        header("Location: " . SITE_URL . "admin/");
                    } else {
                        header("Location: " . SITE_URL . "account/");
                    }
                    exit;
                } else {
                    $error_message = "Invalid credentials. Please try again.";
                }
            } else {
                $error_message = "Invalid credentials. Please try again.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_message = "Database error. Please try again later.";
        }
    }
}

// Include header
require_once __DIR__ . '/../templates/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-center"><?php echo $page_title; ?></h2>
                </div>
                <div class="card-body">
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                    <?php endif; ?>

                    <form action="<?php echo SITE_URL; ?>login/" method="POST">
                        <div class="mb-3">
                            <label for="email_or_username" class="form-label">Username or Email</label>
                            <input type="text" class="form-control" id="email_or_username" name="email_or_username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <p class="mb-0">Don't have an account? <a href="<?php echo SITE_URL; ?>register/">Register here</a></p>
                    <p class="mb-0"><a href="<?php echo SITE_URL; ?>forgot_password/">Forgot your password?</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once __DIR__ . '/../templates/footer.php';
?>
