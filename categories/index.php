<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

$page_title = "All Categories";
require_once __DIR__ . '/../templates/header.php';

// Fetch all categories to display
$all_categories = [];
$sql_cats = "SELECT name, slug, description, image_url FROM categories ORDER BY name ASC";
$res_cats = mysqli_query($conn, $sql_cats);
if ($res_cats) {
    while($cat_row = mysqli_fetch_assoc($res_cats)) {
        $all_categories[] = $cat_row;
    }
}
?>

<div class="container py-5">
    <h1 class="text-center mb-4"><?php echo $page_title; ?></h1>

    <?php if (!empty($all_categories)): ?>
        <div class="list-group">
            <?php foreach ($all_categories as $category): ?>
                <a href="<?php echo SITE_URL; ?>category/?slug=<?php echo htmlspecialchars($category['slug']); ?>" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1"><?php echo htmlspecialchars($category['name']); ?></h5>
                        <?php if(!empty($category['image_url'])): ?>
                            <img src="<?php echo SITE_URL . 'uploads/categories/' . htmlspecialchars($category['image_url']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" style="max-height: 50px; border-radius: 4px;">
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($category['description'])): ?>
                        <p class="mb-1"><small><?php echo htmlspecialchars(substr($category['description'], 0, 150)) . (strlen($category['description']) > 150 ? '...' : ''); ?></small></p>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-center">No categories found.</p>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
