<?php
session_start(); // Not strictly necessary for this page unless using session messages
require_once __DIR__ . '/../config.php'; // Corrected
require_once __DIR__ . '/../includes/db.php'; // Corrected

$page_title = "About Us";

// Fetch About Us content from settings table
$about_us_content = '';
$sql_about = "SELECT setting_value FROM settings WHERE setting_key = 'about_us_content' LIMIT 1";
$result_about = mysqli_query($conn, $sql_about);
if ($result_about && mysqli_num_rows($result_about) > 0) {
    $row = mysqli_fetch_assoc($result_about);
    $about_us_content = $row['setting_value'];
} else {
    // Fallback content if not found in DB
    $about_us_content = "<p>Welcome to " . SITE_NAME . "! We are passionate about wellness and dedicated to providing high-quality products to support your journey to a healthier life. Our mission is to build trust through education and transparency, offering items that deliver real benefits.</p><p>Learn more about our philosophy and commitment soon!</p>";
}

require_once __DIR__ . '/../templates/header.php'; // Corrected
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <h1 class="text-center mb-4"><?php echo $page_title; ?></h1>

            <div class="card">
                <div class="card-body">
                    <?php echo nl2br(htmlspecialchars_decode($about_us_content)); // Use htmlspecialchars_decode if admin saves HTML, otherwise just nl2br(htmlspecialchars()) ?>
                    <?php // If admin saves HTML, ensure it's properly sanitized before saving and here before display.
                          // For plain text from DB, nl2br(htmlspecialchars($about_us_content)) is safer.
                          // Given the current setup, let's assume admin saves plain text or very basic HTML that's safe.
                          // A true WYSIWYG would require more robust sanitization.
                          // For now, assuming the content in DB is plain text or safe HTML snippets.
                    ?>
                </div>
            </div>

            <div class="mt-4 p-3 bg-light rounded">
                <h4>Our Commitment</h4>
                <p>At <?php echo SITE_NAME; ?>, we are committed to:</p>
                <ul>
                    <li><strong>Quality:</strong> Sourcing and offering only the highest quality wellness products.</li>
                    <li><strong>Trust:</strong> Building long-lasting relationships with our customers through transparency and honesty.</li>
                    <li><strong>Education:</strong> Providing clear information about how our products work and their potential benefits.</li>
                    <li><strong>Well-being:</strong> Supporting your overall health and wellness journey.</li>
                </ul>
            </div>

            <div class="mt-4 text-center">
                 <p class="text-muted small">
                    Disclaimer: Information and products on this site are not intended to diagnose, treat, cure, or prevent any disease. Always consult with a healthcare professional for any health concerns or before starting a new treatment.
                </p>
            </div>

        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php'; // Corrected
?>
