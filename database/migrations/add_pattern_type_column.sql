-- ============================================================================
-- Migration: Add pattern_type column to measurements table
-- Created: 2025-01-02
-- Description: Adds pattern_type column to track what type of pattern
--              the measurement is for (blouse, kurti, pants, etc.)
-- ============================================================================

-- Add pattern_type column with default value 'blouse' for existing records
ALTER TABLE measurements
ADD COLUMN pattern_type VARCHAR(50) DEFAULT 'blouse' COMMENT 'Type of pattern: blouse, kurti, blouse_back, blouse_front, sleeve, pants, etc.' AFTER category;

-- Also add to public_measurements table for consistency
ALTER TABLE public_measurements
ADD COLUMN pattern_type VARCHAR(50) DEFAULT 'blouse' COMMENT 'Type of pattern: blouse, kurti, blouse_back, blouse_front, sleeve, pants, etc.' AFTER category;

-- Update existing records to have 'blouse' as pattern_type (if NULL)
UPDATE measurements SET pattern_type = 'blouse' WHERE pattern_type IS NULL;
UPDATE public_measurements SET pattern_type = 'blouse' WHERE pattern_type IS NULL;
