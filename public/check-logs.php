<?php
// Simple log viewer
$logFile = __DIR__ . '/../storage/logs/laravel-' . date('Y-m-d') . '.log';

if (!file_exists($logFile)) {
    die('Log file not found: ' . $logFile);
}

// Get last 100 lines
$lines = file($logFile);
$lastLines = array_slice($lines, -100);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Laravel Logs - Last 100 Lines</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        h1 { color: #4ec9b0; }
        .log-line { padding: 5px; border-bottom: 1px solid #333; }
        .error { color: #f48771; }
        .warning { color: #dcdcaa; }
        .info { color: #4fc1ff; }
        .refresh { position: fixed; top: 10px; right: 10px; padding: 10px 20px; background: #007acc; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <a href="?refresh=1" class="refresh">Refresh Logs</a>
    <h1>Laravel Logs - <?php echo date('Y-m-d'); ?></h1>
    <p>Last 100 lines from: <?php echo $logFile; ?></p>
    <hr>
    <div class="logs">
        <?php foreach ($lastLines as $line): ?>
            <?php
            $class = '';
            if (strpos($line, 'ERROR') !== false) $class = 'error';
            elseif (strpos($line, 'WARNING') !== false) $class = 'warning';
            elseif (strpos($line, 'INFO') !== false) $class = 'info';
            ?>
            <div class="log-line <?php echo $class; ?>"><?php echo htmlspecialchars($line); ?></div>
        <?php endforeach; ?>
    </div>
    <script>
        // Auto scroll to bottom
        window.scrollTo(0, document.body.scrollHeight);
    </script>
</body>
</html>
