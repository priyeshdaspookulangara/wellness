<?php
// This is a conceptual migration file.
// In a real framework, this would use the framework's migration commands.
// Here, it serves as a record of the necessary SQL schema change.

$sql = "
CREATE TABLE IF NOT EXISTS `product_media` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `media_type` ENUM('image', 'video') NOT NULL,
  `path_or_url` VARCHAR(2083) NOT NULL, -- 2083 is a common max length for URLs
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// To apply this migration, you would run this SQL against your database.
// For example, using a command-line client:
// mysql -u your_db_user -p your_db_password your_db_name < database/migrations/create_product_media_table.php
// (Assuming the SQL is saved in a runnable format)

// Note: The above SQL is for documentation. The presence of this file
// indicates that this schema change is part of the feature.
?>
