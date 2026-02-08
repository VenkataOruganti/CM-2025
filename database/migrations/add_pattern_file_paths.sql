-- Migration: Add pattern file paths to pattern_making_portfolio table
-- This allows storing preview, PDF, and SVG download file paths for each pattern
-- Date: 2026-01-06

-- Add preview_file column (full path to preview PHP file)
ALTER TABLE pattern_making_portfolio
ADD COLUMN IF NOT EXISTS preview_file VARCHAR(255) DEFAULT NULL
COMMENT 'Full path to pattern preview file (e.g., pattern-studio/savi/saviComplete.php)';

-- Add pdf_download_file column (full path to PDF download PHP file)
ALTER TABLE pattern_making_portfolio
ADD COLUMN IF NOT EXISTS pdf_download_file VARCHAR(255) DEFAULT NULL
COMMENT 'Full path to PDF download file (e.g., pattern-studio/savi/saviDownloadPdf.php)';

-- Add svg_download_file column (full path to SVG download PHP file)
ALTER TABLE pattern_making_portfolio
ADD COLUMN IF NOT EXISTS svg_download_file VARCHAR(255) DEFAULT NULL
COMMENT 'Full path to SVG download file (e.g., pattern-studio/savi/saviDownloadSvg.php)';

-- Update existing patterns with their file paths
-- Savi pattern (assuming id exists, update based on title or code_page)
UPDATE pattern_making_portfolio
SET preview_file = 'pattern-studio/savi/saviComplete.php',
    pdf_download_file = 'pattern-studio/savi/saviDownloadPdf.php',
    svg_download_file = 'pattern-studio/savi/saviDownloadSvg.php'
WHERE code_page LIKE '%savi%' OR title LIKE '%Savi%';

-- Princess Boat Neck pattern
UPDATE pattern_making_portfolio
SET preview_file = 'pattern-studio/boatPrince/princessBoatNeck.php',
    pdf_download_file = 'pattern-studio/boatPrince/princessBoatNeckPDFDownload.php',
    svg_download_file = 'pattern-studio/boatPrince/princessBoatNeckSVGDownload.php'
WHERE code_page LIKE '%boatPrince%' OR code_page LIKE '%princessBoatNeck%' OR title LIKE '%Princess%' OR title LIKE '%Boat Neck%';

-- Create index for faster lookups
CREATE INDEX IF NOT EXISTS idx_pattern_preview_file ON pattern_making_portfolio(preview_file);
