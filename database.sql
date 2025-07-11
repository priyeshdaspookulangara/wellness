-- Wellness E-commerce Platform Database Schema
-- Target Server Type    : MySQL
-- Target Server Version : 50700 (or higher, InnoDB assumed)
-- Encoding              : utf8mb4

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `address_street` varchar(255) DEFAULT NULL,
  `address_city` varchar(100) DEFAULT NULL,
  `address_state` varchar(100) DEFAULT NULL,
  `address_zip` varchar(20) DEFAULT NULL,
  `address_country` varchar(100) DEFAULT NULL,
  `phone_number` varchar(30) DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_email` (`email`),
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for categories
-- ----------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `slug` varchar(170) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for products
-- ----------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(11) unsigned NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `description` text NOT NULL,
  `how_it_works` text DEFAULT NULL,
  `health_benefits_text` text DEFAULT NULL, -- For listing ailments/benefits
  `gauss_strength` varchar(50) DEFAULT NULL, -- e.g., "1000-1500 Gauss" or specific value
  `material_quality_design` text DEFAULT NULL,
  `usage_guide_text` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image_url_main` varchar(255) DEFAULT NULL, -- Main product image
  `image_gallery_urls` text DEFAULT NULL, -- JSON array or comma-separated list of other image URLs
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_on_sale` tinyint(1) NOT NULL DEFAULT 0,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `views_count` int(11) unsigned NOT NULL DEFAULT 0,
  `added_to_cart_count` int(11) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_category_id` (`category_id`),
  CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE -- Or SET NULL if preferred
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for orders
-- ----------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL, -- Can be NULL for guest checkouts
  `order_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `shipping_first_name` varchar(100) NOT NULL,
  `shipping_last_name` varchar(100) NOT NULL,
  `shipping_address_street` varchar(255) NOT NULL,
  `shipping_address_city` varchar(100) NOT NULL,
  `shipping_address_state` varchar(100) NOT NULL,
  `shipping_address_zip` varchar(20) NOT NULL,
  `shipping_address_country` varchar(100) NOT NULL,
  `shipping_method` varchar(100) DEFAULT NULL, -- e.g., "Flat Rate"
  `shipping_cost` decimal(10,2) DEFAULT 0.00,
  `payment_method` varchar(100) DEFAULT NULL, -- e.g., "Credit Card (Placeholder)"
  `payment_status` enum('pending','paid','failed') NOT NULL DEFAULT 'pending',
  `transaction_id` varchar(255) DEFAULT NULL, -- For dummy payment reference
  `customer_email` varchar(150) NOT NULL, -- For guest or registered user email at time of order
  `customer_phone` varchar(30) DEFAULT NULL,
  `notes` text DEFAULT NULL, -- Customer notes
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for order_items
-- ----------------------------
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_purchase` decimal(10,2) NOT NULL, -- Price of the product when the order was placed
  `product_name_at_purchase` varchar(200) NOT NULL, -- Store product name for historical reference
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_product_id` (`product_id`),
  CONSTRAINT `fk_orderitem_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_orderitem_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE -- Or SET NULL if product deletion policy requires
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for reviews
-- ----------------------------
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `rating` tinyint(1) NOT NULL, -- e.g., 1 to 5
  `comment` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_review_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_review_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for blog_posts
-- ----------------------------
DROP TABLE IF EXISTS `blog_posts`;
CREATE TABLE `blog_posts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL, -- Author (admin user)
  `title` varchar(255) NOT NULL,
  `slug` varchar(275) NOT NULL,
  `content` longtext NOT NULL,
  `excerpt` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_blogpost_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for settings
-- ----------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Optional: Table for page content (About Us, etc. if not in settings)
-- ----------------------------
-- DROP TABLE IF EXISTS `pages`;
-- CREATE TABLE `pages` (
--   `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
--   `title` varchar(255) NOT NULL,
--   `slug` varchar(275) NOT NULL,
--   `content` longtext NOT NULL,
--   `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
--   `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--   PRIMARY KEY (`id`),
--   UNIQUE KEY `uk_slug` (`slug`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ----------------------------
-- Initial Data (Optional - for settings or default admin)
-- ----------------------------
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('site_name', 'Wellness Wonders', 'The main name of the website'),
('site_tagline', 'Your Journey to Wellness', 'Website tagline displayed in various places'),
('admin_email', 'admin@example.com', 'Default admin email for notifications'),
('contact_email', 'contact@example.com', 'Public contact email address'),
('contact_phone', '1-800-WELLNESS', 'Public contact phone number'),
('footer_disclaimer', 'Disclaimer: Products sold on this website are not intended to diagnose, treat, cure, or prevent any disease. Consult with a healthcare professional before using any wellness products.', 'Legal disclaimer shown in the footer'),
('about_us_content', 'Welcome to Wellness Wonders! We are dedicated to providing high-quality wellness products...', 'Content for the About Us page'),
('testimonials_page_header', 'What Our Customers Say', 'Header for the main testimonials page');

-- Example: Add a default admin user (password is 'adminpassword' - CHANGE THIS IMMEDIATELY)
-- For security, it's better to have a registration process or a script to create the first admin.
-- This is just for quick setup.
-- INSERT INTO `users` (`username`, `email`, `password_hash`, `first_name`, `last_name`, `is_admin`) VALUES
-- ('admin', 'admin@wellness.com', '$2y$10$xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'Admin', 'User', 1);
-- Replace xxxx with a real hash generated by password_hash('adminpassword', PASSWORD_DEFAULT) in PHP.
-- Example hash for 'adminpassword': $2y$10$9Q2oD1Q5gJ7kL3s7B6cE0u.jZ1nF8gH3kL9sW4xP2dR7vY6zQ5.uC (This is just an example, generate your own)

SET FOREIGN_KEY_CHECKS = 1;
