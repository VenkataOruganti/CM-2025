<?php
/**
 * Session and Cache Clear Utility
 * Clears PHP sessions and OPcache to force fresh calculations
 */

session_start();

echo "<h1>Session & Cache Clear</h1>";

// Show current session data before clearing
echo "<h2>Current Session Data:</h2>";
echo "<pre>";
if (isset($_SESSION['measurements'])) {
    echo "qBust: " . ($_SESSION['measurements']['qBust'] ?? 'not set') . "\n";
    echo "qWaist: " . ($_SESSION['measurements']['qWaist'] ?? 'not set') . "\n";
    echo "armhole: " . ($_SESSION['measurements']['armhole'] ?? 'not set') . "\n";
    echo "targetArmhole: " . ($_SESSION['measurements']['targetArmhole'] ?? 'not set') . "\n";
}
echo "</pre>";

// Clear session
$_SESSION = [];
session_destroy();
echo "<p style='color:green'>✓ Session cleared!</p>";

// Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<p style='color:green'>✓ OPcache cleared!</p>";
}

// Clear realpath cache
clearstatcache(true);
echo "<p style='color:green'>✓ Realpath cache cleared!</p>";

// Delete any session files for this user
echo "<p>Session ID was: " . session_id() . "</p>";

echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li><a href='/patterns/sariBlouses/sariBlouse3Tucks.php?customer_id=11&measurement_id=97&mode=dev'>Test Pattern (Dev Mode)</a> - This will create a fresh session with new calculations</li>";
echo "</ol>";

echo "<h2>Expected Values for measurement_id=97:</h2>";
echo "<ul>";
echo "<li>Armhole from DB: 17.5\"</li>";
echo "<li>Target (half): 8.75\"</li>";
echo "<li>qBust (bust/4, no ease): 10\" (for 40\" bust)</li>";
echo "</ul>";
?>
