-- SQL Query to add Draw Credits System to Membership Plans
-- Run this in your database (phpMyAdmin or MySQL client)

-- Add draws_allowed column to membership_plans table
ALTER TABLE `membership_plans` 
ADD COLUMN `draws_allowed` INT NOT NULL DEFAULT 1 AFTER `duration_days`,
ADD COLUMN `max_concurrent_draws` INT NOT NULL DEFAULT 1 AFTER `draws_allowed`;

-- Add draws tracking columns to re_accounts table
ALTER TABLE `re_accounts` 
ADD COLUMN `draws_used` INT NOT NULL DEFAULT 0 AFTER `membership_end_date`,
ADD COLUMN `draws_remaining` INT NOT NULL DEFAULT 0 AFTER `draws_used`,
ADD COLUMN `current_active_draw_id` BIGINT UNSIGNED NULL AFTER `draws_remaining`;

-- Update existing membership plans with draw credits
UPDATE `membership_plans` SET `draws_allowed` = 1, `max_concurrent_draws` = 1 WHERE `slug` = 'silver';
UPDATE `membership_plans` SET `draws_allowed` = 2, `max_concurrent_draws` = 1 WHERE `slug` = 'gold';
UPDATE `membership_plans` SET `draws_allowed` = 5, `max_concurrent_draws` = 1 WHERE `slug` = 'diamond';

-- Update features for membership plans
UPDATE `membership_plans` SET 
    `features` = '["Draw 1 time Reward Property Draws", "Basic Support", "Property Listings Access"]',
    `description` = 'Basic membership plan with essential features'
WHERE `slug` = 'silver';

UPDATE `membership_plans` SET 
    `features` = '["Draw 2 time Reward Property Draws", "Priority Support", "Property Listings Access", "Advanced Filters"]',
    `description` = 'Premium membership plan with advanced features'
WHERE `slug` = 'gold';

UPDATE `membership_plans` SET 
    `features` = '["Draw 5 time Reward Property Draws", "VIP Support", "Property Listings Access", "Advanced Filters", "Early Access to New Draws"]',
    `description` = 'Ultimate membership plan with all features'
WHERE `slug` = 'diamond';

-- Add index for better performance
ALTER TABLE `re_accounts` 
ADD INDEX `idx_current_draw` (`current_active_draw_id`);

-- Success message
SELECT 'Membership Draw Credits System updated successfully!' AS message;
