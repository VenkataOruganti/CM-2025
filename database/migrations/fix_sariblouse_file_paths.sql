-- ============================================================================
-- Migration: Fix Saree Blouse Pattern File Paths (Relative to /pages/)
-- Created: 2026-01-15
-- Description: Updates paths to be relative from /pages/ directory since
--              pattern-preview.php is located in /pages/
-- ============================================================================

-- Update Saree Blouse pattern with correct relative paths
-- Paths must be relative to /pages/ directory where pattern-preview.php is located

UPDATE pattern_making_portfolio
SET
    preview_file = '../patterns/saree_blouses/sariBlouse/sariBlouse.php',
    pdf_download_file = '../patterns/saree_blouses/sariBlouse/sariBlouse_pdf.php',
    svg_download_file = '../patterns/saree_blouses/sariBlouse/sariBlouse_svg.php'
WHERE
    code_page LIKE '%savi%'
    OR title LIKE '%Saree%Blouse%'
    OR title LIKE '%Sari%Blouse%'
LIMIT 1;

-- Verify the update
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
    code_page LIKE '%savi%'
    OR title LIKE '%Saree%Blouse%'
    OR title LIKE '%Sari%Blouse%';

-- Expected result:
-- preview_file should be: ../patterns/saree_blouses/sariBlouse/sariBlouse.php
-- pdf_download_file should be: ../patterns/saree_blouses/sariBlouse/sariBlouse_pdf.php
-- svg_download_file should be: ../patterns/saree_blouses/sariBlouse/sariBlouse_svg.php

-- Note: The '../' prefix is needed because pattern-preview.php is in /pages/
-- and the patterns folder is at the root level
