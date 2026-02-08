-- ============================================================================
-- Migration: Create predesigned_patterns table
-- Created: 2025-01-02
-- Description: Creates a table to store pre-designed pattern PDFs uploaded by
--              Pattern Providers or Admin
-- ============================================================================

-- Create predesigned_patterns table
CREATE TABLE IF NOT EXISTS predesigned_patterns (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Pattern Information
    title VARCHAR(255) NOT NULL COMMENT 'Pattern title/name',
    description TEXT DEFAULT NULL COMMENT 'Pattern description',
    category VARCHAR(50) DEFAULT 'blouse' COMMENT 'Category: blouse, kurti, dress, etc.',

    -- Files
    pdf_file VARCHAR(500) NOT NULL COMMENT 'Path to PDF file',
    thumbnail VARCHAR(500) DEFAULT NULL COMMENT 'Path to thumbnail image',

    -- Designer/Provider Information
    provider_id INT DEFAULT NULL COMMENT 'User ID of pattern provider (NULL if admin uploaded)',
    designer_name VARCHAR(255) DEFAULT NULL COMMENT 'Designer/brand name to display',
    designer_info TEXT DEFAULT NULL COMMENT 'Additional designer information',

    -- Pricing
    price DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Price for download (0 = free)',
    currency VARCHAR(3) DEFAULT 'INR' COMMENT 'Currency code',

    -- Status & Approval
    status ENUM('pending', 'approved', 'rejected', 'archived') DEFAULT 'pending' COMMENT 'Approval status',
    approved_by INT DEFAULT NULL COMMENT 'Admin user ID who approved',
    approved_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Approval timestamp',
    rejection_reason TEXT DEFAULT NULL COMMENT 'Reason for rejection if rejected',

    -- Statistics
    download_count INT DEFAULT 0 COMMENT 'Number of times downloaded',
    view_count INT DEFAULT 0 COMMENT 'Number of times viewed',

    -- Metadata
    tags VARCHAR(500) DEFAULT NULL COMMENT 'Comma-separated tags for search',
    is_featured TINYINT(1) DEFAULT 0 COMMENT 'Featured pattern flag',
    sort_order INT DEFAULT 0 COMMENT 'Display order',

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_provider (provider_id),
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_featured (is_featured),
    INDEX idx_created (created_at),

    -- Foreign Keys
    CONSTRAINT fk_predesigned_provider FOREIGN KEY (provider_id)
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Pre-designed pattern PDFs uploaded by providers or admin';

-- Create index for search
CREATE FULLTEXT INDEX idx_search ON predesigned_patterns(title, description, tags, designer_name);
