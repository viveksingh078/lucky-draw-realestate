-- Check if user joined draw successfully
-- Run this AFTER clicking Join Draw button

-- 1. Check user account status
SELECT 
    id,
    email,
    CONCAT(first_name, ' ', last_name) as name,
    account_status,
    membership_status,
    membership_plan_id,
    draws_used,
    draws_remaining,
    current_active_draw_id,
    total_draws_joined,
    last_draw_joined
FROM re_accounts 
WHERE email = 'YOUR_EMAIL_HERE'  -- CHANGE THIS
LIMIT 1;

-- 2. Check if participation record was created
SELECT 
    ldp.id,
    ldp.draw_id,
    ld.name as draw_name,
    ldp.account_id,
    ldp.entry_fee_paid,
    ldp.payment_status,
    ldp.joined_at,
    ldp.created_at
FROM lucky_draw_participants ldp
INNER JOIN lucky_draws ld ON ld.id = ldp.draw_id
INNER JOIN re_accounts a ON a.id = ldp.account_id
WHERE a.email = 'YOUR_EMAIL_HERE'  -- CHANGE THIS
ORDER BY ldp.created_at DESC
LIMIT 5;

-- 3. Check active draws
SELECT 
    id,
    name,
    status,
    start_date,
    end_date,
    draw_date
FROM lucky_draws 
WHERE status = 'active'
ORDER BY id DESC;

-- 4. Count participants in each draw
SELECT 
    ld.id,
    ld.name,
    COUNT(ldp.id) as total_participants
FROM lucky_draws ld
LEFT JOIN lucky_draw_participants ldp ON ldp.draw_id = ld.id
WHERE ld.status = 'active'
GROUP BY ld.id, ld.name;
