-- Create measurements table
CREATE TABLE IF NOT EXISTS measurements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    measurement_of ENUM('self', 'customer') NOT NULL,
    category ENUM('women', 'men', 'boy', 'girl') NOT NULL,
    customer_name VARCHAR(255) DEFAULT NULL,

    -- Upper Body Measurements (in inches)
    bust DECIMAL(5,2) NOT NULL,
    waist DECIMAL(5,2) NOT NULL,
    hips DECIMAL(5,2) NOT NULL,
    shoulder_width DECIMAL(5,2) DEFAULT NULL,
    sleeve_length DECIMAL(5,2) DEFAULT NULL,
    arm_circumference DECIMAL(5,2) DEFAULT NULL,

    -- Lower Body & Other Measurements (in inches)
    inseam DECIMAL(5,2) DEFAULT NULL,
    thigh_circumference DECIMAL(5,2) DEFAULT NULL,
    neck_circumference DECIMAL(5,2) DEFAULT NULL,
    height DECIMAL(5,2) NOT NULL,

    -- Additional Notes
    notes TEXT DEFAULT NULL,

    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Foreign Key
    CONSTRAINT fk_measurements_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    -- Indexes
    INDEX idx_user_id (user_id),
    INDEX idx_measurement_of (measurement_of),
    INDEX idx_category (category),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
