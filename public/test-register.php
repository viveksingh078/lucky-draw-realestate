<?php
/**
 * Test Registration Debug Script
 * Access: https://sspl20.com/realestate/test-register.php
 */

// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "<h2>Registration Debug Test</h2>";

// Test 1: Check membership_plans table
echo "<h3>1. Membership Plans Table</h3>";
try {
    if (Schema::hasTable('membership_plans')) {
        echo "✅ Table exists<br>";
        $plans = DB::table('membership_plans')->get();
        echo "Plans count: " . $plans->count() . "<br>";
        foreach ($plans as $plan) {
            echo "- {$plan->name}: Rs.{$plan->price}<br>";
        }
    } else {
        echo "❌ Table does NOT exist<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 2: Check re_accounts table columns
echo "<h3>2. Re_Accounts Table Columns</h3>";
try {
    $columns = [
        'membership_plan_id',
        'membership_start_date', 
        'membership_end_date',
        'membership_status',
        'pan_card_number',
        'pan_card_file',
        'payment_utr_number',
        'payment_screenshot',
        'account_status',
    ];
    
    foreach ($columns as $col) {
        if (Schema::hasColumn('re_accounts', $col)) {
            echo "✅ {$col} exists<br>";
        } else {
            echo "❌ {$col} MISSING<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 3: Check if Account model fillable is correct
echo "<h3>3. Account Model Check</h3>";
try {
    $account = new \Botble\RealEstate\Models\Account();
    $fillable = $account->getFillable();
    echo "Fillable fields: " . implode(', ', $fillable) . "<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 4: Try creating a test account
echo "<h3>4. Test Account Creation (Dry Run)</h3>";
try {
    $testData = [
        'first_name' => 'Test',
        'last_name' => 'User',
        'username' => 'testuser_' . time(),
        'email' => 'test_' . time() . '@test.com',
        'phone' => '9999999999',
        'password' => bcrypt('password123'),
        'membership_plan_id' => 1,
        'membership_start_date' => now(),
        'membership_end_date' => now()->addDays(365),
        'membership_status' => 'pending',
        'pan_card_number' => 'ABCDE1234F',
        'pan_card_file' => null,
        'payment_utr_number' => '123456789012',
        'payment_screenshot' => null,
        'account_status' => 'pending',
    ];
    
    echo "Test data prepared successfully<br>";
    echo "<pre>" . print_r($testData, true) . "</pre>";
    
    // Uncomment below to actually create test account
    // $account = \Botble\RealEstate\Models\Account::create($testData);
    // echo "✅ Account created with ID: " . $account->id;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr><p>Debug complete. Check above for any ❌ errors.</p>";
