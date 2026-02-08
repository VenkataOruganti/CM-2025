-- Add women-specific upper body measurement fields to public_measurements table
-- These 14 fields are specific to women's measurements

ALTER TABLE public_measurements
    ADD COLUMN blength DECIMAL(5,2) DEFAULT NULL AFTER category,
    ADD COLUMN fshoulder DECIMAL(5,2) DEFAULT NULL AFTER blength,
    ADD COLUMN shoulder DECIMAL(5,2) DEFAULT NULL AFTER fshoulder,
    ADD COLUMN bnDepth DECIMAL(5,2) DEFAULT NULL AFTER shoulder,
    ADD COLUMN fndepth DECIMAL(5,2) DEFAULT NULL AFTER bnDepth,
    ADD COLUMN apex DECIMAL(5,2) DEFAULT NULL AFTER fndepth,
    ADD COLUMN flength DECIMAL(5,2) DEFAULT NULL AFTER apex,
    ADD COLUMN chest DECIMAL(5,2) DEFAULT NULL AFTER flength,
    ADD COLUMN slength DECIMAL(5,2) DEFAULT NULL AFTER waist,
    ADD COLUMN saround DECIMAL(5,2) DEFAULT NULL AFTER slength,
    ADD COLUMN sopen DECIMAL(5,2) DEFAULT NULL AFTER saround,
    ADD COLUMN armhole DECIMAL(5,2) DEFAULT NULL AFTER sopen;
