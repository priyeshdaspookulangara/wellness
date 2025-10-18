<?php
session_start();
require_once '../config.php';
// The db.php file is empty, so we will create the connection manually.
// In a real application, the connection would be in db.php.

// If user is already logged in, redirect based on their role
if (isset($_SESSION["user_id"])) {
    if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] == 1) {
        header("location: " . SITE_URL . "admin/");
    } else {
        header("location: " . SITE_URL . "account/");
    }
    exit;
}

$email = "";
$password = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($email)) {
        $errors[] = "Please enter your email.";
    }
    if (empty($password)) {
        $errors[] = "Please enter your password.";
    }

    if (empty($errors)) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            // In a real app, log this error instead of dying
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT id, name, email, password, is_admin FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $name, $email_db, $hashed_password, $is_admin);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            // Preserve the guest cart
                            $guest_cart = $_SESSION['cart'] ?? [];

                            // Fetch user's existing cart from a more persistent source if available
                            // For this example, we assume the cart is only in the session.
                            // A more robust implementation would save the cart to the database.

                            // Regenerate session ID for security
                            session_regenerate_id(true);

                            // Store user data in new session
                            $_SESSION["user_id"] = $id;
                            $_SESSION["user_name"] = $name;
                            $_SESSION["is_admin"] = $is_admin;

                            // Merge carts: guest cart items are added to the user's session cart
                            if (!isset($_SESSION['cart'])) {
                                $_SESSION['cart'] = [];
                            }
                            foreach ($guest_cart as $product_id => $quantity) {
                                if (isset($_SESSION['cart'][$product_id])) {
                                    $_SESSION['cart'][$product_id] += $quantity;
                                } else {
                                    $_SESSION['cart'][$product_id] = $quantity;
                                }
                            }

                            // Redirect user based on is_admin flag
                            if ($is_admin == 1) {
                                header("location: " . SITE_URL . "admin/");
                            } else {
                                header("location: " . SITE_URL . "account/");
                            }
                            exit;
                        } else {
                            $errors[] = "The password you entered was not valid.";
                        }
                    }
                } else {
                    $errors[] = "No account found with that email.";
                }
            } else {
                $errors[] = "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
        $conn->close();
    }
}

$pageTitle = "Login";
include_once '../templates/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mt-5">
                <div class="card-header">
                    <h4>Login</h4>
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
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" class="form-control">
                        </div>
                        <div class="form-group">
                            <input type="submit" class="btn btn-primary" value="Login">
                        </div>
                        <p class="mt-3">Don't have an account? <a href="<?php echo SITE_URL; ?>register/">Sign up now</a>.</p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once '../templates/footer.php';
?>
