<?php

// Test script to verify join draw functionality
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Botble\RealEstate\Models\Account;
use Botble\RealEstate\Models\LuckyDraw;
use Botble\RealEstate\Models\MembershipPlan;

echo "<h2>Testing Join Draw Functionality</h2>";

// Test 1: Check if membership plans have draws_allowed
echo "<h3>1. Checking Membership Plans:</h3>";
$plans = MembershipPlan::all();
foreach ($plans as $plan) {
    echo "Plan: {$plan->name} - Draws Allowed: {$plan->draws_allowed}<br>";
}

// Test 2: Check active draws
echo "<h3>2. Checking Active Draws:</h3>";
$activeDraws = LuckyDraw::where('status', 'active')
    ->where('end_date', '>', now())
    ->get();
echo "Active Draws Count: " . $activeDraws->count() . "<br>";
foreach ($activeDraws as $draw) {
    echo "- {$draw->name} (ID: {$draw->id})<br>";
}

// Test 3: Check approved accounts with membership
echo "<h3>3. Checking Approved Accounts:</h3>";
$accounts = Account::where('account_status', 'approved')
    ->whereNotNull('membership_plan_id')
    ->where('membership_status', 'active')
    ->get();
echo "Approved Accounts with Active Membership: " . $accounts->count() . "<br>";
foreach ($accounts as $account) {
    echo "- {$account->name} (Email: {$account->email})<br>";
    echo "  Membership Plan: " . ($account->membershipPlan ? $account->membershipPlan->name : 'None') . "<br>";
    echo "  Draws Used: {$account->draws_used}<br>";
    echo "  Draws Remaining: {$account->draws_remaining}<br>";
    echo "  Current Active Draw: " . ($account->current_active_draw_id ? $account->current_active_draw_id : 'None') . "<br>";
    echo "  Can Join Draw: " . ($account->draws_remaining > 0 && !$account->current_active_draw_id ? 'YES' : 'NO') . "<br>";
    echo "<br>";
}

// Test 4: Check routes
echo "<h3>4. Checking Routes:</h3>";
$routes = [
    'public.lucky-draws.index' => route('public.lucky-draws.index'),
    'public.lucky-draws.winners' => route('public.lucky-draws.winners'),
];

if ($activeDraws->count() > 0) {
    $firstDraw = $activeDraws->first();
    $routes['public.lucky-draws.show'] = route('public.lucky-draws.show', $firstDraw->id);
    $routes['public.lucky-draws.join'] = route('public.lucky-draws.join', $firstDraw->id);
}

foreach ($routes as $name => $url) {
    echo "{$name}: <a href='{$url}' target='_blank'>{$url}</a><br>";
}

// Test 5: Check if Account model methods exist
echo "<h3>5. Checking Account Model Methods:</h3>";
$testAccount = Account::where('account_status', 'approved')->first();
if ($testAccount) {
    echo "Test Account: {$testAccount->name}<br>";
    echo "- hasActiveDraw() method exists: " . (method_exists($testAccount, 'hasActiveDraw') ? 'YES' : 'NO') . "<br>";
    echo "- canJoinDraw() method exists: " . (method_exists($testAccount, 'canJoinDraw') ? 'YES' : 'NO') . "<br>";
    echo "- joinDraw() method exists: " . (method_exists($testAccount, 'joinDraw') ? 'YES' : 'NO') . "<br>";
    echo "- completeDraw() method exists: " . (method_exists($testAccount, 'completeDraw') ? 'YES' : 'NO') . "<br>";
    echo "- currentActiveDraw() method exists: " . (method_exists($testAccount, 'currentActiveDraw') ? 'YES' : 'NO') . "<br>";
    echo "- initializeDrawCredits() method exists: " . (method_exists($testAccount, 'initializeDrawCredits') ? 'YES' : 'NO') . "<br>";
} else {
    echo "No approved accounts found for testing.<br>";
}

echo "<h3>✅ Test Complete!</h3>";
echo "<p><a href='/lucky-draws'>Go to Lucky Draws Page</a></p>";
