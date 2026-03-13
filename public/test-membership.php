<?php
/**
 * Test Membership Plans
 * Open: http://yourdomain.com/test-membership.php
 * DELETE this file after testing!
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h1>Membership Plans Test</h1>";
echo "<pre>";

try {
    // Check if table exists
    $tableExists = DB::select("SHOW TABLES LIKE 'membership_plans'");
    
    if (empty($tableExists)) {
        echo "❌ ERROR: 'membership_plans' table does NOT exist!\n\n";
        echo "You need to run the SQL file: add_membership_kyc_columns.sql\n";
        echo "Go to phpMyAdmin and run that SQL file.\n";
    } else {
        echo "✓ Table 'membership_plans' exists\n\n";
        
        // Get plans
        $plans = DB::table('membership_plans')->get();
        
        if ($plans->isEmpty()) {
            echo "❌ No membership plans found in database!\n";
            echo "Run the INSERT queries from add_membership_kyc_columns.sql\n";
        } else {
            echo "✓ Found " . count($plans) . " membership plans:\n\n";
            foreach ($plans as $plan) {
                echo "ID: {$plan->id}\n";
                echo "Name: {$plan->name}\n";
                echo "Price: ₹{$plan->price}\n";
                echo "Active: " . ($plan->is_active ? 'Yes' : 'No') . "\n";
                echo "---\n";
            }
        }
    }
    
    // Check if columns exist in re_accounts
    echo "\n\nChecking re_accounts table columns:\n";
    $columns = DB::select("SHOW COLUMNS FROM re_accounts LIKE 'membership_plan_id'");
    
    if (empty($columns)) {
        echo "❌ Column 'membership_plan_id' does NOT exist in re_accounts table!\n";
        echo "You need to run the ALTER TABLE queries from add_membership_kyc_columns.sql\n";
    } else {
        echo "✓ Column 'membership_plan_id' exists in re_accounts table\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo "<hr>";
echo "<p style='color: red;'><strong>DELETE this file (test-membership.php) after testing!</strong></p>";
?>
