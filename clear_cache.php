<?php
/**
 * OPcache Clear Utility
 * Access: /clear_cache.php?key=cm2025clear
 */

// Simple security key
if (!isset($_GET['key']) || $_GET['key'] !== 'cm2025clear') {
    die('Access denied. Use ?key=cm2025clear');
}

echo "<h1>Cache Clearing Utility</h1>";

// 1. Clear OPcache
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "<p style='color:green'>✓ OPcache cleared successfully!</p>";
    } else {
        echo "<p style='color:orange'>⚠ OPcache reset returned false</p>";
    }
} else {
    echo "<p style='color:gray'>OPcache not available</p>";
}

// 2. Clear realpath cache
clearstatcache(true);
echo "<p style='color:green'>✓ Realpath cache cleared!</p>";

// 3. Invalidate specific files
$files = [
    __DIR__ . '/includes/deepNeck.php',
    __DIR__ . '/patterns/sariBlouses/sariBlouseBack.php',
    __DIR__ . '/patterns/sariBlouses/sariBlouse3TFront.php',
    __DIR__ . '/patterns/sariBlouses/sariBlouse3Tucks.php',
    __DIR__ . '/patterns/sariBlouses/patternConfig.php',
    __DIR__ . '/patterns/sariBlouses/sariSleeve.php',
];

echo "<h2>Invalidating Individual Files:</h2>";
foreach ($files as $file) {
    if (file_exists($file)) {
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
            echo "<p>✓ Invalidated: " . basename($file) . " (modified: " . date('Y-m-d H:i:s', filemtime($file)) . ")</p>";
        }
    } else {
        echo "<p style='color:red'>✗ File not found: " . basename($file) . "</p>";
    }
}

// 4. Show OPcache status
echo "<h2>OPcache Status:</h2>";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status(false);
    if ($status) {
        echo "<pre>";
        echo "Enabled: " . ($status['opcache_enabled'] ? 'Yes' : 'No') . "\n";
        echo "Cached scripts: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
        echo "Cache hits: " . $status['opcache_statistics']['hits'] . "\n";
        echo "Cache misses: " . $status['opcache_statistics']['misses'] . "\n";
        echo "</pre>";
    }
}

echo "<h2>Done! Now test your patterns:</h2>";
echo "<p><a href='/patterns/sariBlouses/sariBlouse3Tucks.php?customer_id=11&measurement_id=97&mode=dev'>Test Pattern (Dev Mode)</a></p>";
echo "<p><a href='/debug_armhole.php?customer_id=11&measurement_id=97'>Run Diagnostics</a></p>";
?>
