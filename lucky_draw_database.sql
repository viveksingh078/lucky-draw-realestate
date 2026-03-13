-- =====================================================
-- LUCKY DRAW SYSTEM - DATABASE SETUP
-- Run this SQL script in your database (phpMyAdmin/MySQL)
-- =====================================================

-- 1. CREATE LUCKY DRAWS TABLE
CREATE TABLE `lucky_draws` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Draw name like "January Weekly Draw #1"',
  `draw_type` enum('weekly','monthly') NOT NULL,
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `property_value` decimal(15,2) NOT NULL COMMENT 'Property market value',
  `entry_fee` decimal(10,2) NOT NULL COMMENT 'Fee to join draw',
  `start_date` datetime NOT NULL COMMENT 'When participation starts',
  `end_date` datetime NOT NULL COMMENT 'Participation deadline',
  `draw_date` datetime NOT NULL COMMENT 'When winner is announced',
  `status` enum('upcoming','active','completed','cancelled') NOT NULL DEFAULT 'upcoming',
  `total_pool` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Total money collected',
  `winner_id` bigint(20) UNSIGNED NULL COMMENT 'Winner ID (real user or dummy)',
  `winner_type` enum('real','dummy') NULL COMMENT 'Real user or dummy winner',
  `is_profit` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Did we make profit?',
  `profit_loss_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `description` text NULL,
  `settings` json NULL COMMENT 'Extra settings',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status_draw_date` (`status`, `draw_date`),
  KEY `idx_draw_type_status` (`draw_type`, `status`),
  KEY `fk_lucky_draws_property` (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. CREATE LUCKY DRAW PARTICIPANTS TABLE
CREATE TABLE `lucky_draw_participants` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `draw_id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `entry_fee_paid` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','paid','refunded') NOT NULL DEFAULT 'pending',
  `payment_utr` varchar(255) NULL COMMENT 'UTR number for payment',
  `payment_screenshot` varchar(255) NULL COMMENT 'Payment proof image',
  `joined_at` datetime NOT NULL,
  `is_winner` tinyint(1) NOT NULL DEFAULT '0',
  `credit_given` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Credit if lost',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_draw_account` (`draw_id`, `account_id`),
  KEY `idx_draw_payment_status` (`draw_id`, `payment_status`),
  KEY `fk_participants_account` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. CREATE DUMMY WINNERS TABLE
CREATE TABLE `dummy_winners` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `city` varchar(100) NULL,
  `bio` text NULL,
  `avatar` varchar(255) NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. ADD LUCKY DRAW FIELDS TO ACCOUNTS TABLE
ALTER TABLE `re_accounts` 
ADD COLUMN `available_credits` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Credits from lost draws' AFTER `admin_notes`,
ADD COLUMN `total_draws_joined` int(11) NOT NULL DEFAULT '0' AFTER `available_credits`,
ADD COLUMN `total_draws_won` int(11) NOT NULL DEFAULT '0' AFTER `total_draws_joined`,
ADD COLUMN `last_draw_joined` datetime NULL AFTER `total_draws_won`;

-- 5. INSERT SAMPLE DUMMY WINNERS
INSERT INTO `dummy_winners` (`name`, `email`, `phone`, `city`, `bio`, `created_at`, `updated_at`) VALUES
('Rajesh Kumar', 'rajesh.winner@example.com', '9876543210', 'Mumbai', 'Happy to win my dream home through AADS Property Portal!', NOW(), NOW()),
('Priya Sharma', 'priya.winner@example.com', '9876543211', 'Delhi', 'Grateful for this amazing opportunity with AADS!', NOW(), NOW()),
('Amit Singh', 'amit.winner@example.com', '9876543212', 'Bangalore', 'Dreams do come true with AADS Property Portal!', NOW(), NOW()),
('Sunita Patel', 'sunita.winner@example.com', '9876543213', 'Ahmedabad', 'Thank you AADS for making homeownership possible!', NOW(), NOW()),
('Vikash Gupta', 'vikash.winner@example.com', '9876543214', 'Pune', 'Blessed to be chosen as the lucky winner!', NOW(), NOW());

-- 6. ADD FOREIGN KEY CONSTRAINTS (Optional - for data integrity)
-- ALTER TABLE `lucky_draws` ADD CONSTRAINT `fk_lucky_draws_property` FOREIGN KEY (`property_id`) REFERENCES `re_properties` (`id`) ON DELETE CASCADE;
-- ALTER TABLE `lucky_draw_participants` ADD CONSTRAINT `fk_participants_draw` FOREIGN KEY (`draw_id`) REFERENCES `lucky_draws` (`id`) ON DELETE CASCADE;
-- ALTER TABLE `lucky_draw_participants` ADD CONSTRAINT `fk_participants_account` FOREIGN KEY (`account_id`) REFERENCES `re_accounts` (`id`) ON DELETE CASCADE;

-- =====================================================
-- SETUP COMPLETE! 
-- Now you can use the Lucky Draw System
-- =====================================================

-- SAMPLE DATA (Optional - for testing)
-- INSERT INTO `lucky_draws` (`name`, `draw_type`, `property_id`, `property_value`, `entry_fee`, `start_date`, `end_date`, `draw_date`, `status`, `description`, `created_at`, `updated_at`) VALUES
-- ('January Weekly Draw #1', 'weekly', 1, 5000000.00, 999.00, '2024-01-22 00:00:00', '2024-01-28 23:59:59', '2024-01-29 12:00:00', 'active', 'Win a beautiful 2BHK apartment in Mumbai!', NOW(), NOW());

SELECT 'Lucky Draw Database Setup Complete!' as Status;