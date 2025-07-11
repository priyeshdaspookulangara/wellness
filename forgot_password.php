<?php
session_start();
require_once __DIR__ . '/includes/db.php';

$page_title = "Forgot Password";
$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        $errors[] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } else {
        // Placeholder for password reset logic
        // 1. Check if email exists in the database
        // 2. Generate a unique reset token and expiry
        // 3. Store token in database (associated with user)
        // 4. Send an email to the user with a reset link (e.g., reset_password.php?token=...)
        $success_message = "If an account with that email exists, a password reset link has been sent.";
        // To prevent user enumeration, always show a generic success message.
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

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <form action="forgot_password.php" method="POST">
            <div class="form-group">
                <label for="email">Enter your Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
        </form>
        <p class="text-center mt-3">
            Remembered your password? <a href="login.php">Login here</a>
        </p>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
