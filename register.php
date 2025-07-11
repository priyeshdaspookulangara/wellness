<?php
session_start(); // Start session to manage user login state and messages
require_once __DIR__ . '/includes/db.php'; // For database connection and escape_string

$page_title = "User Registration";
$errors = [];
$success_message = '';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: account.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic Validation
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Username must be between 3 and 50 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores.";
    }


    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check if username or email already exists (if no other validation errors)
    if (empty($errors)) {
        $escaped_username = escape_string($username);
        $escaped_email = escape_string($email);

        $sql_check = "SELECT id FROM users WHERE username = '$escaped_username' OR email = '$escaped_email'";
        $result_check = mysqli_query($conn, $sql_check);
        if ($result_check && mysqli_num_rows($result_check) > 0) {
            $existing_user = mysqli_fetch_assoc($result_check);
            if ($existing_user['username'] == $username) {
                 $errors[] = "Username already taken. Please choose another.";
            }
            if ($existing_user['email'] == $email) {
                 $errors[] = "Email address already registered. Please try to login.";
            }
        }
    }

    if (empty($errors)) {
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert user into database
        $sql_insert = "INSERT INTO users (username, email, password_hash, created_at, updated_at)
                       VALUES ('$escaped_username', '$escaped_email', '$password_hash', NOW(), NOW())";

        if (mysqli_query($conn, $sql_insert)) {
            $success_message = "Registration successful! You can now login.";
            // Optionally, log the user in directly
            // $_SESSION['user_id'] = mysqli_insert_id($conn);
            // $_SESSION['username'] = $username;
            // header("Location: account.php");
            // exit;
        } else {
            $errors[] = "Registration failed. Please try again later. Error: " . mysqli_error($conn);
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

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
                <p><a href="login.php">Click here to Login</a></p>
            </div>
        <?php else: // Hide form if registration is successful ?>
            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
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
            <p class="text-center mt-3">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
