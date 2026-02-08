-- Migration: Update public_measurements table - cleanup and prepare for deduplication
-- Date: 2026-01-17
-- Purpose: Remove unused columns and make bust/waist optional

-- Make bust and waist optional (not all measurements require them)
ALTER TABLE public_measurements MODIFY COLUMN bust DECIMAL(5,2) DEFAULT NULL;
ALTER TABLE public_measurements MODIFY COLUMN waist DECIMAL(5,2) DEFAULT NULL;

-- Drop columns that are not used for saree blouse patterns
ALTER TABLE public_measurements DROP COLUMN hips;
ALTER TABLE public_measurements DROP COLUMN height;
ALTER TABLE public_measurements DROP COLUMN sleeve_length;
ALTER TABLE public_measurements DROP COLUMN arm_circumference;
ALTER TABLE public_measurements DROP COLUMN inseam;
ALTER TABLE public_measurements DROP COLUMN thigh_circumference;
ALTER TABLE public_measurements DROP COLUMN neck_circumference;
