-- Migration: Add customer_id to measurements table
-- This links measurements to the customers table for boutique owners

-- Add customer_id column (nullable for backward compatibility)
ALTER TABLE measurements
ADD COLUMN customer_id INT DEFAULT NULL AFTER user_id;

-- Add foreign key constraint
ALTER TABLE measurements
ADD CONSTRAINT fk_customer_id
FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL;

-- Add index for faster lookups
ALTER TABLE measurements
ADD INDEX idx_customer_id (customer_id);

-- Add index for boutique user queries (user_id + customer_id)
ALTER TABLE measurements
ADD INDEX idx_user_customer (user_id, customer_id);
