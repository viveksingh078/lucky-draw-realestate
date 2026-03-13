-- Check User Status for Join Draw
-- Run this in phpMyAdmin to see your account status

-- 1. Check your account details
SELECT 
    id,
    CONCAT(first_name, ' ', last_name) as name,
    email,
    account_status,
    membership_status,
    membership_plan_id,
    draws_used,
    draws_remaining,
    current_active_draw_id,
    approved_at
FROM re_accounts 
WHERE email = 'YOUR_EMAIL_HERE'  -- CHANGE THIS TO YOUR EMAIL
LIMIT 1;

-- 2. Check membership plan details
SELECT 
    mp.id,
    mp.name,
    mp.price,
    mp.draws_allowed,
    mp.max_concurrent_draws
FROM membership_plans mp
INNER JOIN re_accounts a ON a.membership_plan_id = mp.id
WHERE a.email = 'YOUR_EMAIL_HERE'  -- CHANGE THIS TO YOUR EMAIL
LIMIT 1;

-- 3. Check active draws
SELECT 
    id,
    name,
    status,
    start_date,
    end_date,
    draw_date,
    property_value,
    entry_fee
FROM lucky_draws 
WHERE status = 'active' 
AND end_date > NOW()
ORDER BY draw_date ASC;

-- 4. Check if user already joined any draw
SELECT 
    ldp.id,
    ldp.draw_id,
    ld.name as draw_name,
    ldp.payment_status,
    ldp.joined_at,
    ldp.is_winner
FROM lucky_draw_participants ldp
INNER JOIN lucky_draws ld ON ld.id = ldp.draw_id
INNER JOIN re_accounts a ON a.id = ldp.account_id
WHERE a.email = 'YOUR_EMAIL_HERE'  -- CHANGE THIS TO YOUR EMAIL
ORDER BY ldp.created_at DESC
LIMIT 5;

-- 5. Fix user account if needed (UNCOMMENT TO RUN)
-- UPDATE re_accounts 
-- SET 
--     account_status = 'approved',
--     membership_status = 'active',
--     membership_plan_id = 1,
--     draws_used = 0,
--     draws_remaining = 2,
--     current_active_draw_id = NULL,
--     approved_at = NOW()
-- WHERE email = 'YOUR_EMAIL_HERE';  -- CHANGE THIS TO YOUR EMAIL
