-- ============================================================================
-- Migration: Fix NOT NULL constraints on measurement columns
-- Created: 2025-12-28
-- Description: Changes bust, waist, hips, and height columns to allow NULL
-- ============================================================================

ALTER TABLE measurements
MODIFY COLUMN bust DECIMAL(5,2) NULL,
MODIFY COLUMN waist DECIMAL(5,2) NULL,
MODIFY COLUMN hips DECIMAL(5,2) NULL,
MODIFY COLUMN height DECIMAL(5,2) NULL;
