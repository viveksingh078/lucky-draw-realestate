<?php
/**
 * Refresh Composer Autoload
 */

echo "<h2>Refreshing Autoload...</h2>";

// Try to include the controller directly
$controllerPath = __DIR__ . '/../platform/plugins/real-estate/src/Http/Controllers/MembershipPlanController.php';

if (file_exists($controllerPath)) {
    echo "✅ Controller file exists<br>";
    require_once $controllerPath;
    echo "✅ Controller loaded successfully<br>";
    
    if (class_exists('Botble\RealEstate\Http\Controllers\MembershipPlanController')) {
        echo "✅ Controller class is accessible<br>";
    } else {
        echo "❌ Controller class not found after loading<br>";
    }
} else {
    echo "❌ Controller file not found at: $controllerPath<br>";
}

echo "<hr>";
echo "<p>Now clear cache and try again:</p>";
echo "<p><a href='clear-cache.php'>Clear Cache</a></p>";
?>
