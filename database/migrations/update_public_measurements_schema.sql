-- Migration: Update public_measurements table to match actual saree blouse measurement fields
-- Date: 2026-01-17
-- Purpose: Remove upper_body/lower_body concept and add all saree blouse measurement fields

-- Add all saree blouse measurement columns (will show warnings if columns already exist, but won't fail)
ALTER TABLE public_measurements
    ADD COLUMN blength DECIMAL(5,2) DEFAULT NULL COMMENT 'Blouse Back Length',
    ADD COLUMN fshoulder DECIMAL(5,2) DEFAULT NULL COMMENT 'Full Shoulder',
    ADD COLUMN shoulder DECIMAL(5,2) DEFAULT NULL COMMENT 'Shoulder Strap',
    ADD COLUMN bnDepth DECIMAL(5,2) DEFAULT NULL COMMENT 'Back Neck Depth',
    ADD COLUMN fndepth DECIMAL(5,2) DEFAULT NULL COMMENT 'Front Neck Depth',
    ADD COLUMN apex DECIMAL(5,2) DEFAULT NULL COMMENT 'Shoulder to Apex',
    ADD COLUMN flength DECIMAL(5,2) DEFAULT NULL COMMENT 'Front Length',
    ADD COLUMN chest DECIMAL(5,2) DEFAULT NULL COMMENT 'Upper Chest',
    ADD COLUMN slength DECIMAL(5,2) DEFAULT NULL COMMENT 'Sleeve Length',
    ADD COLUMN saround DECIMAL(5,2) DEFAULT NULL COMMENT 'Arm Round',
    ADD COLUMN sopen DECIMAL(5,2) DEFAULT NULL COMMENT 'Sleeve End Round',
    ADD COLUMN armhole DECIMAL(5,2) DEFAULT NULL COMMENT 'Armhole';

-- Make waist optional since not all measurements may have it
ALTER TABLE public_measurements
    MODIFY COLUMN waist DECIMAL(5,2) DEFAULT NULL;

-- Drop columns that are not used for saree blouse patterns
-- (These might fail if columns don't exist - that's okay)
ALTER TABLE public_measurements
    DROP COLUMN IF EXISTS hips;

ALTER TABLE public_measurements
    DROP COLUMN IF EXISTS height;

ALTER TABLE public_measurements
    DROP COLUMN IF EXISTS shoulder_width;

ALTER TABLE public_measurements
    DROP COLUMN IF EXISTS sleeve_length;

ALTER TABLE public_measurements
    DROP COLUMN IF EXISTS arm_circumference;

ALTER TABLE public_measurements
    DROP COLUMN IF EXISTS inseam;

ALTER TABLE public_measurements
    DROP COLUMN IF EXISTS thigh_circumference;

ALTER TABLE public_measurements
    DROP COLUMN IF EXISTS neck_circumference;

-- Update bust to be optional (not all measurements require it)
ALTER TABLE public_measurements
    MODIFY COLUMN bust DECIMAL(5,2) DEFAULT NULL;

-- Add index for faster searches on commonly queried fields
CREATE INDEX IF NOT EXISTS idx_bust_waist ON public_measurements(bust, waist);
CREATE INDEX IF NOT EXISTS idx_category_created ON public_measurements(category, created_at);
