<?php
echo "<!DOCTYPE html><html><head><title>Quick Fix</title></head><body>";
echo "<h1>Quick Database Fixes</h1>";

try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $kernel->bootstrap();
    
    use Illuminate\Support\Facades\DB;
    
    echo "<h2>Running Fixes...</h2>";
    
    // Fix 1: Update Gold plan to 2 draws
    DB::statement("UPDATE membership_plans SET draws_allowed = 2, credit_value = ROUND(price / 2, 2) WHERE slug = 'gold'");
    echo "<p style='color: green;'>✅ Gold plan updated to 2 draws</p>";
    
    // Fix 2: Set wallet balance for approved users
    DB::statement("
        UPDATE re_accounts a
        JOIN membership_plans p ON a.membership_plan_id = p.id
        SET 
            a.wallet_balance = p.price,
            a.wallet_on_hold = 0,
            a.wallet_used = 0
        WHERE a.account_status = 'approved' 
        AND a.membership_status = 'active'
        AND (a.wallet_balance IS NULL OR a.wallet_balance = 0)
    ");
    echo "<p style='color: green;'>✅ Wallet balances updated</p>";
    
    // Fix 3: Create property purchases table
    try {
        DB::statement("
            CREATE TABLE IF NOT EXISTS `re_property_purchases` (
              `id` bigint unsigned NOT NULL AUTO_INCREMENT,
              `account_id` bigint unsigned NOT NULL,
              `property_id` bigint unsigned NOT NULL,
              `property_name` varchar(255) NOT NULL,
              `property_location` varchar(255) DEFAULT NULL,
              `property_price` decimal(15,2) NOT NULL,
              `gst_amount` decimal(15,2) NOT NULL DEFAULT 0,
              `subtotal` decimal(15,2) NOT NULL,
              `lost_draw_discount` decimal(15,2) NOT NULL DEFAULT 0,
              `wallet_discount` decimal(15,2) NOT NULL DEFAULT 0,
              `total_discount` decimal(15,2) NOT NULL DEFAULT 0,
              `final_amount` decimal(15,2) NOT NULL,
              `status` varchar(60) NOT NULL DEFAULT 'pending',
              `admin_notes` text,
              `approved_at` timestamp NULL DEFAULT NULL,
              `approved_by` bigint unsigned DEFAULT NULL,
              `rejected_at` timestamp NULL DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `re_property_purchases_account_id_index` (`account_id`),
              KEY `re_property_purchases_property_id_index` (`property_id`),
              KEY `re_property_purchases_status_index` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "<p style='color: green;'>✅ Property purchases table created</p>";
        
        // Check if table exists and show count
        $tableExists = DB::select("SHOW TABLES LIKE 're_property_purchases'");
        if ($tableExists) {
            $count = DB::table('re_property_purchases')->count();
            echo "<p style='color: blue;'>📊 Property purchases table exists with {$count} records</p>";
        } else {
            echo "<p style='color: red;'>❌ Property purchases table creation failed</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Property purchases table error: " . $e->getMessage() . "</p>";
    }
    
    // Show results
    echo "<h3>Membership Plans:</h3>";
    $plans = DB::table('membership_plans')->select('name', 'price', 'draws_allowed', 'credit_value')->orderBy('price')->get();
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Plan</th><th>Price</th><th>Draws</th><th>Per Draw</th></tr>";
    foreach ($plans as $plan) {
        echo "<tr><td>{$plan->name}</td><td>₹" . number_format($plan->price, 0) . "</td><td>{$plan->draws_allowed}</td><td>₹" . number_format($plan->credit_value, 0) . "</td></tr>";
    }
    echo "</table>";
    
    echo "<h3>Approved Users:</h3>";
    $users = DB::table('re_accounts')
        ->join('membership_plans', 're_accounts.membership_plan_id', '=', 'membership_plans.id')
        ->where('re_accounts.account_status', 'approved')
        ->select('re_accounts.first_name', 're_accounts.last_name', 'membership_plans.name as plan', 're_accounts.wallet_balance', 're_accounts.wallet_on_hold', 're_accounts.draws_remaining')
        ->get();
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>User</th><th>Plan</th><th>Wallet Balance</th><th>On Hold</th><th>Draws Left</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user->first_name} {$user->last_name}</td>";
        echo "<td>{$user->plan}</td>";
        echo "<td>₹" . number_format($user->wallet_balance, 0) . "</td>";
        echo "<td>₹" . number_format($user->wallet_on_hold, 0) . "</td>";
        echo "<td>{$user->draws_remaining}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test PropertyPurchase model
    echo "<h3>Property Purchase System Test:</h3>";
    try {
        $purchaseCount = DB::table('re_property_purchases')->count();
        echo "<p style='color: green;'>✅ PropertyPurchase table accessible: {$purchaseCount} records</p>";
        
        // Test if we can access the admin route
        echo "<p style='color: blue;'>📋 Admin route: <a href='/realestate/public/admin/real-estate/property-purchases' target='_blank'>Property Purchases Admin</a></p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ PropertyPurchase model error: " . $e->getMessage() . "</p>";
    }
    
    echo "<br><p style='color: green; font-size: 18px;'><strong>✅ All fixes applied! Now clear cache: <a href='/clear-all-cache.php'>clear-all-cache.php</a></strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";
?>
