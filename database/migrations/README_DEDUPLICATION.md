# Public Measurements Deduplication System

## Overview

This document explains the deduplication system implemented for public measurements. Instead of storing duplicate measurement records, we increment a "repetition" counter when similar measurements are submitted.

## Key Concept: ±0.5" Tolerance Grouping

Measurements within ±0.5 inches are considered a **match** and grouped together.

**Example:**
- Visitor A submits: bust=34.3, waist=28.2, blength=14.7
- System rounds to: bust=34.5, waist=28.0, blength=15.0
- Record created with `repetition=1`

- Visitor B submits: bust=34.6, waist=28.4, blength=14.9
- System rounds to: bust=34.5, waist=28.0, blength=15.0
- **Match found!** → `repetition` incremented to 2

- Visitor C submits: bust=34.5, waist=28.0, blength=15.0
- Already rounded → **Match found!** → `repetition` incremented to 3

## Database Changes

### Migration: `add_repetition_counter.sql`

```sql
-- Add repetition counter
ALTER TABLE public_measurements
    ADD COLUMN repetition INT UNSIGNED DEFAULT 1;

-- Add updated_at timestamp
ALTER TABLE public_measurements
    ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP;

-- Create index for fast lookups
CREATE INDEX idx_measurements_lookup ON public_measurements(
    category, bust, waist, blength, chest
);

-- Index for analytics (most popular measurements)
CREATE INDEX idx_repetition ON public_measurements(repetition DESC);
```

### New Columns

