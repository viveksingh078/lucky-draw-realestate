-- Fix Diksha Malik's discount (should be 0 because she won)
-- Winners don't get discount, only losers do

-- First check current value
SELECT 
    id,
    first_name,
    last_name,
    available_discount,
    membership_status
FROM re_accounts
WHERE first_name = 'Diksha' AND last_name = 'Malik';

-- Update to correct value (0 because she won the latest draw)
UPDATE re_accounts
SET available_discount = 0,
    membership_status = 'expired'
WHERE first_name = 'Diksha' AND last_name = 'Malik';

-- Verify the update
SELECT 
    id,
    first_name,
    last_name,
    available_discount,
    membership_status
FROM re_accounts
WHERE first_name = 'Diksha' AND last_name = 'Malik';
