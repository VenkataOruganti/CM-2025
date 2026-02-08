-- ============================================================================
-- Migration: Create paper_sizes table
-- Created: 2025-01-02
-- Description: Creates a table to store available paper/printer sizes for PDF output
-- ============================================================================

-- Create paper_sizes table
CREATE TABLE IF NOT EXISTS paper_sizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    size_code VARCHAR(10) NOT NULL UNIQUE COMMENT 'Size code: A0, A2, A3, A4',
    size_name VARCHAR(50) NOT NULL COMMENT 'Display name for the size',
    width_mm DECIMAL(8,2) NOT NULL COMMENT 'Width in millimeters',
    height_mm DECIMAL(8,2) NOT NULL COMMENT 'Height in millimeters',
    is_active TINYINT(1) DEFAULT 1 COMMENT 'Whether this size is available for selection',
    sort_order INT DEFAULT 0 COMMENT 'Display order in dropdown',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Available paper sizes for PDF pattern output';

-- Insert default paper sizes (display in inches for pattern consistency)
-- A0: 33.1 x 46.8 inches (841 x 1189 mm)
INSERT INTO paper_sizes (size_code, size_name, width_mm, height_mm, is_active, sort_order)
VALUES ('A0', 'A0 (33.1 x 46.8 in)', 841.00, 1189.00, 1, 1);

-- A2: 16.5 x 23.4 inches (420 x 594 mm)
INSERT INTO paper_sizes (size_code, size_name, width_mm, height_mm, is_active, sort_order)
VALUES ('A2', 'A2 (16.5 x 23.4 in)', 420.00, 594.00, 1, 2);

-- A3: 11.7 x 16.5 inches (297 x 420 mm)
INSERT INTO paper_sizes (size_code, size_name, width_mm, height_mm, is_active, sort_order)
VALUES ('A3', 'A3 (11.7 x 16.5 in)', 297.00, 420.00, 1, 3);

-- A4: 8.3 x 11.7 inches (210 x 297 mm)
INSERT INTO paper_sizes (size_code, size_name, width_mm, height_mm, is_active, sort_order)
VALUES ('A4', 'A4 (8.3 x 11.7 in)', 210.00, 297.00, 1, 4);

-- US Letter: 8.5 x 11 inches (215.9 x 279.4 mm)
INSERT INTO paper_sizes (size_code, size_name, width_mm, height_mm, is_active, sort_order)
VALUES ('LETTER', 'US Letter (8.5 x 11 in)', 215.90, 279.40, 1, 5);

-- US Legal: 8.5 x 14 inches (215.9 x 355.6 mm)
INSERT INTO paper_sizes (size_code, size_name, width_mm, height_mm, is_active, sort_order)
VALUES ('LEGAL', 'US Legal (8.5 x 14 in)', 215.90, 355.60, 1, 6);

-- Tabloid: 11 x 17 inches (279.4 x 431.8 mm)
INSERT INTO paper_sizes (size_code, size_name, width_mm, height_mm, is_active, sort_order)
VALUES ('TABLOID', 'Tabloid (11 x 17 in)', 279.40, 431.80, 1, 7);
