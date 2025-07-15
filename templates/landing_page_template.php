<?php
// --- UTM Tracking Logic ---
$utm_params = [];
$allowed_utm_keys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];

foreach ($allowed_utm_keys as $key) {
    if (isset($_GET[$key])) {
        $utm_params[$key] = htmlspecialchars($_GET[$key]);
    }
}

session_start();
if (!isset($_SESSION['utm_data'])) {
    $_SESSION['utm_data'] = [];
}
$_SESSION['utm_data'] = array_merge($_SESSION['utm_data'], $utm_params);

$buy_now_url = '[BUTTON_TARGET_URL_PLACEHOLDER]';
$utm_query_string = http_build_query($utm_params);
if (!empty($utm_query_string)) {
    $buy_now_url .= (strpos($buy_now_url, '?') === false ? '?' : '&') . $utm_query_string;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[PAGE_TITLE_PLACEHOLDER]</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; color: #333; }
        .container { max-width: 960px; margin: 50px auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        h1 { color: #0056b3; margin-bottom: 10px; }
        p { line-height: 1.6; margin-bottom: 20px; }
        .action-button {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.2em;
            margin-top: 30px;
            transition: background-color 0.3s ease;
        }
        .action-button:hover { background-color: #0056b3; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const utm_data_js = {};
            <?php foreach ($allowed_utm_keys as $key): ?>
                if (urlParams.has('<?php echo $key; ?>')) {
                    utm_data_js['<?php echo $key; ?>'] = urlParams.get('<?php echo $key; ?>');
                }
            <?php endforeach; ?>

            if (typeof trackEvent === 'function') {
                trackEvent('landing_page_view', window.location.pathname, {
                    'landing_page_slug': '[SLUG_PLACEHOLDER]',
                    'utm_data': utm_data_js
                });
            }
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>[HERO_HEADING_PLACEHOLDER]</h1>
        <p>[HERO_SUBHEADING_PLACEHOLDER]</p>

        <div class="main-content">
            [MAIN_CONTENT_HTML_PLACEHOLDER]
        </div>

        <a href="<?php echo $buy_now_url; ?>" class="action-button">[BUTTON_TEXT_PLACEHOLDER]</a>
    </div>
</body>
</html>
