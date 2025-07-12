<?php
// Set HTTP response status code to 404
http_response_code(404);

require_once __DIR__ . '/config.php'; // For SITE_URL, SITE_NAME
$page_title = "Page Not Found (404)";
require_once __DIR__ . '/templates/header.php';
?>

<div class="container text-center py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="display-1 text-danger">404</h1>
            <h2 class="mb-4">Oops! Page Not Found.</h2>
            <p class="lead mb-4">
                Sorry, the page you are looking for does not exist, might have been removed, or is temporarily unavailable.
            </p>
            <p>You can try the following:
                <ul class="list-unstyled">
                    <li>Double-check the URL for typos.</li>
                    <li><a href="<?php echo SITE_URL; ?>">Return to the Homepage</a></li>
                    <li><a href="<?php echo SITE_URL; ?>contact/">Contact Us</a> if you believe this is an error.</li>
                </ul>
            </p>
            <p class="text-muted">
                Request URL: <?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>
            </p>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/templates/footer.php';
?>
