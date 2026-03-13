-- Add account_type field to differentiate between user and vendor
ALTER TABLE `re_accounts` 
ADD COLUMN `account_type` ENUM('user', 'vendor') NOT NULL DEFAULT 'user' AFTER `account_status`;

-- Add index for faster queries
ALTER TABLE `re_accounts` ADD INDEX `idx_account_type` (`account_type`);

-- Update existing accounts to be 'user' type
UPDATE `re_accounts` SET `account_type` = 'user' WHERE `account_type` IS NULL;
