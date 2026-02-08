-- Add price column to pattern_making_portfolio table
-- Run this migration to add price field for portfolio items

ALTER TABLE pattern_making_portfolio
ADD COLUMN IF NOT EXISTS price DECIMAL(10, 2) DEFAULT 0.00 AFTER description;
