# Membership Draw Credits System - Implementation Guide

## 🎯 Overview
Implemented a membership-based draw credits system where users can join draws based on their membership plan without paying entry fees.

## 📊 Membership Plans & Draw Credits

| Plan | Price | Draws Allowed | Max Concurrent |
|------|-------|---------------|----------------|
| **Silver** | ₹999 | 1 draw | 1 at a time |
| **Gold** | ₹1,999 | 2 draws | 1 at a time |
| **Diamond** | ₹4,999 | 5 draws | 1 at a time |

## 🔑 Key Features

### 1. **No Entry Fees**
- Users don't pay per draw
- Membership plan includes draw credits
- Credits are used when joining a draw

### 2. **One Active Draw at a Time**
- User can only join one draw at a time
- Must wait for current draw to complete before joining another
- Prevents multiple simultaneous participations

### 3. **Draw Credits Tracking**
- `draws_used`: Total draws user has participated in
- `draws_remaining`: Available draws left in membership
- `current_active_draw_id`: Currently active draw (if any)

### 4. **Automatic Credit Management**
- Credits initialized when membership is approved
- Credits deducted when joining a draw
- Slot freed when draw completes (win or lose)
- Losers get property purchase credits

## 📁 Database Changes

### SQL Migration File
Run: `update_membership_draws_system.sql`

**New Columns in `membership_plans`:**
- `draws_allowed` INT - Number of draws allowed in plan
- `max_concurrent_draws` INT - Max concurrent draws (always 1)

**New Columns in `re_accounts`:**
- `draws_used` INT - Total draws used
- `draws_remaining` INT - Remaining draws available
- `current_active_draw_id` BIGINT - Current active draw ID

## 🔄 User Flow

### Joining a Draw:
1. User logs in with active membership
2. Checks available draws
3. Clicks "Join Draw" (shows remaining credits)
4. System validates:
   - Has active membership
   - Has remaining draws
   - No active draw running
   - Not already joined this draw
5. If valid, joins instantly (no payment needed)
6. Draw credit deducted, slot marked as active

### Draw Completion:
1. Draw executes (winner selected)
2. Winner gets property
3. Losers get purchase credits
4. All participants' active draw slot freed
5. Can now join next draw (if credits remaining)

## 💻 Code Changes

### Models Updated:
1. **MembershipPlan.php**
   - Added `draws_allowed` and `max_concurrent_draws` to fillable

2. **Account.php**
   - Added draw tracking fields
   - New methods:
     - `canJoinDraw()` - Check if user can join
     - `joinDraw()` - Join using credits
     - `completeDraw()` - Free up slot after draw
     - `hasActiveDraw()` - Check active draw
     - `currentActiveDraw()` - Get active draw
     - `initializeDrawCredits()` - Initialize credits

### Controllers Updated:
1. **PublicLuckyDrawController.php**
   - `join()` method updated to use membership credits
   - Removed payment flow
   - Added validation for active draws and credits

2. **AccountController.php**
   - `approve()` method initializes draw credits
   - Sets `draws_remaining` based on plan

### Services Updated:
1. **LuckyDrawService.php**
   - `processParticipants()` calls `completeDraw()` for all
   - `calculateCreditAmount()` uses membership price
   - Frees up slots after draw completion

### Views Updated:
1. **public/lucky-draws/index.blade.php** (both plugin & theme)
   - Removed entry fee display
   - Shows remaining draws count
   - Shows different button states:
     - "Join Draw (X left)" - Can join
     - "Already Joined" - Already in this draw
     - "Active Draw Running" - Has another active draw
     - "No Credits Left" - No draws remaining

## 🎮 Admin Features

### Membership Plan Management:
- Admin can set `draws_allowed` for each plan
- Plans show draw credits in features list
- Users see draw allowance when purchasing

### Account Approval:
- When admin approves account:
  - `draws_used` = 0
  - `draws_remaining` = plan's `draws_allowed`
  - User can immediately join draws

## 🚀 Testing Steps

1. **Run SQL Migration:**
   ```sql
   -- Run update_membership_draws_system.sql in phpMyAdmin
   ```

2. **Test User Flow:**
   - Create/approve user with Silver plan (1 draw)
   - User joins a draw
   - Try joining another draw (should be blocked)
   - Wait for draw to complete
   - User can now join next draw

3. **Test Different Plans:**
   - Silver: Can join 1 draw
   - Gold: Can join 2 draws (one at a time)
   - Diamond: Can join 5 draws (one at a time)

4. **Test Validations:**
   - No membership → Cannot join
   - No credits left → Cannot join
   - Active draw running → Cannot join another
   - Already joined → Cannot join same draw again

## 📝 Important Notes

1. **Entry Fee Removed**: Users no longer pay per draw
2. **One at a Time**: Only one active draw allowed
3. **Credits System**: Based on membership plan
4. **Auto-Free Slots**: Slots freed after draw completion
5. **Purchase Credits**: Losers still get property discount credits

## 🔧 Future Enhancements

- Allow multiple concurrent draws for premium plans
- Add draw credit purchase option
- Implement draw credit expiry
- Add draw history tracking
- Send notifications when slot is freed

---

**Status**: ✅ Fully Implemented
**Date**: January 28, 2026
**Version**: 1.0
