-- Add Aadhaar card fields to re_accounts table
ALTER TABLE `re_accounts` 
ADD COLUMN `aadhaar_number` VARCHAR(12) NULL UNIQUE AFTER `pan_card_number`,
ADD COLUMN `aadhaar_front_image` VARCHAR(255) NULL AFTER `aadhaar_number`,
ADD COLUMN `aadhaar_back_image` VARCHAR(255) NULL AFTER `aadhaar_front_image`;

-- Add index for faster lookup
ALTER TABLE `re_accounts` ADD INDEX `idx_aadhaar_number` (`aadhaar_number`);
