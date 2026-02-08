-- ============================================================================
-- Migration: Add Women-Specific Measurement Columns
-- Created: 2025-12-28
-- Description: Adds columns to store saree blouse measurements for women
-- ============================================================================

-- Add new columns for women-specific measurements
ALTER TABLE measurements
ADD COLUMN blouse_back_length DECIMAL(5,2) NULL COMMENT 'Blouse back length in inches (field 1)',
ADD COLUMN full_shoulder DECIMAL(5,2) NULL COMMENT 'Full shoulder width in inches (field 2)',
ADD COLUMN shoulder_strap DECIMAL(5,2) NULL COMMENT 'Shoulder strap measurement in inches (field 3)',
ADD COLUMN back_neck_depth DECIMAL(5,2) NULL COMMENT 'Back neck depth in inches (field 4)',
ADD COLUMN front_neck_depth DECIMAL(5,2) NULL COMMENT 'Front neck depth in inches (field 5)',
ADD COLUMN shoulder_to_apex DECIMAL(5,2) NULL COMMENT 'Shoulder to apex measurement in inches (field 6)',
ADD COLUMN front_length DECIMAL(5,2) NULL COMMENT 'Front length in inches (field 7)',
ADD COLUMN upper_chest DECIMAL(5,2) NULL COMMENT 'Upper chest measurement in inches (field 8)',
ADD COLUMN bust DECIMAL(5,2) NULL COMMENT 'Bust round measurement in inches (field 9)',
ADD COLUMN sleeve_length DECIMAL(5,2) NULL COMMENT 'Sleeve length in inches (field 11)',
ADD COLUMN arm_round DECIMAL(5,2) NULL COMMENT 'Arm round/circumference in inches (field 12)',
ADD COLUMN sleeve_end_round DECIMAL(5,2) NULL COMMENT 'Sleeve end round in inches (field 13)',
ADD COLUMN armhole DECIMAL(5,2) NULL COMMENT 'Armhole measurement in inches (field 14)';

-- Add indexes for better query performance
CREATE INDEX idx_measurements_category ON measurements(category);
CREATE INDEX idx_measurements_user_category ON measurements(user_id, category);
