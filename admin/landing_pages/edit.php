<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

// Check for admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . SITE_URL . "/login");
    exit;
}

$page = [
    'page_id' => '',
    'page_name' => '',
    'slug' => '',
    'page_title' => '',
    'hero_heading' => '',
    'hero_subheading' => '',
    'main_content_html' => '',
    'button_text' => 'Apply Now',
    'button_target_url' => SITE_URL . '/apply',
    'is_active' => true
];

if (isset($_GET['id'])) {
    $db = db_connect();
    $stmt = $db->prepare("SELECT * FROM landing_pages WHERE page_id = :id");
    $stmt->bindValue(':id', $_GET['id']);
    $stmt->execute();
    $page = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1><?php echo isset($_GET['id']) ? 'Edit' : 'Create'; ?> Landing Page</h1>

            <form action="save.php" method="post">
                <input type="hidden" name="page_id" value="<?php echo $page['page_id']; ?>">

                <div class="form-group">
                    <label for="page_name">Page Name</label>
                    <input type="text" class="form-control" id="page_name" name="page_name" value="<?php echo htmlspecialchars($page['page_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="slug">Slug (URL)</label>
                    <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($page['slug']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="page_title">Page Title (HTML)</label>
                    <input type="text" class="form-control" id="page_title" name="page_title" value="<?php echo htmlspecialchars($page['page_title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="hero_heading">Hero Heading</label>
                    <input type="text" class="form-control" id="hero_heading" name="hero_heading" value="<?php echo htmlspecialchars($page['hero_heading']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="hero_subheading">Hero Subheading</label>
                    <textarea class="form-control" id="hero_subheading" name="hero_subheading" rows="3"><?php echo htmlspecialchars($page['hero_subheading']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="main_content_html">Main Content (HTML)</label>
                    <textarea class="form-control" id="main_content_html" name="main_content_html" rows="10"><?php echo htmlspecialchars($page['main_content_html']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="button_text">Button Text</label>
                    <input type="text" class="form-control" id="button_text" name="button_text" value="<?php echo htmlspecialchars($page['button_text']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="button_target_url">Button Target URL</label>
                    <input type="text" class="form-control" id="button_target_url" name="button_target_url" value="<?php echo htmlspecialchars($page['button_target_url']); ?>" required>
                </div>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" <?php echo $page['is_active'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Save Landing Page</button>
            </form>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
