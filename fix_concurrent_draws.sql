-- ============================================
-- FIX CONCURRENT DRAWS SYSTEM
-- ============================================
-- This allows users to join multiple draws at the same time
-- based on their membership plan's draws_allowed value
-- 
-- IMPORTANT: Run these queries ONE BY ONE in phpMyAdmin
-- ============================================

-- STEP 1: Check current membership plans configuration
-- Copy and run this first to see current values
SELECT 
    id, 
    name, 
    slug,
    price, 
    draws_allowed, 
    max_concurrent_draws,
    CASE 
        WHEN max_concurrent_draws < draws_allowed THEN '❌ NEEDS FIX'
        ELSE '✅ OK'
    END as status
FROM membership_plans
ORDER BY id;

-- STEP 2: Update max_concurrent_draws to match draws_allowed
-- This is the main fix - allows users to join multiple draws simultaneously
UPDATE membership_plans 
SET max_concurrent_draws = draws_allowed;

-- STEP 3: Verify the fix worked
SELECT 
    id, 
    name, 
    slug,
    draws_allowed, 
    max_concurrent_draws,
    '✅ FIXED' as status
FROM membership_plans
ORDER BY id;

-- STEP 4: Check your account status
-- Replace 'YOUR_EMAIL_HERE' with your actual email
SELECT 
    a.id,
    a.email,
    a.first_name,
    a.last_name,
    a.account_status,
    a.membership_status,
    a.draws_remaining,
    a.draws_used,
    mp.name as plan_name,
    mp.draws_allowed as 'total_draws_in_plan',
    mp.max_concurrent_draws as 'max_at_same_time',
    (SELECT COUNT(*) 
     FROM lucky_draw_participants ldp 
     INNER JOIN lucky_draws ld ON ld.id = ldp.draw_id 
     WHERE ldp.account_id = a.id 
     AND ld.status = 'active' 
     AND ldp.payment_status = 'paid') as 'currently_joined_draws'
FROM re_accounts a
LEFT JOIN membership_plans mp ON mp.id = a.membership_plan_id
WHERE a.email = 'YOUR_EMAIL_HERE';  -- ⚠️ CHANGE THIS TO YOUR EMAIL

-- STEP 5: (OPTIONAL) If you need to reset your draws
-- Uncomment and run this if you want to reset your draw credits
-- UPDATE re_accounts 
-- SET draws_used = 0,
--     draws_remaining = (SELECT draws_allowed FROM membership_plans WHERE id = membership_plan_id)
-- WHERE email = 'YOUR_EMAIL_HERE';  -- ⚠️ CHANGE THIS TO YOUR EMAIL

-- ============================================
-- EXPECTED RESULTS AFTER FIX:
-- ============================================
-- Silver Plan: max_concurrent_draws = 1 (can join 1 draw at a time)
-- Gold Plan: max_concurrent_draws = 2 (can join 2 draws at same time)
-- Diamond Plan: max_concurrent_draws = 5 (can join 5 draws at same time)
-- ============================================
