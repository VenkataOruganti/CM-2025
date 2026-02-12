<?php
/**
 * Clear PHP OPcache - Run this once then delete
 */

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully!\n";
} else {
    echo "OPcache is not enabled.\n";
}

// Also clear any file stat cache
clearstatcache(true);
echo "File stat cache cleared.\n";

echo "\nNow refresh your browser (Ctrl+Shift+R or Cmd+Shift+R)\n";
echo "\nDELETE THIS FILE AFTER USE!\n";
