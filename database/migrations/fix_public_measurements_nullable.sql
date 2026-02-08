-- Make hips and height nullable in public_measurements table
-- This allows insertion of women's upper body measurements without requiring lower body data

ALTER TABLE public_measurements
    MODIFY COLUMN hips DECIMAL(5,2) DEFAULT NULL,
    MODIFY COLUMN height DECIMAL(5,2) DEFAULT NULL;
