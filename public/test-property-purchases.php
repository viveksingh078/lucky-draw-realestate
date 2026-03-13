<?php
/**
 * Test Property Purchases Admin Route
 */

echo "<h1>🏠 Testing Property Purchases Admin</h1>";

try {
    // Load Laravel
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $kernel->bootstrap();

    echo "<p>✅ Laravel loaded</p>";

    // Test if PropertyPurchase model exists
    try {
        $model = new \Botble\RealEstate\Models\PropertyPurchase();
        echo "<p>✅ PropertyPurchase model loaded</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ PropertyPurchase model error: " . $e->getMessage() . "</p>";
    }

    // Test database connection
    try {
        $count = \Illuminate\Support\Facades\DB::table('re_property_purchases')->count();
        echo "<p>✅ Database table accessible: {$count} records</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
    }

    // Test route exists
    try {
        $route = route('property-purchases.index');
        echo "<p>✅ Route exists: <a href='{$route}' target='_blank'>{$route}</a></p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Route error: " . $e->getMessage() . "</p>";
    }

    // Test view file exists
    $viewPath = base_path('platform/plugins/real-estate/resources/views/property-purchases/index.blade.php');
    if (file_exists($viewPath)) {
        echo "<p>✅ View file exists: {$viewPath}</p>";
    } else {
        echo "<p style='color: red;'>❌ View file missing: {$viewPath}</p>";
    }

    // Test controller method
    try {
        $controller = new \Botble\RealEstate\Http\Controllers\PropertyPurchaseController();
        echo "<p>✅ Controller loaded</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Controller error: " . $e->getMessage() . "</p>";
    }

    echo "<hr>";
    echo "<h2>🔧 Quick Fixes:</h2>";
    echo "<p><a href='/clear-all-cache.php'>Clear All Cache</a></p>";
    echo "<p><a href='/realestate/public/admin/real-estate/property-purchases'>Try Admin Route</a></p>";

} catch (\Exception $e) {
    echo "<p style='color: red;'>❌ Fatal Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>