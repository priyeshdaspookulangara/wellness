<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

// Admin authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $_SESSION['admin_error'] = "Access denied.";
    header("Location: " . SITE_URL . "/login.php");
    exit;
}

$page_param = $_GET['page'] ?? 'about'; // Default to 'about'
$content_key = '';
$content_title = '';

switch ($page_param) {
    case 'about':
        $content_key = 'about_us_content';
        $content_title = 'About Us Page Content';
        break;
    case 'testimonials_header': // Example for another piece of content
        $content_key = 'testimonials_page_header';
        $content_title = 'Testimonials Page Header Text';
        break;
    // Add more cases for other manageable content areas stored in 'settings'
    default:
        $_SESSION['error_message'] = "Invalid content page specified.";
        header("Location: index.php"); // Redirect to admin dashboard
        exit;
}

$page_title = "Edit " . $content_title;
require_once 'includes/header.php';

$current_content = '';
$sql_get = "SELECT setting_value FROM settings WHERE setting_key = '" . escape_string($content_key) . "' LIMIT 1";
$result_get = mysqli_query($conn, $sql_get);
if ($result_get && mysqli_num_rows($result_get) > 0) {
    $row = mysqli_fetch_assoc($result_get);
    $current_content = $row['setting_value'];
}

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_content = $_POST['content_value'] ?? ''; // Using direct POST value, consider sanitization if allowing HTML

    // For simple text, escape_string is fine. If allowing HTML, a proper HTML sanitizer is needed.
    // For this project, we'll assume plain text or admin-trusted simple HTML.
    $content_esc = escape_string($new_content);

    // Check if setting exists, then update or insert
    $sql_check = "SELECT id FROM settings WHERE setting_key = '" . escape_string($content_key) . "'";
    $res_check = mysqli_query($conn, $sql_check);

    if ($res_check && mysqli_num_rows($res_check) > 0) {
        // Update existing setting
        $sql_update = "UPDATE settings SET setting_value = '$content_esc', updated_at = NOW() WHERE setting_key = '" . escape_string($content_key) . "'";
        if (mysqli_query($conn, $sql_update)) {
            $success_message = $content_title . " updated successfully.";
            $current_content = $new_content; // Update displayed content
        } else {
            $errors[] = "Failed to update content: " . mysqli_error($conn);
        }
    } else {
        // Insert new setting
        $sql_insert = "INSERT INTO settings (setting_key, setting_value, description, created_at, updated_at)
                       VALUES ('" . escape_string($content_key) . "', '$content_esc', '$content_title', NOW(), NOW())";
        if (mysqli_query($conn, $sql_insert)) {
            $success_message = $content_title . " saved successfully.";
            $current_content = $new_content;
        } else {
            $errors[] = "Failed to save content: " . mysqli_error($conn);
        }
    }
}

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $page_title; ?></h1>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($success_message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<form action="content_edit.php?page=<?php echo htmlspecialchars($page_param); ?>" method="POST">
    <div class="card">
        <div class="card-header"><?php echo htmlspecialchars($content_title); ?></div>
        <div class="card-body">
            <div class="form-group">
                <label for="content_value">Content:</label>
                <textarea class="form-control" id="content_value" name="content_value" rows="15"><?php echo htmlspecialchars($current_content); ?></textarea>
                <small class="form-text text-muted">
                    Enter the content for this section. Basic HTML is allowed if you know what you're doing, but ensure it's secure.
                    For multi-line text, new lines will be preserved.
                </small>
            </div>
            <button type="submit" class="btn btn-primary">Save Content</button>
        </div>
    </div>
</form>

<div class="mt-4">
    <h4>Preview:</h4>
    <div class="card">
        <div class="card-body">
            <?php echo nl2br(htmlspecialchars($current_content)); // Simple preview, does not render HTML ?>
        </div>
    </div>
</div>


<?php
require_once 'includes/footer.php';
?>
