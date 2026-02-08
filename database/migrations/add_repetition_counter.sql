-- Migration: Add repetition counter and updated_at to public_measurements table
-- Date: 2026-01-17
-- Purpose: Enable deduplication - increment counter instead of storing duplicate measurements

-- Add repetition counter column
ALTER TABLE public_measurements
    ADD COLUMN repetition INT UNSIGNED DEFAULT 1
    COMMENT 'Number of times these measurements (within ±0.5" tolerance) have been submitted';

-- Add updated_at column to track when repetition counter was last incremented
ALTER TABLE public_measurements
    ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
    COMMENT 'Last time this record was updated (when repetition was incremented)';

-- Create composite index for fast lookup of similar measurements
-- We index the most commonly provided fields: category, bust, waist, blength, chest
CREATE INDEX idx_measurements_lookup ON public_measurements(category, bust, waist, blength, chest);

-- Add index on repetition for analytics queries (e.g., "most common measurements")
CREATE INDEX idx_repetition ON public_measurements(repetition DESC);

-- Add index on bust and waist for general queries
CREATE INDEX idx_bust_waist ON public_measurements(bust, waist);

-- Add index on category and created_at
CREATE INDEX idx_category_created ON public_measurements(category, created_at);
