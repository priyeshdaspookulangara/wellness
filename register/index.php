<?php
session_start();
require_once '../config.php';
require_once '../includes/db.php';

// If user is already logged in, redirect
if (isset($_SESSION["user_id"])) {
    header("location: " . SITE_URL . "account/");
    exit;
}

$name = "";
$email = "";
$password = "";
// Check for referral cookie and pre-fill the code
$referral_code = $_COOKIE['affiliate_ref'] ?? '';
$errors = [];
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    // Overwrite with POST data if form was submitted
    $referral_code = trim($_POST["referral_code"]);

    if (empty($name)) {
        $errors[] = "Please enter your name.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    $db = db_connect();

    // Check if email already exists
    $sql_check_email = "SELECT id FROM users WHERE email = ?";
    $stmt_check_email = $db->prepare($sql_check_email);
    $stmt_check_email->execute([$email]);
    if ($stmt_check_email->fetch()) {
        $errors[] = "An account with this email address already exists.";
    }

    $affiliate_id = null;
    if (!empty($referral_code)) {
        $sql_check_code = "SELECT id FROM affiliates WHERE referral_code = ? AND status = 'active'";
        $stmt_check_code = $db->prepare($sql_check_code);
        $stmt_check_code->execute([$referral_code]);
        $affiliate = $stmt_check_code->fetch(PDO::FETCH_ASSOC);
        if ($affiliate) {
            $affiliate_id = $affiliate['id'];
        } else {
            $errors[] = "The provided referral code is not valid.";
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql_insert_user = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
        $stmt_insert_user = $db->prepare($sql_insert_user);

        if ($stmt_insert_user->execute([$name, $email, $hashed_password])) {
            $new_user_id = $db->lastInsertId();

            if ($affiliate_id) {
                $sql_insert_referral = "INSERT INTO referrals (affiliate_id, referred_user_id) VALUES (?, ?)";
                $stmt_insert_referral = $db->prepare($sql_insert_referral);
                $stmt_insert_referral->execute([$affiliate_id, $new_user_id]);
            }

            // Clear the cookie after successful registration
            setcookie('affiliate_ref', '', time() - 3600, "/");

            $_SESSION['message'] = "Registration successful! You can now log in.";
            $_SESSION['message_type'] = "success";
            header("location: " . SITE_URL . "login/");
            exit;
        } else {
            $errors[] = "Something went wrong. Please try again later.";
        }
    }
}

$pageTitle = "Register";
include_once '../templates/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mt-5">
                <div class="card-header">
                    <h4>Register</h4>
                </div>
                <div class="card-body">
                    <?php
                    if (!empty($errors)) {
                        echo '<div class="alert alert-danger">';
                        foreach ($errors as $error) {
                            echo '<p class="mb-0">' . htmlspecialchars($error) . '</p>';
                        }
                        echo '</div>';
                    }
                    ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group mb-3">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="referral_code">Referral Code (Optional)</label>
                            <input type="text" name="referral_code" id="referral_code" class="form-control" value="<?php echo htmlspecialchars($referral_code); ?>">
                        </div>
                        <div class="form-group">
                            <input type="submit" class="btn btn-primary" value="Register">
                        </div>
                        <p class="mt-3">Already have an account? <a href="<?php echo SITE_URL; ?>login/">Login here</a>.</p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once '../templates/footer.php';
?>