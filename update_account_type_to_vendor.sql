-- Update existing account_type ENUM from 'dealer' to 'vendor'
-- This is for when you already ran the dealer SQL and now want to change to vendor

-- Step 1: Check current column definition
-- Run this first to see current state:
-- SHOW COLUMNS FROM `re_accounts` LIKE 'account_type';

-- Step 2: Modify the ENUM to change 'dealer' to 'vendor'
ALTER TABLE `re_accounts` 
MODIFY COLUMN `account_type` ENUM('user', 'vendor') NOT NULL DEFAULT 'user';

-- Step 3: Update any existing 'dealer' accounts to 'vendor'
UPDATE `re_accounts` 
SET `account_type` = 'vendor' 
WHERE `account_type` = 'dealer';

-- Verify the changes
SELECT account_type, COUNT(*) as count 
FROM `re_accounts` 
GROUP BY account_type;
