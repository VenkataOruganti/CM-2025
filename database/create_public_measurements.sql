-- Public Measurements Table for Anonymous Data Collection
-- This table stores all measurement submissions for admin analytics
-- No user identification - completely anonymous

CREATE TABLE IF NOT EXISTS public_measurements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category ENUM('women', 'men', 'boy', 'girl') NOT NULL,

    -- Required measurements
    bust DECIMAL(5,2) NOT NULL,
    waist DECIMAL(5,2) NOT NULL,
    hips DECIMAL(5,2) NOT NULL,
    height DECIMAL(5,2) NOT NULL,

    -- Optional measurements
    shoulder_width DECIMAL(5,2) DEFAULT NULL,
    sleeve_length DECIMAL(5,2) DEFAULT NULL,
    arm_circumference DECIMAL(5,2) DEFAULT NULL,
    inseam DECIMAL(5,2) DEFAULT NULL,
    thigh_circumference DECIMAL(5,2) DEFAULT NULL,
    neck_circumference DECIMAL(5,2) DEFAULT NULL,

    -- Timestamp for data analysis
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Index for faster queries by category and date
    INDEX idx_category (category),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
