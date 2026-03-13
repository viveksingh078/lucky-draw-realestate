<?php
// Debug Join Draw - Check what's happening
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Botble\RealEstate\Models\Account;
use Botble\RealEstate\Models\LuckyDraw;

echo "<h1>Debug Join Draw</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

// Check if user is logged in
if (!auth('account')->check()) {
    echo "<p class='error'>❌ User NOT logged in!</p>";
    echo "<p><a href='" . route('public.account.login') . "'>Login here</a></p>";
    die();
}

$user = auth('account')->user();
echo "<h2 class='success'>✅ User Logged In</h2>";
echo "<p><strong>Name:</strong> {$user->name}</p>";
echo "<p><strong>Email:</strong> {$user->email}</p>";
echo "<p><strong>ID:</strong> {$user->id}</p>";

echo "<hr>";

// Check user status
echo "<h2>User Status:</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Field</th><th>Value</th><th>Status</th></tr>";

$checks = [
    'account_status' => ['value' => $user->account_status, 'expected' => 'approved'],
    'membership_status' => ['value' => $user->membership_status, 'expected' => 'active'],
    'membership_plan_id' => ['value' => $user->membership_plan_id, 'expected' => 'not null'],
    'draws_used' => ['value' => $user->draws_used, 'expected' => 'any'],
    'draws_remaining' => ['value' => $user->draws_remaining, 'expected' => '> 0'],
    'current_active_draw_id' => ['value' => $user->current_active_draw_id, 'expected' => 'null'],
];

foreach ($checks as $field => $check) {
    $value = $check['value'] ?? 'NULL';
    $expected = $check['expected'];
    
    $status = '✅';
    if ($field === 'account_status' && $value !== 'approved') $status = '❌';
    if ($field === 'membership_status' && $value !== 'active') $status = '❌';
    if ($field === 'membership_plan_id' && empty($value)) $status = '❌';
    if ($field === 'draws_remaining' && $value <= 0) $status = '❌';
    if ($field === 'current_active_draw_id' && !empty($value)) $status = '❌';
    
    echo "<tr>";
    echo "<td><strong>{$field}</strong></td>";
    echo "<td>{$value}</td>";
    echo "<td>{$status} (Expected: {$expected})</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";

// Check active draws
echo "<h2>Active Draws:</h2>";
$activeDraws = LuckyDraw::where('status', 'active')
    ->where('end_date', '>', now())
    ->get();

if ($activeDraws->count() === 0) {
    echo "<p class='error'>❌ No active draws found!</p>";
} else {
    echo "<p class='success'>✅ Found {$activeDraws->count()} active draw(s)</p>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Name</th><th>Status</th><th>End Date</th><th>Action</th></tr>";
    
    foreach ($activeDraws as $draw) {
        $canJoin = $user->canJoinDraw($draw);
        $alreadyJoined = $user->luckyDrawParticipations()->where('draw_id', $draw->id)->exists();
        
        echo "<tr>";
        echo "<td>{$draw->id}</td>";
        echo "<td>{$draw->name}</td>";
        echo "<td>{$draw->status}</td>";
        echo "<td>{$draw->end_date->format('Y-m-d H:i')}</td>";
        echo "<td>";
        
        if ($alreadyJoined) {
            echo "<span class='success'>Already Joined</span>";
        } elseif ($canJoin) {
            echo "<span class='success'>Can Join</span>";
            echo "<br><a href='test-direct-join.php?draw_id={$draw->id}' style='color:blue;'>Test Direct Join</a>";
        } else {
            echo "<span class='error'>Cannot Join</span>";
        }
        
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";

// Check routes
echo "<h2>Routes Check:</h2>";
try {
    $indexRoute = route('public.lucky-draws.index');
    echo "<p class='success'>✅ Index Route: <a href='{$indexRoute}'>{$indexRoute}</a></p>";
    
    if ($activeDraws->count() > 0) {
        $firstDraw = $activeDraws->first();
        $joinRoute = route('public.lucky-draws.join', $firstDraw->id);
        echo "<p class='success'>✅ Join Route: {$joinRoute}</p>";
    }
} catch (\Exception $e) {
    echo "<p class='error'>❌ Route Error: {$e->getMessage()}</p>";
}

echo "<hr>";

// Check recent logs
echo "<h2>Recent Logs:</h2>";
$logFile = __DIR__ . '/../storage/logs/laravel-' . date('Y-m-d') . '.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $recentLines = array_slice($lines, -20);
    echo "<pre style='background:#f5f5f5;padding:10px;overflow:auto;max-height:300px;'>";
    foreach ($recentLines as $line) {
        if (strpos($line, 'Join Draw') !== false || strpos($line, 'ERROR') !== false) {
            echo htmlspecialchars($line);
        }
    }
    echo "</pre>";
} else {
    echo "<p class='error'>Log file not found</p>";
}

echo "<hr>";
echo "<p><a href='/lucky-draws'>Go to Lucky Draws Page</a></p>";
echo "<p><a href='check-logs.php'>View Full Logs</a></p>";
