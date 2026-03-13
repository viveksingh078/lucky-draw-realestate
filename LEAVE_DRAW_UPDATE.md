# Leave Draw Feature - Updated Logic

## Changes Made

### Previous Logic (OLD):
- User could leave draw **1 day before start_date**
- Example: If start_date = 31 Jan, user could leave until 30 Jan
- Very restrictive - users couldn't leave after draw started

### New Logic (CURRENT):
- User can leave draw **anytime before end_date**
- Example: If end_date = 18 Feb, user can leave until 17 Feb (11:59 PM)
- Much more flexible - users can exit anytime before draw completes

## Files Modified

### 1. `platform/plugins/real-estate/src/Models/Account.php`
**Method: `leaveDraw()`**
```php
// OLD: Check 1 day before start
$oneDayBeforeStart = $draw->start_date->copy()->subDay();
if (now()->greaterThan($oneDayBeforeStart)) {
    return false;
}

// NEW: Check before end date
if (now()->greaterThanOrEqualTo($draw->end_date)) {
    return false; // Cannot leave, draw has ended
}
```

**Method: `canLeaveDraw()`**
```php
// OLD: Check 1 day before start
$oneDayBeforeStart = $draw->start_date->copy()->subDay();
return now()->lessThanOrEqualTo($oneDayBeforeStart);

// NEW: Check before end date
return now()->lessThan($draw->end_date);
```

### 2. `platform/plugins/real-estate/src/Http/Controllers/PublicLuckyDrawController.php`
**Method: `leave()`**
```php
// OLD: Error message with start date
$oneDayBefore = $draw->start_date->copy()->subDay()->format('M d, Y h:i A');
return redirect()->with('error', 'You can only leave the draw before ' . $oneDayBefore);

// NEW: Error message with end date
$endDate = $draw->end_date->format('M d, Y h:i A');
return redirect()->with('error', 'You can only leave the draw before it ends (' . $endDate . ')');
```

### 3. `platform/themes/flex-home/views/real-estate/public/lucky-draws/index.blade.php`
**View Changes:**
```php
// OLD: Show start date deadline
<small class="text-muted d-block mt-1">Can leave until {{ $oneDayBefore->format('M d, h:i A') }}</small>

// NEW: Show end date deadline
<small class="text-muted d-block mt-1">Can leave until {{ $draw->end_date->format('M d, h:i A') }}</small>
```

## User Experience

### Scenario 1: Draw Active
- Start Date: 31 Jan 2026
- End Date: 18 Feb 2026
- Current Date: 5 Feb 2026
- **Result:** ✅ User CAN leave (before end date)

### Scenario 2: Draw Ended
- Start Date: 31 Jan 2026
- End Date: 18 Feb 2026
- Current Date: 19 Feb 2026
- **Result:** ❌ User CANNOT leave (draw ended)

### Scenario 3: Draw Not Started Yet
- Start Date: 31 Jan 2026
- End Date: 18 Feb 2026
- Current Date: 29 Jan 2026
- **Result:** ✅ User CAN leave (before end date)

## Benefits

1. **More Flexible:** Users can change their mind anytime before draw completes
2. **Better UX:** No confusion about "1 day before start" deadline
3. **Simpler Logic:** Just check if draw has ended or not
4. **Fair System:** Users have maximum time to decide

## Testing Steps

1. Join a draw
2. Try to leave before end_date → Should work ✅
3. Wait for draw to end
4. Try to leave after end_date → Should fail ❌
5. Check that credit is refunded when leaving successfully

## Next Steps

1. Clear cache: `https://sspl20.com/clear-all-cache.php`
2. Test the leave functionality
3. Verify credit refund is working
4. Check error messages are showing correctly
