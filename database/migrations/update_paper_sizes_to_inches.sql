-- ============================================================================
-- Migration: Update paper_sizes display names to show inches
-- Created: 2026-01-19
-- Description: Updates A-series paper sizes to display dimensions in inches
--              for consistency with pattern measurements
-- ============================================================================

-- Update A0: 33.1 x 46.8 inches
UPDATE paper_sizes SET size_name = 'A0 (33.1 x 46.8 in)' WHERE size_code = 'A0';

-- Update A2: 16.5 x 23.4 inches
UPDATE paper_sizes SET size_name = 'A2 (16.5 x 23.4 in)' WHERE size_code = 'A2';

-- Update A3: 11.7 x 16.5 inches
UPDATE paper_sizes SET size_name = 'A3 (11.7 x 16.5 in)' WHERE size_code = 'A3';

-- Update A4: 8.3 x 11.7 inches
UPDATE paper_sizes SET size_name = 'A4 (8.3 x 11.7 in)' WHERE size_code = 'A4';

-- Add US paper sizes if not exist
INSERT IGNORE INTO paper_sizes (size_code, size_name, width_mm, height_mm, is_active, sort_order)
VALUES ('LETTER', 'US Letter (8.5 x 11 in)', 215.90, 279.40, 1, 5);

INSERT IGNORE INTO paper_sizes (size_code, size_name, width_mm, height_mm, is_active, sort_order)
VALUES ('LEGAL', 'US Legal (8.5 x 14 in)', 215.90, 355.60, 1, 6);

INSERT IGNORE INTO paper_sizes (size_code, size_name, width_mm, height_mm, is_active, sort_order)
VALUES ('TABLOID', 'Tabloid (11 x 17 in)', 279.40, 431.80, 1, 7);

-- Verify changes
SELECT size_code, size_name,
       ROUND(width_mm / 25.4, 1) as width_inches,
       ROUND(height_mm / 25.4, 1) as height_inches
FROM paper_sizes
ORDER BY sort_order;
