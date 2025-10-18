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
    $is_affiliate_request = isset($_POST['is_affiliate']) && $_POST['is_affiliate'] == '1';

    if (empty($name)) {
        $errors[] = "Please enter your name.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if email already exists
    $stmt_check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt_check_email->bind_param("s", $email);
    $stmt_check_email->execute();
    $result_check_email = $stmt_check_email->get_result();
    if ($result_check_email->num_rows > 0) {
        $errors[] = "An account with this email address already exists.";
    }

    $affiliate_id = null;
    if (!empty($referral_code)) {
        $stmt_check_code = $conn->prepare("SELECT id FROM affiliates WHERE referral_code = ? AND status = 'active'");
        $stmt_check_code->bind_param("s", $referral_code);
        $stmt_check_code->execute();
        $result_check_code = $stmt_check_code->get_result();
        if ($result_check_code->num_rows > 0) {
            $affiliate = $result_check_code->fetch_assoc();
            $affiliate_id = $affiliate['id'];
        } else {
            $errors[] = "The provided referral code is not valid.";
        }
    }

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt_insert_user = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt_insert_user->bind_param("sss", $name, $email, $hashed_password);
            $stmt_insert_user->execute();
            $new_user_id = $conn->insert_id;

            if ($affiliate_id) {
                $stmt_insert_referral = $conn->prepare("INSERT INTO referrals (affiliate_id, referred_user_id) VALUES (?, ?)");
                $stmt_insert_referral->bind_param("ii", $affiliate_id, $new_user_id);
                $stmt_insert_referral->execute();
            }

            // Check if user wants to register as an affiliate
            if ($is_affiliate_request) {
                $new_referral_code = 'ref_' . uniqid() . $new_user_id;
                $stmt_insert_affiliate = $conn->prepare("INSERT INTO affiliates (user_id, referral_code, status) VALUES (?, ?, 'inactive')");
                $stmt_insert_affiliate->bind_param("is", $new_user_id, $new_referral_code);
                $stmt_insert_affiliate->execute();
                $_SESSION['message'] = "Registration successful! Your affiliate application has been submitted for review.";
            } else {
                $_SESSION['message'] = "Registration successful! You can now log in.";
            }

            $conn->commit();

            // Clear the cookie after successful registration
            setcookie('affiliate_ref', '', time() - 3600, "/");

            $_SESSION['message_type'] = "success";
            header("location: " . SITE_URL . "login/");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Something went wrong. Please try again later.";
        }
    }
    $conn->close();
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
                        <div class="form-group form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="is_affiliate" name="is_affiliate" value="1">
                            <label class="form-check-label" for="is_affiliate">I want to register as an affiliate</label>
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