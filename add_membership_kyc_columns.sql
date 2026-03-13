-- SQL Query to add Membership and KYC columns to re_accounts table
-- Run this in your database (phpMyAdmin or MySQL client)

-- Add membership and KYC related columns
ALTER TABLE `re_accounts` 
ADD COLUMN `membership_plan_id` INT UNSIGNED NULL AFTER `credits`,
ADD COLUMN `membership_start_date` DATETIME NULL AFTER `membership_plan_id`,
ADD COLUMN `membership_end_date` DATETIME NULL AFTER `membership_start_date`,
ADD COLUMN `membership_status` VARCHAR(20) DEFAULT 'pending' AFTER `membership_end_date`,
ADD COLUMN `pan_card_number` VARCHAR(20) NULL AFTER `membership_status`,
ADD COLUMN `pan_card_file` VARCHAR(255) NULL AFTER `pan_card_number`,
ADD COLUMN `payment_qr_code` VARCHAR(255) NULL AFTER `pan_card_file`,
ADD COLUMN `payment_utr_number` VARCHAR(50) NULL AFTER `payment_qr_code`,
ADD COLUMN `payment_screenshot` VARCHAR(255) NULL AFTER `payment_utr_number`,
ADD COLUMN `account_status` VARCHAR(20) DEFAULT 'pending' AFTER `payment_screenshot`,
ADD COLUMN `admin_notes` TEXT NULL AFTER `account_status`,
ADD COLUMN `approved_at` DATETIME NULL AFTER `admin_notes`,
ADD COLUMN `approved_by` INT UNSIGNED NULL AFTER `approved_at`;

-- Create membership_plans table
CREATE TABLE IF NOT EXISTS `membership_plans` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text NULL,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `duration_days` int(11) NOT NULL DEFAULT 365,
  `features` text NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `membership_plans_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default membership plans
INSERT INTO `membership_plans` (`name`, `slug`, `description`, `price`, `duration_days`, `features`, `is_active`, `sort_order`) VALUES
('Silver', 'silver', 'Basic membership plan with essential features', 999.00, 365, '["Feature 1", "Feature 2", "Feature 3"]', 1, 1),
('Gold', 'gold', 'Premium membership plan with advanced features', 1999.00, 365, '["All Silver features", "Feature 4", "Feature 5", "Feature 6"]', 1, 2),
('Diamond', 'diamond', 'Ultimate membership plan with all features', 4999.00, 365, '["All Gold features", "Feature 7", "Feature 8", "Feature 9", "Priority Support"]', 1, 3);

-- Add indexes for better performance
ALTER TABLE `re_accounts` 
ADD INDEX `idx_membership_plan` (`membership_plan_id`),
ADD INDEX `idx_account_status` (`account_status`),
ADD INDEX `idx_membership_status` (`membership_status`);

-- Success message
SELECT 'Database updated successfully! All columns and tables created.' AS message;
