-- Fix Gold Plan draws_allowed
-- Gold should have 2 draws, not 3

-- Update Gold plan
UPDATE membership_plans 
SET draws_allowed = 2,
    credit_value = ROUND(price / 2, 2)
WHERE slug = 'gold';

-- Verify all plans
SELECT 
    id,
    name,
    slug,
    price,
    draws_allowed,
    credit_value,
    CONCAT('₹', FORMAT(credit_value, 0), ' per draw') as 'Entry Fee'
FROM membership_plans
ORDER BY price;

-- Expected results:
-- Silver: ₹10,000 / 1 draw = ₹10,000 per draw
-- Gold: ₹30,000 / 2 draws = ₹15,000 per draw
-- Diamond: ₹50,000 / 5 draws = ₹10,000 per draw

SELECT 'Gold plan fixed to 2 draws!' AS message;
