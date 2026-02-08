-- ============================================================================
-- Migration: Rename database columns to match form field names
-- Created: 2025-12-28
-- Description: Renames columns to use form field names (like fshoulder)
--              instead of full names, with descriptions in comments
-- ============================================================================

ALTER TABLE measurements
CHANGE COLUMN blouse_back_length blength DECIMAL(5,2) NULL COMMENT 'Blouse Back Length (field 1)',
CHANGE COLUMN full_shoulder fshoulder DECIMAL(5,2) NULL COMMENT 'Full Shoulder (field 2)',
CHANGE COLUMN shoulder_strap shoulder DECIMAL(5,2) NULL COMMENT 'Shoulder Strap (field 3)',
CHANGE COLUMN back_neck_depth bnDepth DECIMAL(5,2) NULL COMMENT 'Back Neck Depth (field 4)',
CHANGE COLUMN front_neck_depth fndepth DECIMAL(5,2) NULL COMMENT 'Front Neck Depth (field 5)',
CHANGE COLUMN shoulder_to_apex apex DECIMAL(5,2) NULL COMMENT 'Shoulder to Apex (field 6)',
CHANGE COLUMN front_length flength DECIMAL(5,2) NULL COMMENT 'Front Length (field 7)',
CHANGE COLUMN upper_chest chest DECIMAL(5,2) NULL COMMENT 'Upper Chest (field 8)',
CHANGE COLUMN sleeve_length slength DECIMAL(5,2) NULL COMMENT 'Sleeve Length (field 11)',
CHANGE COLUMN arm_round saround DECIMAL(5,2) NULL COMMENT 'Arm Round (field 12)',
CHANGE COLUMN sleeve_end_round sopen DECIMAL(5,2) NULL COMMENT 'Sleeve End Round (field 13)';
