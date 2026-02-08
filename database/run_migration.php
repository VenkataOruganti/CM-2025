<?php
/**
 * Run migration to update blouse pattern paths
 * Execute this file on the server to update the database
 */

// Load database config
require_once __DIR__ . '/../config/database.php';

echo "Running migration: Update Blouse Pattern Paths\n";
echo "================================================\n\n";

try {
    global $pdo;

    // Step 1: Show current state
    echo "Step 1: Current state of blouse patterns\n";
    echo "-----------------------------------------\n";
    $stmt = $pdo->query("SELECT id, title, preview_file, pdf_download_file FROM pattern_making_portfolio WHERE title LIKE '%Blouse%' OR code_page LIKE '%savi%'");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        echo "ID: {$row['id']}, Title: {$row['title']}\n";
        echo "  preview_file: {$row['preview_file']}\n";
        echo "  pdf_download_file: {$row['pdf_download_file']}\n\n";
    }

    // Step 2: Update 4 Tucks pattern
    echo "Step 2: Updating 4 Tucks patterns...\n";
    $stmt = $pdo->prepare("UPDATE pattern_making_portfolio SET preview_file = '../patterns/sariBlouses/sariBlouse4Tucks.php', pdf_download_file = '../patterns/pdfGenerator.php', svg_download_file = '../patterns/svgGenerator.php' WHERE title LIKE '%4%Tuck%' OR title LIKE '%4 Tuck%' OR title LIKE '%Four Tuck%'");
    $stmt->execute();
    echo "  Updated " . $stmt->rowCount() . " rows\n\n";

    // Step 3: Update 3 Dart/Tucks pattern
    echo "Step 3: Updating 3 Dart/Tucks patterns...\n";
    $stmt = $pdo->prepare("UPDATE pattern_making_portfolio SET preview_file = '../patterns/sariBlouses/sariBlouse3Tucks.php', pdf_download_file = '../patterns/pdfGenerator.php', svg_download_file = '../patterns/svgGenerator.php' WHERE (title LIKE '%3%Dart%' OR title LIKE '%3 Dart%' OR title LIKE '%Three Dart%' OR title LIKE '%3%Tuck%') AND title NOT LIKE '%4%'");
    $stmt->execute();
    echo "  Updated " . $stmt->rowCount() . " rows\n\n";

    // Step 4: Update generic Saree Blouse/Savi patterns
    echo "Step 4: Updating generic Saree Blouse/Savi patterns...\n";
    $stmt = $pdo->prepare("UPDATE pattern_making_portfolio SET preview_file = '../patterns/sariBlouses/sariBlouse4Tucks.php', pdf_download_file = '../patterns/pdfGenerator.php', svg_download_file = '../patterns/svgGenerator.php' WHERE (code_page LIKE '%savi%' OR title LIKE '%Saree Blouse%' OR title LIKE '%Sari Blouse%') AND title NOT LIKE '%3%Dart%' AND title NOT LIKE '%3%Tuck%' AND title NOT LIKE '%4%Tuck%'");
    $stmt->execute();
    echo "  Updated " . $stmt->rowCount() . " rows\n\n";

    // Step 5: Verify
    echo "Step 5: Verify updates\n";
    echo "----------------------\n";
    $stmt = $pdo->query("SELECT id, title, preview_file, pdf_download_file FROM pattern_making_portfolio WHERE title LIKE '%Blouse%' OR code_page LIKE '%savi%' ORDER BY title");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        echo "ID: {$row['id']}, Title: {$row['title']}\n";
        echo "  preview_file: {$row['preview_file']}\n";
        echo "  pdf_download_file: {$row['pdf_download_file']}\n\n";
    }

    echo "\n================================================\n";
    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
