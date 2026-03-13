<?php
/**
 * Cache Clear Script
 * Open this file in browser: http://yourdomain.com/clear-cache.php
 * After clearing cache, DELETE this file for security!
 */

echo "<h1>Cache Clearing...</h1>";
echo "<pre>";

// Clear Laravel cache directories
$cachePaths = [
    '../bootstrap/cache/config.php',
    '../bootstrap/cache/routes.php',
    '../bootstrap/cache/services.php',
    '../bootstrap/cache/packages.php',
    '../storage/framework/cache',
    '../storage/framework/views',
];

foreach ($cachePaths as $path) {
    $fullPath = __DIR__ . '/' . $path;
    
    if (is_file($fullPath)) {
        if (unlink($fullPath)) {
            echo "✓ Deleted: $path\n";
        } else {
            echo "✗ Failed to delete: $path\n";
        }
    } elseif (is_dir($fullPath)) {
        $files = glob($fullPath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "✓ Cleared directory: $path\n";
    } else {
        echo "- Not found: $path\n";
    }
}

echo "\n<strong>Cache cleared successfully!</strong>\n";
echo "\n<span style='color: red;'>IMPORTANT: Delete this file (clear-cache.php) now for security!</span>\n";
echo "</pre>";

echo "<hr>";
echo "<h2>Now try again!</h2>";
echo "<a href='/admin/real-estate/membership-plans'>Go to Membership Plans</a> | ";
echo "<a href='/admin/real-estate/accounts'>Go to Accounts</a>";
?>
