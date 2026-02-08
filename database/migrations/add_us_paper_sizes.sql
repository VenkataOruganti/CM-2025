-- ============================================================================
-- Migration: Add US paper sizes to paper_sizes table
-- Created: 2026-01-19
-- Description: Adds US Letter, Legal, and Tabloid paper sizes
-- ============================================================================

-- US Letter: 8.5 x 11 inches = 215.9 x 279.4 mm
INSERT IGNORE INTO paper_sizes (size_code, size_name, width_mm, height_mm, is_active, sort_order)
VALUES ('LETTER', 'US Letter (8.5 x 11 in)', 215.90, 279.40, 1, 5);

-- US Legal: 8.5 x 14 inches = 215.9 x 355.6 mm
INSERT IGNORE INTO paper_sizes (size_code, size_name, width_mm, height_mm, is_active, sort_order)
VALUES ('LEGAL', 'US Legal (8.5 x 14 in)', 215.90, 355.60, 1, 6);

-- Tabloid: 11 x 17 inches = 279.4 x 431.8 mm
INSERT IGNORE INTO paper_sizes (size_code, size_name, width_mm, height_mm, is_active, sort_order)
VALUES ('TABLOID', 'Tabloid (11 x 17 in)', 279.40, 431.80, 1, 7);

-- Verify insertion
SELECT * FROM paper_sizes ORDER BY sort_order;
