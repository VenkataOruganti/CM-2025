-- ============================================================================
-- Migration: Create standard_sizes table
-- Created: 2025-01-02
-- Description: Creates a table to store standard size measurements that can
--              be used to pre-fill form fields on the pattern studio page
-- ============================================================================

-- Create standard_sizes table
CREATE TABLE IF NOT EXISTS standard_sizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    size_code VARCHAR(10) NOT NULL COMMENT 'Size code: XS, S, M, L, XL, XXL',
    size_name VARCHAR(50) NOT NULL COMMENT 'Display name for the size',
    category VARCHAR(50) DEFAULT 'women' COMMENT 'Category: women, men, boy, girl',
    height DECIMAL(5,2) DEFAULT NULL COMMENT 'Height in inches',
    bust DECIMAL(5,2) DEFAULT NULL COMMENT 'Bust measurement in inches',
    chest DECIMAL(5,2) DEFAULT NULL COMMENT 'Chest measurement in inches',
    waist DECIMAL(5,2) DEFAULT NULL COMMENT 'Waist measurement in inches',
    hips DECIMAL(5,2) DEFAULT NULL COMMENT 'Hip measurement in inches',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_size_category (size_code, category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Standard size measurements for pre-filling pattern forms';

-- Insert XS size for women category
INSERT INTO standard_sizes (size_code, size_name, category, height, bust, chest, waist, hips)
VALUES ('XS', 'Extra Small', 'women', 66, 32, 28, 26, 36);

-- Insert S size for women category
INSERT INTO standard_sizes (size_code, size_name, category, height, bust, chest, waist, hips)
VALUES ('S', 'Small', 'women', 66, 34, 30, 26, 36);

-- Insert M size for women category
INSERT INTO standard_sizes (size_code, size_name, category, height, bust, chest, waist, hips)
VALUES ('M', 'Medium', 'women', 66, 36, 32, 30, 38);

-- Insert L size for women category
INSERT INTO standard_sizes (size_code, size_name, category, height, bust, chest, waist, hips)
VALUES ('L', 'Large', 'women', 66, 38, 34, 30, 40);

-- Insert XL size for women category
INSERT INTO standard_sizes (size_code, size_name, category, height, bust, chest, waist, hips)
VALUES ('XL', 'Extra Large', 'women', 66, 40, 36, 32, 42);

-- Insert XXL size for women category
INSERT INTO standard_sizes (size_code, size_name, category, height, bust, chest, waist, hips)
VALUES ('XXL', 'Extra Extra Large', 'women', 66, 42, 38, 34, 44);

-- Insert 3XL size for women category
INSERT INTO standard_sizes (size_code, size_name, category, height, bust, chest, waist, hips)
VALUES ('3XL', '3X Large', 'women', 66, 44, 38, 36, 46);
