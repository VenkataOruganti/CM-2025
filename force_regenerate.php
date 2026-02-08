<?php
/**
 * Force Pattern Regeneration
 * Clears all cached pattern data and regenerates fresh
 */
session_start();

// Clear all pattern-related session data
$keysToDelete = [];
foreach ($_SESSION as $key => $value) {
    if (strpos($key, 'pattern') !== false ||
        strpos($key, 'measurements') !== false ||
        strpos($key, 'svg') !== false) {
        $keysToDelete[] = $key;
    }
}

foreach ($keysToDelete as $key) {
    unset($_SESSION[$key]);
}

// Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
}
clearstatcache(true);

$customerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 11;
$measurementId = isset($_GET['measurement_id']) ? (int)$_GET['measurement_id'] : 97;

echo "<h1>Pattern Regeneration</h1>";
echo "<p>✓ Cleared " . count($keysToDelete) . " session keys</p>";
echo "<p>✓ Cleared OPcache and file cache</p>";

echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li><a href='patterns/sariBlouses/sariBlouse3Tucks.php?customer_id={$customerId}&measurement_id={$measurementId}&mode=dev'>Step 1: View Pattern (Dev Mode)</a> - This regenerates with fresh calculations</li>";
echo "<li>After viewing, click 'Download PDF' or 'Download SVG' from that page</li>";
echo "</ol>";

echo "<h2>Direct Links (after Step 1):</h2>";
echo "<ul>";
echo "<li><a href='patterns/pdfGenerator.php?measurement_id={$measurementId}'>Generate PDF</a></li>";
echo "<li><a href='patterns/svgGenerator.php?measurement_id={$measurementId}'>Generate SVG</a></li>";
echo "</ul>";
?>
