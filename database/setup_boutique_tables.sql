-- Combined SQL setup for Boutique feature
-- This file creates the customers table and adds customer_id to measurements table

USE cm;

-- Create customers table for boutique owners
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    boutique_user_id INT NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_reference VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Foreign key to link to boutique owner
    FOREIGN KEY (boutique_user_id) REFERENCES users(id) ON DELETE CASCADE,

    -- Index for faster lookups
    INDEX idx_boutique_user (boutique_user_id),
    INDEX idx_customer_name (customer_name),
    INDEX idx_customer_reference (customer_reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add customer_id column to measurements table (if not exists)
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'cm'
    AND TABLE_NAME = 'measurements'
    AND COLUMN_NAME = 'customer_id'
);

-- Add customer_id column (nullable for backward compatibility)
SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE measurements ADD COLUMN customer_id INT DEFAULT NULL AFTER user_id',
    'SELECT "customer_id column already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key constraint (if not exists)
SET @constraint_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = 'cm'
    AND TABLE_NAME = 'measurements'
    AND CONSTRAINT_NAME = 'fk_customer_id'
);

SET @sql = IF(
    @constraint_exists = 0,
    'ALTER TABLE measurements ADD CONSTRAINT fk_customer_id FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL',
    'SELECT "fk_customer_id constraint already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index for customer_id (if not exists)
SET @index_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = 'cm'
    AND TABLE_NAME = 'measurements'
    AND INDEX_NAME = 'idx_customer_id'
);

SET @sql = IF(
    @index_exists = 0,
    'ALTER TABLE measurements ADD INDEX idx_customer_id (customer_id)',
    'SELECT "idx_customer_id index already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add composite index for user_id and customer_id (if not exists)
SET @index_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = 'cm'
    AND TABLE_NAME = 'measurements'
    AND INDEX_NAME = 'idx_user_customer'
);

SET @sql = IF(
    @index_exists = 0,
    'ALTER TABLE measurements ADD INDEX idx_user_customer (user_id, customer_id)',
    'SELECT "idx_user_customer index already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Boutique tables setup completed successfully!' AS status;
