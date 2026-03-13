# Wallet/Money System - Design Document

## Client Requirement Change

### OLD SYSTEM (Credits):
```
Silver Plan (₹10,000) → 1 credit
Gold Plan (₹30,000) → 2 credits
Diamond Plan (₹50,000) → 5 credits

Dashboard shows: "2 Credits Remaining"
```

### NEW SYSTEM (Wallet/Money):
```
Silver Plan (₹10,000) → ₹10,000 wallet balance
Gold Plan (₹30,000) → ₹30,000 wallet balance
Diamond Plan (₹50,000) → ₹50,000 wallet balance

Dashboard shows: "₹30,000 Balance"
```

## Key Changes

### 1. Terminology Change
- ❌ "Credits" → ✅ "Wallet Balance" / "Money"
- ❌ "Draw Credits" → ✅ "Wallet Balance"
- ❌ "Credits Remaining" → ✅ "Available Balance"

### 2. Database Fields
**Current:**
- `draws_allowed` - Number of draws (1, 2, 5)
- `draws_remaining` - Credits left
- `draws_used` - Credits used

**New Approach:**
- Keep same fields but change display logic
- `draws_remaining` will store money value
- When user buys plan, set `draws_remaining = plan price`

### 3. Draw Entry Cost
**Fixed Cost per Draw: ₹10,000**

All plans use ₹10,000 per draw entry:
- Silver (₹10,000) = 1 draw (₹10,000 / ₹10,000)
- Gold (₹30,000) = 3 draws (₹30,000 / ₹10,000)
- Diamond (₹50,000) = 5 draws (₹50,000 / ₹10,000)

### 4. User Flow

**Step 1: Buy Plan**
```
User buys Gold Plan (₹30,000)
↓
Wallet Balance = ₹30,000
```

**Step 2: Join Draw**
```
User joins Draw 1
↓
Wallet Balance: ₹30,000 - ₹10,000 = ₹20,000
Balance On Hold: ₹10,000
```

**Step 3: Draw Result**
```
Option A: User WINS
↓
Balance On Hold: ₹10,000 → Consumed
Wallet Balance: ₹20,000 (unchanged)
Membership: Expired

Option B: User LOSES
↓
Balance On Hold: ₹10,000 → Consumed
Wallet Balance: ₹20,000 (unchanged)
Available Discount: +₹10,000
```

**Step 4: Join Another Draw**
```
User joins Draw 2
↓
Wallet Balance: ₹20,000 - ₹10,000 = ₹10,000
Balance On Hold: ₹10,000
```

## Database Schema Changes

### Option 1: Reuse Existing Fields (Recommended)
```sql
-- No new columns needed!
-- Just change the logic:

-- When user buys plan:
UPDATE re_accounts 
SET draws_remaining = membership_plan.price
WHERE id = user_id;

-- When user joins draw:
UPDATE re_accounts 
SET draws_remaining = draws_remaining - 10000,
    draws_used = draws_used + 10000
WHERE id = user_id;
```

### Option 2: Add New Wallet Fields (More Clear)
```sql
ALTER TABLE re_accounts
ADD COLUMN wallet_balance DECIMAL(15,2) DEFAULT 0 AFTER draws_remaining,
ADD COLUMN wallet_on_hold DECIMAL(15,2) DEFAULT 0 AFTER wallet_balance,
ADD COLUMN wallet_used DECIMAL(15,2) DEFAULT 0 AFTER wallet_on_hold;
```

## Implementation Plan

### Phase 1: Database Update
1. Add wallet fields (if using Option 2)
2. Migrate existing data:
   - Convert credits to money
   - `wallet_balance = draws_remaining × 10000`

### Phase 2: Model Updates
1. Update Account model:
   - Add wallet methods
   - Change credit methods to wallet methods
2. Update MembershipPlan model:
   - Remove draws_allowed display
   - Show plan price as wallet credit

### Phase 3: UI Updates
1. Dashboard:
   - Show "Wallet Balance: ₹30,000"
   - Show "On Hold: ₹10,000"
   - Show "Available: ₹20,000"

2. Header:
   - Change "Draw Credits: 2" → "Wallet: ₹20,000"

3. Draw Join Page:
   - Show "Entry Fee: ₹10,000"
   - Show "Your Balance: ₹30,000"
   - Button: "Join Draw (₹10,000)"

