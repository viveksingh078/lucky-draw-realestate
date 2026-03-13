-- ============================================
-- PROPERTY DISCOUNT SYSTEM - DATABASE MIGRATION
-- ============================================
-- This adds the ability for users to get property discounts
-- based on their unused/lost draw credits
-- ============================================

-- STEP 1: Add credit_value to membership_plans
-- This stores how much each credit is worth in rupees
ALTER TABLE `membership_plans` 
ADD COLUMN `credit_value` DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `draws_allowed`;

-- STEP 2: Add discount fields to re_accounts
-- available_discount: How much discount user can use
-- discount_used: How much discount has been used
ALTER TABLE `re_accounts` 
ADD COLUMN `available_discount` DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `draws_remaining`,
ADD COLUMN `discount_used` DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `available_discount`;

-- STEP 3: Calculate and set credit values for existing plans
-- Credit Value = Plan Price / Total Draws Allowed
UPDATE `membership_plans` 
SET `credit_value` = ROUND(price / draws_allowed, 2);

-- STEP 4: Verify credit values
SELECT 
    id,
    name,
    price,
    draws_allowed,
    credit_value,
    CONCAT('₹', FORMAT(credit_value, 0)) as 'Credit Value Display'
FROM membership_plans
ORDER BY id;

-- STEP 5: Calculate available discount for existing users
-- Discount = Number of Lost Draws × Credit Value
UPDATE re_accounts a
INNER JOIN membership_plans mp ON mp.id = a.membership_plan_id
SET a.available_discount = (
    SELECT COALESCE(COUNT(*), 0) * mp.credit_value
    FROM lucky_draw_participants ldp
    INNER JOIN lucky_draws ld ON ld.id = ldp.draw_id
    WHERE ldp.account_id = a.id
    AND ld.status = 'completed'
    AND ldp.is_winner = 0
)
WHERE a.membership_status = 'active'
AND a.membership_plan_id IS NOT NULL;

-- STEP 6: For users who never joined draws, give full plan amount as discount
-- (Optional - only if you want to give discount to users who never participated)
-- UPDATE re_accounts a
-- INNER JOIN membership_plans mp ON mp.id = a.membership_plan_id
-- SET a.available_discount = mp.price
-- WHERE a.membership_status = 'active'
-- AND a.total_draws_joined = 0
-- AND a.membership_plan_id IS NOT NULL;

-- STEP 7: Verify user discounts
SELECT 
    a.id,
    a.email,
    a.first_name,
    a.last_name,
    mp.name as plan_name,
    mp.price as plan_price,
    mp.credit_value,
    a.draws_used,
    a.total_draws_won,
    a.available_discount,
    CONCAT('₹', FORMAT(a.available_discount, 0)) as 'Discount Display'
FROM re_accounts a
LEFT JOIN membership_plans mp ON mp.id = a.membership_plan_id
WHERE a.membership_plan_id IS NOT NULL
ORDER BY a.id;

-- ============================================
-- EXPECTED RESULTS:
-- ============================================
-- Silver Plan (₹10,000 / 1 draw) = ₹10,000 per credit
-- Gold Plan (₹30,000 / 2 draws) = ₹15,000 per credit
-- Diamond Plan (₹50,000 / 5 draws) = ₹10,000 per credit
--
-- User with Gold plan who lost 1 draw = ₹15,000 discount
-- User with Gold plan who lost 2 draws = ₹30,000 discount
-- User with Diamond plan who lost 3 draws = ₹30,000 discount
-- ============================================

-- Success message
SELECT 'Property Discount System migration completed successfully!' AS message;
