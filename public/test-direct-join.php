<?php
// Direct Join Test - Bypass form, directly call joinDraw
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Botble\RealEstate\Models\Account;
use Botble\RealEstate\Models\LuckyDraw;

echo "<h1>Direct Join Test</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;}</style>";

if (!auth('account')->check()) {
    echo "<p class='error'>Please login first!</p>";
    die();
}

$drawId = $_GET['draw_id'] ?? null;
if (!$drawId) {
    echo "<p class='error'>No draw ID provided!</p>";
    die();
}

$user = auth('account')->user();
$draw = LuckyDraw::find($drawId);

if (!$draw) {
    echo "<p class='error'>Draw not found!</p>";
    die();
}

echo "<h2>Attempting to Join Draw:</h2>";
echo "<p><strong>Draw:</strong> {$draw->name} (ID: {$draw->id})</p>";
echo "<p><strong>User:</strong> {$user->name} (ID: {$user->id})</p>";

echo "<hr>";

// Check before status
echo "<h3>Before Join:</h3>";
echo "<p>Draws Remaining: {$user->draws_remaining}</p>";
echo "<p>Draws Used: {$user->draws_used}</p>";
echo "<p>Current Active Draw: " . ($user->current_active_draw_id ?? 'None') . "</p>";

echo "<hr>";

try {
    // Attempt to join
    echo "<h3>Joining Draw...</h3>";
    
    if (!$user->canJoinDraw($draw)) {
        echo "<p class='error'>❌ canJoinDraw() returned false!</p>";
        
        // Check why
        $alreadyJoined = $user->luckyDrawParticipations()->where('draw_id', $draw->id)->exists();
        if ($alreadyJoined) {
            echo "<p>Reason: Already joined this draw</p>";
        }
        if (!$user->isApproved()) {
            echo "<p>Reason: Account not approved (Status: {$user->account_status})</p>";
        }
        if ($user->membership_status !== 'active') {
            echo "<p>Reason: Membership not active (Status: {$user->membership_status})</p>";
        }
        if ($user->current_active_draw_id) {
            echo "<p>Reason: Already has active draw (ID: {$user->current_active_draw_id})</p>";
        }
        if ($user->draws_remaining <= 0) {
            echo "<p>Reason: No draws remaining ({$user->draws_remaining})</p>";
        }
        
        die();
    }
    
    echo "<p class='success'>✅ canJoinDraw() passed!</p>";
    
    // Call joinDraw
    $participant = $user->joinDraw($draw);
    
    echo "<p class='success'>✅ joinDraw() executed!</p>";
    echo "<p>Participant ID: {$participant->id}</p>";
    
    // Refresh user data
    $user = $user->fresh();
    
    echo "<hr>";
    echo "<h3>After Join:</h3>";
    echo "<p>Draws Remaining: {$user->draws_remaining}</p>";
    echo "<p>Draws Used: {$user->draws_used}</p>";
    echo "<p>Current Active Draw: " . ($user->current_active_draw_id ?? 'None') . "</p>";
    echo "<p>Total Draws Joined: {$user->total_draws_joined}</p>";
    
    echo "<hr>";
    echo "<h2 class='success'>✅ SUCCESS! Draw joined successfully!</h2>";
    echo "<p><a href='/account/lucky-draws'>View My Draws</a></p>";
    
} catch (\Exception $e) {
    echo "<p class='error'>❌ ERROR: {$e->getMessage()}</p>";
    echo "<pre>{$e->getTraceAsString()}</pre>";
}

echo "<hr>";
echo "<p><a href='debug-join.php'>Back to Debug Page</a></p>";