### Phase 4: Logic Updates
1. Join Draw:
   - Check if `wallet_balance >= 10000`
   - Deduct ₹10,000 from balance
   - Add ₹10,000 to on_hold

2. Draw Complete:
   - Remove from on_hold
   - If lost: Add to discount

3. Leave Draw:
   - Refund ₹10,000 to balance
   - Remove from on_hold

## Display Examples

### Dashboard Cards:
```
┌─────────────────────────────────┐
│ Wallet Balance                  │
│ ₹30,000                         │
│ (Available: ₹20,000)            │
│ (On Hold: ₹10,000)              │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│ Membership Plan                 │
│ Gold Plan                       │
│ ₹30,000 Wallet Credit           │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│ Property Discount               │
│ ₹10,000                         │
│ (From 1 lost draw)              │
└─────────────────────────────────┘
```

### Header:
```
[Logo] [User] [Settings] [Wallet: ₹20,000] [Reward Draws] [Logout]
```

### Draw Join Button:
```
Before: "Join Draw (2 credits left)"
After:  "Join Draw (₹10,000)"
```

## Business Rules

1. **Fixed Entry Fee:**
   - All draws cost ₹10,000 to enter
   - No variable pricing

2. **Wallet Balance:**
   - Shows total money available
   - Deducted when joining draw
   - Refunded when leaving draw

3. **On Hold Amount:**
   - Money locked in active draws
   - Released when draw completes
   - Not available for new draws

4. **Available Balance:**
   - `Available = Wallet Balance - On Hold`
   - This is what user can use for new draws

5. **Discount System:**
   - Lost draws add ₹10,000 to discount
   - Can be used on property purchase
   - Separate from wallet balance

## Migration Strategy

### Step 1: Add Wallet Fields
```sql
ALTER TABLE re_accounts
ADD COLUMN wallet_balance DECIMAL(15,2) DEFAULT 0,
ADD COLUMN wallet_on_hold DECIMAL(15,2) DEFAULT 0,
ADD COLUMN wallet_used DECIMAL(15,2) DEFAULT 0;
```

### Step 2: Migrate Existing Data
```sql
-- Convert credits to money (₹10,000 per credit)
UPDATE re_accounts
SET wallet_balance = draws_remaining * 10000,
    wallet_used = draws_used * 10000;

-- Calculate on-hold amount (active draws × ₹10,000)
UPDATE re_accounts a
SET wallet_on_hold = (
    SELECT COUNT(*) * 10000
    FROM lucky_draw_participants ldp
    INNER JOIN lucky_draws ld ON ld.id = ldp.draw_id
    WHERE ldp.account_id = a.id
    AND ld.status = 'active'
    AND ldp.payment_status = 'paid'
);
```

### Step 3: Update Plan Prices (Optional)
```sql
-- If you want to adjust plan prices
UPDATE membership_plans
SET price = 10000, draws_allowed = 1 WHERE slug = 'silver';
UPDATE membership_plans
SET price = 30000, draws_allowed = 3 WHERE slug = 'gold';
UPDATE membership_plans
SET price = 50000, draws_allowed = 5 WHERE slug = 'diamond';
```

## Testing Checklist

- [ ] User buys plan → Wallet balance = plan price
- [ ] User joins draw → Balance deducted, on-hold increased
- [ ] User leaves draw → Balance refunded, on-hold decreased
- [ ] Draw completes (win) → On-hold cleared, membership expires
- [ ] Draw completes (lose) → On-hold cleared, discount added
- [ ] Dashboard shows correct balance
- [ ] Header shows correct balance
- [ ] Can't join if balance < ₹10,000
- [ ] Multiple draws work correctly
- [ ] Balance calculations are accurate

## Advantages of Wallet System

1. ✅ **More Transparent:** Users see actual money
2. ✅ **Easier to Understand:** ₹30,000 vs "2 credits"
3. ✅ **Flexible:** Can add money anytime (future feature)
4. ✅ **Professional:** Like real wallet/payment system
5. ✅ **Clear Pricing:** ₹10,000 per draw is clear

## Next Steps

1. Create SQL migration file
2. Update Account model with wallet methods
3. Update all UI to show money instead of credits
4. Update join/leave logic to use wallet
5. Test thoroughly
6. Deploy

---

**Recommendation:** Use Option 2 (new wallet fields) for clarity and future flexibility.
