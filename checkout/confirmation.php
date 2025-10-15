<?php
session_start();
require_once '../config.php';
require_once '../includes/db.php';
require_once '../templates/header.php';

if (!isset($_SESSION['order_id'])) {
    header('Location: ' . SITE_URL);
    exit();
}

$order_id = $_SESSION['order_id'];
unset($_SESSION['order_id']); // Unset session variable after use

?>

<div class="container my-5 text-center">
    <h2>Order Confirmation & Payment</h2>
    <p class="lead">Thank you for your order! Your Order ID is <strong>#<?php echo $order_id; ?></strong>.</p>
    <hr>

    <h4>Complete Your Payment via Google Pay (UPI)</h4>
    <p>Please use your favorite UPI app to complete the payment.</p>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5>Option A: Scan QR Code</h5>
                    <img src="https://via.placeholder.com/250x250.png?text=Your+GPay+QR+Code" alt="Google Pay QR Code" class="img-fluid mb-3">

                    <hr>

                    <h5>Option B: Pay via UPI ID</h5>
                    <p>Send the payment to the following UPI ID:</p>
                    <p class="h4" id="upiId">your-business-upi-id@oksbi</p>
                    <button class="btn btn-sm btn-secondary" onclick="copyToClipboard('#upiId')">Copy UPI ID</button>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <h4>Step 3: Confirm Your Payment</h4>
    <p>After making the payment, please enter the <strong>Transaction ID</strong> below to help us verify your payment.</p>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <form action="submit_transaction.php" method="POST">
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                <div class="form-group">
                    <label for="transaction_id">UPI Transaction ID</label>
                    <input type="text" class="form-control" id="transaction_id" name="transaction_id" placeholder="Enter the 12-digit transaction ID" required>
                </div>
                <button type="submit" class="btn btn-primary">Submit Transaction ID</button>
            </form>
        </div>
    </div>
</div>

<script>
function copyToClipboard(element) {
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val($(element).text()).select();
    document.execCommand("copy");
    $temp.remove();
    alert("UPI ID copied to clipboard!");
}
</script>

<?php require_once '../templates/footer.php'; ?>