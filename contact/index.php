<?php
session_start();
require_once __DIR__ . '/../config.php'; // Corrected
require_once __DIR__ . '/../includes/db.php'; // Corrected

$page_title = "Contact Us";

$form_data = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data['name'] = trim($_POST['name'] ?? '');
    $form_data['email'] = trim($_POST['email'] ?? '');
    $form_data['subject'] = trim($_POST['subject'] ?? 'General Inquiry');
    $form_data['message'] = trim($_POST['message'] ?? '');

    // Validation
    if (empty($form_data['name'])) {
        $errors[] = "Your name is required.";
    }
    if (empty($form_data['email']) || !filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email address is required.";
    }
    if (empty($form_data['subject'])) {
        $errors[] = "Subject is required.";
    }
    if (empty($form_data['message'])) {
        $errors[] = "Your message cannot be empty.";
    }
    if (strlen($form_data['message']) > 5000) {
        $errors[] = "Your message is too long (max 5000 characters).";
    }

    if (empty($errors)) {
        // Send email
        $to = ADMIN_EMAIL; // Defined in config.php
        $email_subject = "Contact Form Submission: " . $form_data['subject'];

        $email_body = "You have received a new message from your website contact form.\n\n";
        $email_body .= "Here are the details:\n";
        $email_body .= "Name: " . $form_data['name'] . "\n";
        $email_body .= "Email: " . $form_data['email'] . "\n";
        $email_body .= "Subject: " . $form_data['subject'] . "\n";
        $email_body .= "Message:\n" . $form_data['message'] . "\n";

        $headers = "From: " . $form_data['name'] . " <" . $form_data['email'] . ">\r\n";
        $headers .= "Reply-To: " . $form_data['email'] . "\r\n";
        // $headers .= "X-Mailer: PHP/" . phpversion(); // Optional

        // In a real application, using a library like PHPMailer is recommended for reliability and features.
        // The mail() function's success is server-dependent.
        if (mail($to, $email_subject, $email_body, $headers)) {
            $success_message = "Thank you for contacting us! Your message has been sent successfully. We'll get back to you shortly.";
            // Clear form data after successful submission
            $form_data = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
        } else {
            // This error message might not be very helpful if mail() fails due to server config.
            $errors[] = "Sorry, there was an error sending your message. Please try again later or contact us directly via " . ADMIN_EMAIL;
        }
    }
}

require_once __DIR__ . '/../templates/header.php'; // Corrected
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="text-center mb-4"><?php echo $page_title; ?></h1>
            <p class="text-center text-muted mb-5">
                Have questions, feedback, or need assistance? Fill out the form below, and we'll get back to you as soon as possible.
                You can also reach us via <?php echo ADMIN_EMAIL; ?>.
            </p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong>Please correct the following errors:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>contact/" method="POST"> <!-- Updated form action -->
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($form_data['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Your Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <select class="form-control" id="subject" name="subject">
                                <option value="General Inquiry" <?php echo ($form_data['subject'] == 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                                <option value="Product Question" <?php echo ($form_data['subject'] == 'Product Question') ? 'selected' : ''; ?>>Product Question</option>
                                <option value="Order Support" <?php echo ($form_data['subject'] == 'Order Support') ? 'selected' : ''; ?>>Order Support</option>
                                <option value="Consultation Request" <?php echo ($form_data['subject'] == 'Consultation Request') ? 'selected' : ''; ?>>Consultation Request</option>
                                <option value="Feedback" <?php echo ($form_data['subject'] == 'Feedback') ? 'selected' : ''; ?>>Feedback</option>
                                <option value="Other" <?php echo ($form_data['subject'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="message">Your Message</label>
                            <textarea class="form-control" id="message" name="message" rows="6" required><?php echo htmlspecialchars($form_data['message']); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4">
                <h4>Our Office (Example)</h4>
                <p>
                    123 Wellness Drive<br>
                    Serenity City, ST 90210<br>
                    United States
                </p>
                <p><em>Please note: Office visits by appointment only.</em></p>
            </div>

        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php'; // Corrected
?>
