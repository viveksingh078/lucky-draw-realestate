<?php
/**
 * Manual Reward Draw Processing Script
 * 
 * This script can be run manually or via cron job to process reward draws
 * URL: /process-lucky-draws.php
 * 
 * Cron Job Example (every 5 minutes):
 * */5 * * * * /usr/bin/php /path/to/your/site/public/process-lucky-draws.php


// Security check - only allow from localhost or specific IPs
$allowedIPs = ['127.0.0.1', '::1', 'localhost'];
$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if (!in_array($clientIP, $allowedIPs) && !isset($_GET['force'])) {
    http_response_code(403);
    die('Access denied. This script can only be run from localhost.');
}

echo "<h1>🎯 Reward Draw Processing</h1>";
echo "<p>Started at: " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

try {
    // Load Laravel
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

    // Load necessary classes
    require_once __DIR__.'/../platform/plugins/real-estate/src/Services/LuckyDrawService.php';
    require_once __DIR__.'/../platform/plugins/real-estate/src/Models/LuckyDraw.php';
    require_once __DIR__.'/../platform/plugins/real-estate/src/Models/LuckyDrawParticipant.php';
    require_once __DIR__.'/../platform/plugins/real-estate/src/Models/DummyWinner.php';

    $service = new \Botble\RealEstate\Services\LuckyDrawService();

    echo "<h2>📅 Step 1: Activating Upcoming Draws</h2>";
    $activated = $service->autoActivateDraws();
    
    if ($activated > 0) {
        echo "<p style='color: green;'>✅ Activated {$activated} draw(s)</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ No draws to activate</p>";
    }

    echo "<h2>🏆 Step 2: Executing Completed Draws</h2>";
    $executed = $service->autoExecuteDraws();
    
    if ($executed > 0) {
        echo "<p style='color: green;'>🎉 Executed {$executed} draw(s) and selected winners!</p>";
        
        // Show statistics
        echo "<h3>📊 Current Statistics:</h3>";
        $stats = $service->getDrawStatistics();
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Metric</th><th>Value</th></tr>";
        echo "<tr><td>Total Draws</td><td>{$stats['total_draws']}</td></tr>";
        echo "<tr><td>Active Draws</td><td>{$stats['active_draws']}</td></tr>";
        echo "<tr><td>Completed Draws</td><td>{$stats['completed_draws']}</td></tr>";
        echo "<tr><td>Total Participants</td><td>{$stats['total_participants']}</td></tr>";
        echo "<tr><td>Total Revenue</td><td>₹" . number_format($stats['total_revenue'], 2) . "</td></tr>";
        echo "<tr><td>Real Winners</td><td>{$stats['real_winners']}</td></tr>";
        echo "<tr><td>Dummy Winners</td><td>{$stats['dummy_winners']}</td></tr>";
        echo "<tr><td>Net Profit/Loss</td><td>₹" . number_format($stats['total_profit'] + $stats['total_loss'], 2) . "</td></tr>";
        echo "</table>";
        
    } else {
        echo "<p style='color: blue;'>ℹ️ No draws ready for execution</p>";
    }

    echo "<hr>";
    echo "<p style='color: green; font-weight: bold;'>✅ Processing completed successfully!</p>";
    echo "<p>Completed at: " . date('Y-m-d H:i:s') . "</p>";

} catch (\Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    
    // Log error
    error_log("Reward Draw Processing Error: " . $e->getMessage());
}

echo "<hr>";
echo "<p><a href='/admin/real-estate/lucky-draws'>Go to Admin Panel</a> | ";
echo "<a href='/lucky-draws'>View Public Draws</a></p>";
?>