-- Create customers table for boutique owners
-- This table stores customer information for boutique/tailor businesses

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
