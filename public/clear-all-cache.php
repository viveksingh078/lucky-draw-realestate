<?php
/**
 * Clear All Cache - Run this file once to clear all caches
 * URL: /clear-all-cache.php
 */

echo "<h1>🧹 Clearing All Caches...</h1>";

try {
    // Load Laravel
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

    echo "<p>✅ Laravel loaded</p>";

    // Clear various caches
    Artisan::call('cache:clear');
    echo "<p>✅ Application cache cleared</p>";

    Artisan::call('config:clear');
    echo "<p>✅ Config cache cleared</p>";

    Artisan::call('route:clear');
    echo "<p>✅ Route cache cleared</p>";

    Artisan::call('view:clear');
    echo "<p>✅ View cache cleared</p>";

    // Clear compiled files
    if (file_exists(storage_path('framework/cache/data'))) {
        $files = glob(storage_path('framework/cache/data/*'));
        foreach($files as $file) {
            if(is_file($file)) {
                @unlink($file);
            }
        }
        echo "<p>✅ Framework cache cleared</p>";
    }

    // Clear view compiled files
    if (file_exists(storage_path('framework/views'))) {
        $files = glob(storage_path('framework/views/*'));
        foreach($files as $file) {
            if(is_file($file)) {
                @unlink($file);
            }
        }
        echo "<p>✅ Compiled views cleared</p>";
    }

    echo "<hr>";
    echo "<h2 style='color: green;'>✅ All caches cleared successfully!</h2>";
    echo "<p><a href='/lucky-draws'>Go to Reward Draws</a> | <a href='/'>Go to Home</a></p>";

} catch (\Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
