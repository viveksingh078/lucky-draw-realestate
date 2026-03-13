-- Fix wallet_on_hold calculation
-- This should only show money that is actually locked in ACTIVE draws

-- Step 1: Reset all on-hold amounts to 0
UPDATE re_accounts 
SET wallet_on_hold = 0;

-- Step 2: Calculate correct on-hold amount based on ACTIVE draws only
UPDATE re_accounts a
SET wallet_on_hold = (
    SELECT COALESCE(SUM(ldp.entry_fee_paid), 0)
    FROM lucky_draw_participants ldp
    INNER JOIN lucky_draws ld ON ld.id = ldp.draw_id
    WHERE ldp.account_id = a.id
    AND ld.status = 'active'
    AND ldp.payment_status = 'paid'
)
WHERE membership_plan_id IS NOT NULL;

-- Step 3: Verify the fix
SELECT 
    a.id,
    a.email,
    a.first_name,
    mp.name as plan_name,
    a.wallet_balance,
    a.wallet_on_hold,
    (a.wallet_balance - a.wallet_on_hold) as available,
    (SELECT COUNT(*) 
     FROM lucky_draw_participants ldp 
     INNER JOIN lucky_draws ld ON ld.id = ldp.draw_id 
     WHERE ldp.account_id = a.id 
     AND ld.status = 'active' 
     AND ldp.payment_status = 'paid') as active_draws_count
FROM re_accounts a
LEFT JOIN membership_plans mp ON mp.id = a.membership_plan_id
WHERE a.membership_plan_id IS NOT NULL;

-- Success message
SELECT 'Wallet on-hold amounts fixed!' AS message;
