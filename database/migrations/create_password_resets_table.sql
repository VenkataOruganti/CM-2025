-- Password Resets Table Migration
-- This table stores password reset tokens with expiration
-- Tokens are hashed for security

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    token_hash VARCHAR(64) NOT NULL,  -- SHA-256 hash of the token
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,    -- When the token was used (NULL if not used)
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) DEFAULT NULL,  -- IPv6 compatible
    user_agent TEXT DEFAULT NULL,

    -- Indexes for fast lookups
    INDEX idx_token_hash (token_hash),
    INDEX idx_user_id (user_id),
    INDEX idx_email (email),
    INDEX idx_expires_at (expires_at),

    -- Foreign key to users table
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Clean up expired tokens (can be run as a scheduled job)
-- DELETE FROM password_resets WHERE expires_at < NOW() AND used_at IS NULL;
