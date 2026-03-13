-- ============================================
-- CONVERT CREDIT SYSTEM TO WALLET/MONEY SYSTEM
-- ============================================
-- This converts the credit-based system to money-based wallet system
-- Users will see actual money (₹) instead of credits
-- ============================================

-- STEP 1: Add wallet fields to re_accounts table
ALTER TABLE `re_accounts`
ADD COLUMN `wallet_balance` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Total wallet money available' AFTER `draws_remaining`,
ADD COLUMN `wallet_on_hold` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Money locked in active draws' AFTER `wallet_balance`,
ADD COLUMN `wallet_used` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Total money spent on draws' AFTER `wallet_on_hold`;

-- STEP 2: Migrate existing credit data to wallet money
-- Convert credits to money (₹10,000 per credit/draw)
UPDATE `re_accounts`
SET 
    wallet_balance = draws_remaining * 10000,
    wallet_used = draws_used * 10000
WHERE membership_plan_id IS NOT NULL;

-- STEP 3: Calculate on-hold amount for users with active draws
-- Each active draw holds ₹10,000
UPDATE re_accounts a
SET wallet_on_hold = (
    SELECT COALESCE(COUNT(*), 0) * 10000
    FROM lucky_draw_participants ldp
    INNER JOIN lucky_draws ld ON ld.id = ldp.draw_id
    WHERE ldp.account_id = a.id
    AND ld.status = 'active'
    AND ldp.payment_status = 'paid'
)
WHERE membership_plan_id IS NOT NULL;

-- STEP 4: Update membership plans to reflect wallet credits
-- (Optional - adjust if needed)
UPDATE membership_plans SET draws_allowed = 1 WHERE slug = 'silver';
UPDATE membership_plans SET draws_allowed = 3 WHERE slug = 'gold';
UPDATE membership_plans SET draws_allowed = 5 WHERE slug = 'diamond';

-- STEP 5: Verify wallet balances
SELECT 
    a.id,
    a.email,
    a.first_name,
    a.last_name,
    mp.name as plan_name,
    mp.price as plan_price,
    a.wallet_balance,
    a.wallet_on_hold,
    a.wallet_used,
    (a.wallet_balance - a.wallet_on_hold) as available_balance,
    CONCAT('₹', FORMAT(a.wallet_balance, 0)) as 'Wallet Display',
    CONCAT('₹', FORMAT(a.wallet_on_hold, 0)) as 'On Hold Display',
    CONCAT('₹', FORMAT(a.wallet_balance - a.wallet_on_hold, 0)) as 'Available Display'
FROM re_accounts a
LEFT JOIN membership_plans mp ON mp.id = a.membership_plan_id
WHERE a.membership_plan_id IS NOT NULL
ORDER BY a.id;

-- STEP 6: Add indexes for better performance
ALTER TABLE `re_accounts` 
ADD INDEX `idx_wallet_balance` (`wallet_balance`),
ADD INDEX `idx_wallet_on_hold` (`wallet_on_hold`);

-- ============================================
-- EXPECTED RESULTS:
-- ============================================
-- Silver Plan (₹10,000) → Wallet: ₹10,000
-- Gold Plan (₹30,000) → Wallet: ₹30,000
-- Diamond Plan (₹50,000) → Wallet: ₹50,000
--
-- User with 1 active draw:
-- - Wallet Balance: ₹30,000
-- - On Hold: ₹10,000
-- - Available: ₹20,000
-- ============================================

-- Success message
SELECT 'Wallet system migration completed successfully!' AS message,
       'Users can now see money (₹) instead of credits' AS note,
       'Entry fee per draw: ₹10,000' AS draw_cost;
