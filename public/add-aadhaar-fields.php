<?php
echo "<!DOCTYPE html><html><head><title>Add Aadhaar Fields</title></head><body>";
echo "<h1>Adding Aadhaar Card Fields to Database</h1>";

try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $kernel->bootstrap();
    
    use Illuminate\Support\Facades\DB;
    
    echo "<h2>Running Database Updates...</h2>";
    
    // Check if columns already exist
    $columns = DB::select("SHOW COLUMNS FROM re_accounts LIKE 'aadhaar%'");
    
    if (count($columns) > 0) {
        echo "<p style='color: orange;'>⚠ Aadhaar columns already exist!</p>";
    } else {
        // Add Aadhaar fields
        DB::statement("ALTER TABLE `re_accounts` 
            ADD COLUMN `aadhaar_number` VARCHAR(12) NULL UNIQUE AFTER `pan_card_number`,
            ADD COLUMN `aadhaar_front_image` VARCHAR(255) NULL AFTER `aadhaar_number`,
            ADD COLUMN `aadhaar_back_image` VARCHAR(255) NULL AFTER `aadhaar_front_image`");
        
        echo "<p style='color: green;'>✅ Aadhaar fields added successfully!</p>";
        
        // Add index
        DB::statement("ALTER TABLE `re_accounts` ADD INDEX `idx_aadhaar_number` (`aadhaar_number`)");
        echo "<p style='color: green;'>✅ Aadhaar index created!</p>";
    }
    
    // Show table structure
    echo "<h3>Updated Table Structure:</h3>";
    $columns = DB::select("SHOW COLUMNS FROM re_accounts WHERE Field LIKE '%aadhaar%' OR Field LIKE '%pan%'");
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column->Field}</td>";
        echo "<td>{$column->Type}</td>";
        echo "<td>{$column->Null}</td>";
        echo "<td>{$column->Key}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><p style='color: green; font-size: 18px;'><strong>✅ All updates completed! Now clear cache: <a href='/clear-all-cache.php'>clear-all-cache.php</a></strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";
?>
