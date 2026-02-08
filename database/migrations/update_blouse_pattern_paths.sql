-- ============================================================================
-- Migration: Update All Blouse Pattern File Paths
-- Created: 2026-01-25
-- Description: Updates pattern_making_portfolio table to point all saree/sari
--              blouse patterns (3 Dart, 4 Tucks, etc.) to use pdfGenerator.php
-- ============================================================================

-- IMPORTANT: Run this migration on the LIVE database to fix PDF generation

-- ============================================================================
-- Step 1: Show current state of blouse patterns
-- ============================================================================
SELECT
    id,
    title,
    code_page,
    preview_file,
    pdf_download_file,
    svg_download_file,
    status
FROM pattern_making_portfolio
WHERE
    title LIKE '%Blouse%'
    OR title LIKE '%blouse%'
    OR code_page LIKE '%savi%'
    OR code_page LIKE '%blouse%';

-- ============================================================================
-- Step 2: Update "Sari Blouse - 4 Tucks" pattern
-- Uses the sariBlouses folder structure
-- ============================================================================
UPDATE pattern_making_portfolio
SET
    preview_file = '../patterns/sariBlouses/sariBlouse4Tucks.php',
    pdf_download_file = '../patterns/pdfGenerator.php',
    svg_download_file = '../patterns/svgGenerator.php'
WHERE
    title LIKE '%4%Tuck%'
    OR title LIKE '%4 Tuck%'
    OR title LIKE '%Four Tuck%';

-- ============================================================================
-- Step 3: Update "Saree Blouse - 3 Dart" pattern (if exists separately)
-- Uses the sariBlouses folder structure
-- ============================================================================
UPDATE pattern_making_portfolio
SET
    preview_file = '../patterns/sariBlouses/sariBlouse3Tucks.php',
    pdf_download_file = '../patterns/pdfGenerator.php',
    svg_download_file = '../patterns/svgGenerator.php'
WHERE
    (title LIKE '%3%Dart%'
    OR title LIKE '%3 Dart%'
    OR title LIKE '%Three Dart%'
    OR title LIKE '%3%Tuck%')
    AND title NOT LIKE '%4%';

-- ============================================================================
-- Step 4: Update generic "Saree Blouse" or "Savi" pattern
-- This is for any entry that doesn't match specific variants
-- ============================================================================
UPDATE pattern_making_portfolio
SET
    preview_file = '../patterns/sariBlouses/sariBlouse4Tucks.php',
    pdf_download_file = '../patterns/pdfGenerator.php',
    svg_download_file = '../patterns/svgGenerator.php'
WHERE
    (code_page LIKE '%savi%'
    OR title LIKE '%Saree Blouse%'
    OR title LIKE '%Sari Blouse%')
    AND title NOT LIKE '%3%Dart%'
    AND title NOT LIKE '%3%Tuck%'
    AND title NOT LIKE '%4%Tuck%';

-- ============================================================================
-- Step 5: Verify the updates
-- ============================================================================
SELECT
    id,
    title,
    code_page,
    preview_file,
    pdf_download_file,
    svg_download_file,
    status
FROM pattern_making_portfolio
WHERE
    title LIKE '%Blouse%'
    OR title LIKE '%blouse%'
    OR code_page LIKE '%savi%'
    OR code_page LIKE '%blouse%'
ORDER BY title;

-- ============================================================================
-- Expected Results:
--
-- For "Sari Blouse - 4 Tucks":
--   preview_file: ../patterns/sariBlouses/sariBlouse4Tucks.php
--   pdf_download_file: ../patterns/pdfGenerator.php
--
-- For "Saree Blouse - 3 Dart" (or 3 Tucks):
--   preview_file: ../patterns/sariBlouses/sariBlouse3Tucks.php
--   pdf_download_file: ../patterns/pdfGenerator.php
--
-- The pdfGenerator.php will automatically handle both formats and generate
-- proper PDFs with summary sheets, tiling, CM logo, etc.
-- ============================================================================
