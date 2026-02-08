-- ============================================================================
-- Master Migration: Pattern Catalog System
-- Created: 2025-01-02
-- Description: Run all migrations for the new pattern catalog system
--
-- Tables created:
--   1. paper_sizes - Available paper sizes for PDF output
--   2. predesigned_patterns - Static PDF patterns from providers
--   3. pattern_templates - Parametric pattern code references
--   4. blouse_designs - Front/back blouse design options
--
-- Run these migrations in order by sourcing each file:
--   source create_paper_sizes_table.sql;
--   source create_predesigned_patterns_table.sql;
--   source create_pattern_templates_table.sql;
--   source create_blouse_designs_table.sql;
--
-- Or run this file which includes all migrations inline.
-- ============================================================================

-- ============================================================================
-- 1. PAPER SIZES TABLE
-- ============================================================================
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

-- Insert default paper sizes
INSERT IGNORE INTO paper_sizes (size_code, size_name, width_mm, height_mm, is_active, sort_order) VALUES
('A0', 'A0 (841 x 1189 mm)', 841.00, 1189.00, 1, 1),
('A2', 'A2 (420 x 594 mm)', 420.00, 594.00, 1, 2),
('A3', 'A3 (297 x 420 mm)', 297.00, 420.00, 1, 3),
('A4', 'A4 (210 x 297 mm)', 210.00, 297.00, 1, 4);

-- ============================================================================
-- 2. PREDESIGNED PATTERNS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS predesigned_patterns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL COMMENT 'Pattern title/name',
    description TEXT DEFAULT NULL COMMENT 'Pattern description',
    category VARCHAR(50) DEFAULT 'blouse' COMMENT 'Category: blouse, kurti, dress, etc.',
    pdf_file VARCHAR(500) NOT NULL COMMENT 'Path to PDF file',
    thumbnail VARCHAR(500) DEFAULT NULL COMMENT 'Path to thumbnail image',
    provider_id INT DEFAULT NULL COMMENT 'User ID of pattern provider',
    designer_name VARCHAR(255) DEFAULT NULL COMMENT 'Designer/brand name',
    designer_info TEXT DEFAULT NULL COMMENT 'Additional designer information',
    price DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Price for download',
    currency VARCHAR(3) DEFAULT 'INR' COMMENT 'Currency code',
    status ENUM('pending', 'approved', 'rejected', 'archived') DEFAULT 'pending',
    approved_by INT DEFAULT NULL,
    approved_at TIMESTAMP NULL DEFAULT NULL,
    rejection_reason TEXT DEFAULT NULL,
    download_count INT DEFAULT 0,
    view_count INT DEFAULT 0,
    tags VARCHAR(500) DEFAULT NULL,
    is_featured TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_provider (provider_id),
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_featured (is_featured),
    INDEX idx_created (created_at),
    CONSTRAINT fk_predesigned_provider FOREIGN KEY (provider_id)
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Pre-designed pattern PDFs';

-- ============================================================================
-- 3. PATTERN TEMPLATES TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS pattern_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Template display name',
    description TEXT DEFAULT NULL COMMENT 'Template description',
    category VARCHAR(50) DEFAULT 'blouse' COMMENT 'Category: blouse, kurti, pants, etc.',
    code_reference VARCHAR(255) NOT NULL COMMENT 'PHP file reference',
    pdf_generator VARCHAR(255) DEFAULT NULL COMMENT 'PDF generator file reference',
    thumbnail VARCHAR(500) DEFAULT NULL COMMENT 'Path to thumbnail image',
    preview_image VARCHAR(500) DEFAULT NULL COMMENT 'Path to preview image',
    required_measurements TEXT DEFAULT NULL COMMENT 'JSON array of required fields',
    price DECIMAL(10,2) DEFAULT 0.00,
    currency VARCHAR(3) DEFAULT 'INR',
    is_active TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    usage_count INT DEFAULT 0,
    tags VARCHAR(500) DEFAULT NULL,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'intermediate',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_active (is_active),
    INDEX idx_featured (is_featured),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Pattern templates for Build Your Pattern';

-- Insert existing Savi pattern
INSERT IGNORE INTO pattern_templates (
    name, description, category, code_reference, pdf_generator,
    required_measurements, is_active, is_featured, sort_order
) VALUES (
    'Savi Blouse Pattern',
    'Complete blouse pattern with front, back, sleeve, and waist band (patti). Generates 4 pages of cutting patterns based on your measurements.',
    'blouse',
    'savi/saviComplete',
    'savi/saviDownloadPdf',
    '["blength", "fshoulder", "shoulder", "bnDepth", "fndepth", "apex", "flength", "chest", "waist", "slength", "saround", "sopen", "bust", "armhole"]',
    1, 1, 1
);

-- ============================================================================
-- 4. BLOUSE DESIGNS TABLE
-- ============================================================================
CREATE TABLE IF NOT EXISTS blouse_designs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Design name',
    description TEXT DEFAULT NULL,
    design_type ENUM('front', 'back') NOT NULL COMMENT 'Front or back design',
    code_reference VARCHAR(255) DEFAULT NULL COMMENT 'PHP file reference',
    svg_template TEXT DEFAULT NULL COMMENT 'Static SVG template',
    thumbnail VARCHAR(500) NOT NULL COMMENT 'Path to thumbnail',
    preview_image VARCHAR(500) DEFAULT NULL,
    compatible_templates TEXT DEFAULT NULL COMMENT 'JSON array of template IDs',
    price DECIMAL(10,2) DEFAULT 0.00,
    currency VARCHAR(3) DEFAULT 'INR',
    provider_id INT DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected', 'archived') DEFAULT 'approved',
    is_active TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    usage_count INT DEFAULT 0,
    tags VARCHAR(500) DEFAULT NULL,
    style_category VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_design_type (design_type),
    INDEX idx_provider (provider_id),
    INDEX idx_status (status),
    INDEX idx_active (is_active),
    INDEX idx_featured (is_featured),
    INDEX idx_sort_order (sort_order),
    INDEX idx_style (style_category),
    CONSTRAINT fk_blouse_design_provider FOREIGN KEY (provider_id)
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Front and back blouse design options';

-- ============================================================================
-- Migration complete!
-- ============================================================================
SELECT 'Pattern Catalog migrations completed successfully!' AS status;
