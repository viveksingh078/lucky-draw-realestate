# Dummy Winner Selection Implementation

## Summary
Admin can now select either a real participant OR a dummy winner when manually selecting winners for a draw.

## Changes Made

### 1. Controller - Load Dummy Winners ✅
**File:** `platform/plugins/real-estate/src/Http/Controllers/LuckyDrawController.php`
**Method:** `selectWinner()`

Added:
```php
// Get all dummy winners
$dummyWinners = \Botble\RealEstate\Models\DummyWinner::all();
```

And passed to view:
```php
return view('...', compact('draw', 'participants', 'dummyWinners', ...));
```

### 2. View - Added Tabs for Real vs Dummy ✅
**File:** `platform/plugins/real-estate/resources/views/lucky-draws/select-winner.blade.php`

- Added Bootstrap tabs with 2 sections:
  - "Real Participants" tab
  - "Dummy Winners" tab
- Added hidden input: `<input type="hidden" name="winner_type" id="winner_type" value="real">`
- Changed radio button name from `winner_account_id` to `winner_id`
- Added `data-type="real"` or `data-type="dummy"` to each radio button

### 3. JavaScript - Handle Type Selection ✅
Updated JavaScript to:
- Set `winner_type` field based on selected radio button
- Show confirmation with winner type (Real Participant or Dummy Winner)

### 4. Controller - Process Winner Selection ❌ PENDING
**File:** `platform/plugins/real-estate/src/Http/Controllers/LuckyDrawController.php`
**Method:** `setWinner()`

**NEEDS TO BE UPDATED:**

Change validation from:
```php
$request->validate([
    'winner_account_id' => 'required|exists:re_accounts,id',
]);
```

To:
```php
$request->validate([
    'winner_id' => 'required',
    'winner_type' => 'required|in:real,dummy',
]);
```

Then update logic:
```php
$winnerType = $request->input('winner_type');
$winnerId = $request->input('winner_id');

// Update draw with winner_type
$draw->update([
    'status' => 'completed',
    'winner_id' => $winnerId,
    'winner_type' => $winnerType,  // 'real' or 'dummy'
    ...
]);

// Only process participants if real winner
if ($winnerType === 'real') {
    // Mark winner participant as is_winner = true
    // Mark others as is_winner = false
    // Update discounts
}

// If dummy winner, all participants are losers
if ($winnerType === 'dummy') {
    foreach ($participants as $participant) {
        $participant->update(['is_winner' => false]);
        // Update discount for all (they all lost)
        if ($participant->account) {
            $participant->account->refresh();
            if (method_exists($participant->account, 'updateDiscountAfterDraw')) {
                $participant->account->updateDiscountAfterDraw($draw, false);
            }
        }
    }
}

// Send email only for real winners
if ($winnerType === 'real') {
    $this->sendWinnerNotification($draw, $winnerParticipant);
}
```

## How It Works

1. Admin clicks "Select Winner Manually"
2. Page shows 2 tabs:
   - **Real Participants**: Shows all paid participants
   - **Dummy Winners**: Shows all dummy winners from database
3. Admin selects one winner (either real or dummy)
4. System saves:
   - `winner_id`: ID of winner (account_id or dummy_winner_id)
   - `winner_type`: 'real' or 'dummy'
5. If real winner: One participant wins, others lose
6. If dummy winner: ALL participants lose (get discount)

## Database
- `re_lucky_draws` table already has `winner_type` column
- `re_dummy_winners` table already exists with dummy winner data

## Testing
1. Create a draw with participants
2. Go to "Select Winner Manually"
3. Try selecting a real participant - should work
4. Try selecting a dummy winner - should work
5. Check winners page - should show correct winner

## Cache Clear
After all changes: https://sspl20.com/clear-all-cache.php