| Column | Type | Description |
|--------|------|-------------|
| `repetition` | INT UNSIGNED | Number of times these measurements (within ±0.5") have been submitted |
| `updated_at` | TIMESTAMP | Last time `repetition` was incremented |

## How It Works

### 1. Rounding Algorithm

```php
function roundMeasurement($value) {
    if ($value === null) return null;
    // Round to nearest 0.5
    return round($value * 2) / 2;
}
```

**Examples:**
- 14.0 → 14.0
- 14.2 → 14.0
- 14.3 → 14.5
- 14.7 → 15.0
- 14.8 → 15.0
- 15.0 → 15.0

### 2. Matching Logic

When a visitor submits measurements:

**Step 1:** Round all 14 fields to nearest 0.5
```
Original:  blength=14.3, fshoulder=15.7, shoulder=3.2, ...
Rounded:   blength=14.5, fshoulder=15.5, shoulder=3.0, ...
```

**Step 2:** Search database for matching rounded values
```sql
SELECT id, repetition
FROM public_measurements
WHERE category = 'women'
  AND blength = 14.5
  AND fshoulder = 15.5
  AND shoulder = 3.0
  -- ... all 14 fields
LIMIT 1;
```

**Step 3:** If match found → increment counter
```sql
UPDATE public_measurements
SET repetition = repetition + 1,
    updated_at = CURRENT_TIMESTAMP
WHERE id = ?;
```

**Step 4:** If no match → insert new record
```sql
INSERT INTO public_measurements (...)
VALUES (..., repetition = 1);
```

## Code Structure

### Files Modified

1. **`/includes/public_measurements_helper.php`** (NEW)
   - `roundMeasurement()` - Rounds to nearest 0.5
   - `findMatchingPublicMeasurement()` - Searches for existing match
   - `incrementPublicMeasurementRepetition()` - Increments counter
   - `insertNewPublicMeasurement()` - Creates new record
   - `savePublicMeasurement()` - Main function (handles deduplication logic)

2. **`/pages/pattern-studio.php`** (UPDATED)
   - Lines 32: Added `require_once` for helper file
   - Lines 211-237: Logged-in user submission (uses deduplication)
   - Lines 302-328: Guest user submission (uses deduplication)

3. **`/pages/public-measurements.php`** (UPDATED)
   - Lines 56-59: Added `repetition`, `updated_at` to SELECT
   - Lines 78: ORDER BY `repetition DESC` (shows most popular first)
   - Lines 220-223: Added "Count", "First Seen", "Last Seen" columns
   - Lines 245-262: Display repetition badge and timestamps
   - Lines 151-164: CSS for repetition badge

### Usage Example

```php
// In pattern-studio.php (when visitor submits measurements)

require_once __DIR__ . '/../includes/public_measurements_helper.php';

$publicMeasurements = [
    'blength' => 14.5,
    'fshoulder' => 15.0,
    'shoulder' => 3.5,
    // ... all 14 fields
];

$result = savePublicMeasurement($pdo, 'women', 'blouse', $publicMeasurements);

// Result:
// ['status' => 'updated', 'id' => 123] if match found
// ['status' => 'created', 'id' => 456] if new record
```

## Benefits

### 1. Storage Efficiency
- **Before:** 10,000 visitors with similar measurements → 10,000 database rows
- **After:** 10,000 visitors with similar measurements → ~500 database rows (assuming 20 common measurement sets)
- **Savings:** 95% reduction in storage

### 2. Analytics Value
The `repetition` counter provides valuable insights:
- Most common measurement combinations
- Popular size ranges
- Regional trends (if combined with location data)

### 3. Performance
- Indexed fields enable fast lookups (category, bust, waist, blength, chest)
- Smaller table size = faster queries
- Admin dashboard shows "popular measurements" first (ORDER BY repetition DESC)

## Admin Dashboard Display

The public-measurements.php admin page now shows:

| Column | Description |
|--------|-------------|
| **Count** | Purple badge showing repetition count (e.g., "5×") |
| **First Seen** | When this measurement set was first submitted (`created_at`) |
| **Last Seen** | When it was most recently submitted (`updated_at`) |

**Visual:**
```
Count     First Seen       Last Seen
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 25×      Jan 15, 2026     Jan 17, 2026  ← Most popular
 12×      Jan 12, 2026     Jan 16, 2026
  8×      Jan 10, 2026     Jan 14, 2026
  3×      Jan 17, 2026     Jan 17, 2026
  1×      Jan 17, 2026     -             ← Unique measurement
```

## Testing

### Test Case 1: Exact Match
```php
// Visitor 1: bust=36.0, waist=30.0
// Result: New record created, repetition=1

// Visitor 2: bust=36.0, waist=30.0 (exact same)
// Result: Record updated, repetition=2
```

### Test Case 2: Within Tolerance
```php
// Visitor 1: bust=36.2, waist=30.1
// Rounded: bust=36.0, waist=30.0
// Result: New record created, repetition=1

// Visitor 2: bust=36.3, waist=30.4
// Rounded: bust=36.5, waist=30.5
// Result: New record created (different rounded values)

// Visitor 3: bust=36.1, waist=30.2
// Rounded: bust=36.0, waist=30.0
// Result: Match with Visitor 1, repetition=2
```

### Test Case 3: NULL Handling
```php
// Visitor 1: bust=36.0, waist=30.0, slength=NULL
// Result: New record created

// Visitor 2: bust=36.0, waist=30.0, slength=NULL
// Result: Match found (NULL values also matched), repetition=2

// Visitor 3: bust=36.0, waist=30.0, slength=12.0
// Result: New record (slength differs from NULL)
```

## Edge Cases

### Case 1: All Fields Must Match
If ANY of the 14 fields differ (after rounding), it's considered a new measurement.

```php
Measurement A: bust=36.0, waist=30.0, blength=14.5, ... (all 14 fields)
Measurement B: bust=36.0, waist=30.0, blength=15.0, ... (blength differs)
→ Stored as separate records
```

### Case 2: NULL vs. Zero
```php
NULL ≠ 0.0

If visitor doesn't provide a field → stored as NULL
If visitor provides 0 → stored as 0.0
These are NOT considered a match.
```

### Case 3: Race Conditions
If two visitors submit identical measurements simultaneously:
- Acceptable behavior: Two records may be created instead of one with repetition=2
- Impact: Minimal (rare occurrence, can be manually merged if needed)

## Performance Benchmarks

Based on expected usage:

**Index Lookup (fast):**
```sql
-- Uses idx_measurements_lookup index
SELECT id FROM public_measurements
WHERE category = 'women'
  AND bust = 36.0
  AND waist = 30.0
  AND blength = 14.5
  AND chest = 32.0;
→ ~0.5ms (indexed fields)
```

**Full Match Check:**
```sql
-- After index narrows down results, checks all 14 fields
WHERE ... AND (all 14 fields match)
→ ~2-5ms total (including index lookup)
```

**Update Operation:**
```sql
UPDATE public_measurements SET repetition = repetition + 1 WHERE id = ?;
→ ~1ms
```

**Total time per submission:** ~3-6ms (negligible)

## Future Enhancements

### 1. Configurable Tolerance
Allow admin to adjust tolerance (currently hardcoded to ±0.5"):
```php
define('MEASUREMENT_TOLERANCE', 0.5); // inches
```

### 2. Analytics Dashboard
Create charts showing:
- Most common measurement combinations
- Repetition distribution histogram
- Trends over time

### 3. Suggestion System
When visitor enters measurements, suggest:
"Your measurements match X other visitors. Would you like to see common pattern adjustments?"

## Rollback Plan

If needed, revert changes:

```sql
-- Remove new columns
ALTER TABLE public_measurements
    DROP COLUMN repetition,
    DROP COLUMN updated_at;

-- Remove indexes
DROP INDEX idx_measurements_lookup ON public_measurements;
DROP INDEX idx_repetition ON public_measurements;
```

Then restore old insertion code in pattern-studio.php (direct INSERT without deduplication).

---

**Implementation Date:** January 17, 2026
**Author:** CuttingMaster Team
**Version:** 1.0
