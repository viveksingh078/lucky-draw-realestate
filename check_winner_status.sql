-- Check winner status for Diksha Malik
SELECT 
    p.id as participant_id,
    p.is_winner,
    p.payment_status,
    a.first_name,
    a.last_name,
    a.email,
    d.id as draw_id,
    d.name as draw_name,
    d.status as draw_status,
    d.winner_id,
    d.winner_type
FROM re_lucky_draw_participants p
JOIN re_accounts a ON p.account_id = a.id
JOIN re_lucky_draws d ON p.draw_id = d.id
WHERE a.first_name = 'Diksha' AND a.last_name = 'Malik'
ORDER BY p.created_at DESC;

-- Also check if the draw has the correct winner_id
SELECT 
    id,
    name,
    status,
    winner_id,
    winner_type,
    draw_date
FROM re_lucky_draws
WHERE status = 'completed'
ORDER BY draw_date DESC
LIMIT 5;
