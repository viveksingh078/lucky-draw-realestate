<?php
/**
 * Test Properties Table
 * Check if properties are available for reward draws
 */

echo "<h1>🏠 Properties Test</h1>";

try {
    // Database connection
    $host = 'localhost';
    $dbname = 'sspl2icu_realestate';
    $username = 'root';
    $password = '';

    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>✅ Database Connected</h2>";
    
    // Check if re_properties table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 're_properties'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Table 're_properties' exists</p>";
        
        // Get properties count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM re_properties");
        $count = $stmt->fetch(PDO::FETCH_OBJ);
        echo "<p>📊 Total Properties: <strong>{$count->count}</strong></p>";
        
        // Get approved properties
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM re_properties WHERE moderation_status = 'approved'");
        $approved = $stmt->fetch(PDO::FETCH_OBJ);
        echo "<p>✅ Approved Properties: <strong>{$approved->count}</strong></p>";
        
        if ($approved->count > 0) {
            // Show sample properties
            $stmt = $pdo->query("SELECT id, name, price, location FROM re_properties WHERE moderation_status = 'approved' LIMIT 5");
            $properties = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            echo "<h3>📋 Sample Properties:</h3>";
            echo "<table border='1' cellpadding='5' cellspacing='0'>";
            echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Location</th></tr>";
            foreach ($properties as $property) {
                echo "<tr>";
                echo "<td>{$property->id}</td>";
                echo "<td>{$property->name}</td>";
                echo "<td>₹" . number_format($property->price, 2) . "</td>";
                echo "<td>{$property->location}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<p style='color: green; font-weight: bold;'>🎉 Ready to create reward draws!</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ No approved properties found. Please approve some properties first.</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Table 're_properties' not found</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='/admin/real-estate/lucky-draws/create'>Try Creating Reward Draw</a></p>";
?>