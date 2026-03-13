<?php
/**
 * Create required folders for file uploads
 */

$folders = [
    __DIR__ . '/storage/accounts',
    __DIR__ . '/storage/accounts/kyc',
    __DIR__ . '/storage/accounts/payments',
];

echo "<h2>Creating Upload Folders</h2>";

foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        if (mkdir($folder, 0755, true)) {
            echo "✅ Created: {$folder}<br>";
        } else {
            echo "❌ Failed to create: {$folder}<br>";
        }
    } else {
        echo "✓ Already exists: {$folder}<br>";
    }
}

echo "<hr><p>Done! Now try registration again.</p>";
echo "<p><a href='/realestate/public/register'>Go to Register Page</a></p>";
