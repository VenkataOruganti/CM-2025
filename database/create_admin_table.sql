-- Admin table for dashboard access
CREATE TABLE IF NOT EXISTS admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (Username: venkataoruganti@yahoo.com, Password: Bhargav)
INSERT INTO admin_users (username, password) VALUES
('venkataoruganti@yahoo.com', '$2y$12$G8GfkL3HyIRSKMM.FHYYpeRCLA9gxsuIw6KtVmyaOjpRChuL.Vf.O');
