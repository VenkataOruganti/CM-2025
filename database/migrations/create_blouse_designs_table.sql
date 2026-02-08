-- ============================================================================
-- Migration: Create blouse_designs table
-- Created: 2025-01-02
-- Description: Creates a table to store front and back blouse design options
--              that can be selected and combined with pattern templates
-- ============================================================================

-- Create blouse_designs table
CREATE TABLE IF NOT EXISTS blouse_designs (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Design Information
    name VARCHAR(255) NOT NULL COMMENT 'Design name (e.g., "Deep V-Neck", "Princess Cut")',
    description TEXT DEFAULT NULL COMMENT 'Design description',

    -- Type: front or back
    design_type ENUM('front', 'back') NOT NULL COMMENT 'Whether this is a front or back design',

    -- Code Reference (for parametric designs)
    code_reference VARCHAR(255) DEFAULT NULL COMMENT 'PHP file reference for SVG generation',
    svg_template TEXT DEFAULT NULL COMMENT 'Static SVG template if not parametric',

    -- Display
    thumbnail VARCHAR(500) NOT NULL COMMENT 'Path to thumbnail image',
    preview_image VARCHAR(500) DEFAULT NULL COMMENT 'Path to larger preview image',

    -- Compatibility
    compatible_templates TEXT DEFAULT NULL COMMENT 'JSON array of compatible pattern_template IDs',

    -- Pricing
    price DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Additional price for this design',
    currency VARCHAR(3) DEFAULT 'INR' COMMENT 'Currency code',

    -- Provider (if uploaded by pattern provider)
    provider_id INT DEFAULT NULL COMMENT 'User ID of pattern provider (NULL if admin)',

    -- Status
    status ENUM('pending', 'approved', 'rejected', 'archived') DEFAULT 'approved' COMMENT 'Approval status',
    is_active TINYINT(1) DEFAULT 1 COMMENT 'Whether design is active',
    is_featured TINYINT(1) DEFAULT 0 COMMENT 'Featured design flag',
    sort_order INT DEFAULT 0 COMMENT 'Display order',

    -- Statistics
    usage_count INT DEFAULT 0 COMMENT 'Number of times used',

    -- Metadata
    tags VARCHAR(500) DEFAULT NULL COMMENT 'Comma-separated tags for search',
    style_category VARCHAR(100) DEFAULT NULL COMMENT 'Style: traditional, modern, casual, formal, etc.',

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_design_type (design_type),
    INDEX idx_provider (provider_id),
    INDEX idx_status (status),
    INDEX idx_active (is_active),
    INDEX idx_featured (is_featured),
    INDEX idx_sort_order (sort_order),
    INDEX idx_style (style_category),

    -- Foreign Keys
    CONSTRAINT fk_blouse_design_provider FOREIGN KEY (provider_id)
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Front and back blouse design options';

-- Create index for search
CREATE FULLTEXT INDEX idx_design_search ON blouse_designs(name, description, tags);
