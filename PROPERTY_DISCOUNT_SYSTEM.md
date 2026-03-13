# Property Discount System - Design Document

## Client Requirement
"One membership fee = One draw participation OR property purchase discount"
"Membership expires after winning or after being redeemed in a draw"

## Current vs New System

### Current System ❌
- User buys Gold Plan (₹30,000) = 2 draws
- User joins 1 draw, loses = 1 credit wasted
- No benefit from lost draws
- Remaining credits just sit unused

### New System ✅
- User buys Gold Plan (₹30,000) = 2 draws OR discount
- User joins 1 draw, loses = ₹15,000 discount available
- User can use discount when buying property
- Membership value is preserved

## Credit Value Calculation

### Per Credit Value = Plan Price / Total Draws

| Plan | Price | Draws | Per Credit Value |
|------|-------|-------|------------------|
| Silver | ₹10,000 | 1 | ₹10,000 |
| Gold | ₹30,000 | 2 | ₹15,000 |
| Diamond | ₹50,000 | 5 | ₹10,000 |

## Discount Calculation Logic

### Formula:
```
Available Discount = (Draws Used - Draws Won) × Credit Value
```

### Examples:

**Scenario 1: User joined 1 draw, lost**
- Plan: Gold (₹30,000, 2 draws, ₹15,000/credit)
- Draws used: 1
- Draws won: 0
- Lost draws: 1
- **Discount: 1 × ₹15,000 = ₹15,000**

**Scenario 2: User joined 2 draws, lost both**
- Plan: Gold (₹30,000, 2 draws, ₹15,000/credit)
- Draws used: 2
- Draws won: 0
- Lost draws: 2
- **Discount: 2 × ₹15,000 = ₹30,000**

**Scenario 3: User never joined any draw**
- Plan: Gold (₹30,000, 2 draws)
- Draws used: 0
- **Discount: Full plan amount = ₹30,000**

**Scenario 4: User joined 1 draw, WON**
- Plan: Gold (₹30,000, 2 draws)
- Draws won: 1
- **Discount: ₹0** (Membership expires after winning)
- **Membership Status: Expired**

## Database Changes Needed

### 1. Add to `membership_plans` table:
```sql
ALTER TABLE membership_plans 
ADD COLUMN credit_value DECIMAL(15,2) DEFAULT 0 AFTER draws_allowed;
```

### 2. Add to `re_accounts` table:
```sql
ALTER TABLE re_accounts 
ADD COLUMN available_discount DECIMAL(15,2) DEFAULT 0 AFTER draws_remaining,
ADD COLUMN discount_used DECIMAL(15,2) DEFAULT 0 AFTER available_discount;
```

### 3. Update existing plans:
```sql
-- Calculate and set credit values
UPDATE membership_plans 
SET credit_value = price / draws_allowed;
```

## Implementation Steps

### Step 1: Update Models

**MembershipPlan Model:**
- Add `credit_value` field
- Add method `getCreditValue()` to calculate per-credit value

**Account Model:**
- Add `available_discount` field
- Add `discount_used` field
- Add method `calculateAvailableDiscount()` to compute discount
- Add method `useDiscount($amount)` to apply discount on property purchase
- Add method `updateDiscountAfterDraw()` to recalculate after draw completes

### Step 2: Update Draw Completion Logic

**When draw completes:**
1. If user WON:
   - Set `membership_status = 'expired'`
   - Set `available_discount = 0`
   - Membership ends

2. If user LOST:
   - Calculate discount: `lost_draws × credit_value`
   - Update `available_discount`
   - User can use discount on property

### Step 3: Update Dashboard Display

**Show:**
- Available Discount Amount (₹)
- Credit Value per draw (₹)
- How discount is calculated
- Option to use discount on property purchase

### Step 4: Property Purchase Integration

**When user buys property:**
1. Check if user has `available_discount > 0`
2. Show discount option at checkout
3. Apply discount to property price
4. Update `discount_used` field
5. Set `membership_status = 'expired'` after discount used

## UI Changes

### Dashboard Cards:
```
┌─────────────────────────────────────┐
│ Available Discount: ₹15,000         │
│ (1 lost draw × ₹15,000)             │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ Membership Value: ₹30,000           │
│ Per Credit: ₹15,000                 │
└─────────────────────────────────────┘
```

### Property Page:
```
Property Price: ₹5,00,000
Your Discount: -₹15,000
─────────────────────────
Final Price: ₹4,85,000
```

## Business Rules

1. **Discount Accumulation:**
   - Each lost draw adds to discount
   - Discount = lost_draws × credit_value

2. **Discount Usage:**
   - Can be used on any property purchase
   - One-time use only
   - Membership expires after use

3. **Membership Expiry:**
   - Expires when user wins a draw
   - Expires when discount is used
   - Does NOT expire if draws are unused

4. **Unused Credits:**
   - If user never joins draws, full plan amount becomes discount
   - Encourages property purchase

## Migration SQL

```sql
-- Add new columns
ALTER TABLE membership_plans 
ADD COLUMN credit_value DECIMAL(15,2) DEFAULT 0 AFTER draws_allowed;

ALTER TABLE re_accounts 
ADD COLUMN available_discount DECIMAL(15,2) DEFAULT 0 AFTER draws_remaining,
ADD COLUMN discount_used DECIMAL(15,2) DEFAULT 0 AFTER available_discount;

-- Calculate credit values for existing plans
UPDATE membership_plans 
SET credit_value = ROUND(price / draws_allowed, 2);

-- For existing users, calculate available discount based on lost draws
UPDATE re_accounts a
INNER JOIN membership_plans mp ON mp.id = a.membership_plan_id
SET a.available_discount = (
    SELECT COUNT(*) * mp.credit_value
    FROM lucky_draw_participants ldp
    INNER JOIN lucky_draws ld ON ld.id = ldp.draw_id
    WHERE ldp.account_id = a.id
    AND ld.status = 'completed'
    AND ldp.is_winner = 0
)
WHERE a.membership_status = 'active';
```

## Testing Checklist

- [ ] Credit value calculated correctly for each plan
- [ ] Discount updates when draw completes (lost)
- [ ] Discount shows on dashboard
- [ ] Discount can be applied on property purchase
- [ ] Membership expires after winning
- [ ] Membership expires after discount used
- [ ] Activity logs show discount earned
- [ ] Activity logs show discount used

## Next Steps

1. Run migration SQL
2. Update models with new fields and methods
3. Update draw completion logic
4. Update dashboard to show discount
5. Integrate discount in property purchase flow
6. Test all scenarios
