-- ============================================================================
-- Migration: Update Saree Blouse Pattern File Paths
-- Created: 2026-01-15
-- Description: Updates pattern_making_portfolio table to point to new
--              saree blouse pattern files with CM logo integration
-- ============================================================================

-- Update Saree Blouse (Savi) pattern to use new file structure
-- This includes the new sariBlouse.php files with:
-- - Data/Logic/Presentation separation
-- - Session storage
-- - CM logo integration in PDF
-- - SVG ZIP export
-- - Dashboard integration

UPDATE pattern_making_portfolio
SET
    preview_file = 'patterns/saree_blouses/sariBlouse/sariBlouse.php',
    pdf_download_file = 'patterns/saree_blouses/sariBlouse/sariBlouse_pdf.php',
    svg_download_file = 'patterns/saree_blouses/sariBlouse/sariBlouse_svg.php'
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
-- preview_file should be: patterns/saree_blouses/sariBlouse/sariBlouse.php
-- pdf_download_file should be: patterns/saree_blouses/sariBlouse/sariBlouse_pdf.php
-- svg_download_file should be: patterns/saree_blouses/sariBlouse/sariBlouse_svg.php
