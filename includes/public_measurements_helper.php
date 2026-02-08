<?php
/**
 * ============================================================================
 * PUBLIC MEASUREMENTS HELPER FUNCTIONS
 * ============================================================================
 *
 * This file provides helper functions for managing public measurements with
 * deduplication support. Instead of storing duplicate measurements, we
 * increment a "repetition" counter when similar measurements are submitted.
 *
 * Matching logic: Values within ±0.5 inches are considered a match.
 * For example: 14.5, 14.8, 15.0 are all grouped into the "15" bucket.
 *
 * @author CuttingMaster Team
 * @version 1.0
 * @date 2026-01-17
 * ============================================================================
 */

/**
 * Round measurement to nearest 0.5 increment for grouping similar values
 * Examples:
 *   14.3 → 14.5 (rounds to nearest 0.5)
 *   14.8 → 15.0 (rounds to nearest 0.5)
 *   15.0 → 15.0 (already at 0.5 increment)
 *   NULL → NULL (preserves null values)
 *
 * @param float|null $value The measurement value to round
 * @return float|null The rounded value or null
 */
function roundMeasurement($value) {
    if ($value === null || $value === '') {
        return null;
    }
    // Round to nearest 0.5: multiply by 2, round to integer, divide by 2
    return round($value * 2) / 2;
}

/**
 * Find an existing public measurement record that matches the given measurements
 * within ±0.5" tolerance. All 14 fields are rounded to nearest 0.5 before comparison.
 *
 * @param PDO $pdo Database connection
 * @param string $category Category (women/men/boy/girl)
 * @param array $measurements Associative array of measurement values
 * @return int|null The ID of the matching record, or null if no match found
 */
function findMatchingPublicMeasurement($pdo, $category, $measurements) {
    // Define all 14 saree blouse measurement fields
    $fields = [
        'blength', 'fshoulder', 'shoulder', 'bnDepth', 'fndepth', 'apex',
        'flength', 'chest', 'bust', 'waist', 'slength', 'saround',
        'sopen', 'armhole'
    ];

    // Round all measurements to nearest 0.5 for grouping
    $roundedMeasurements = [];
    foreach ($fields as $field) {
        $roundedMeasurements[$field] = roundMeasurement($measurements[$field] ?? null);
    }

    // Build WHERE clause - check all 14 fields
    // For NULL values, we check "field IS NULL"
    // For non-NULL values, we check "field = rounded_value"
    $whereClauses = ['category = :category'];
    $params = [':category' => $category];

    foreach ($fields as $field) {
        $roundedValue = $roundedMeasurements[$field];

        if ($roundedValue === null) {
            // Match NULL values
            $whereClauses[] = "$field IS NULL";
        } else {
            // Match rounded value (within ±0.5" tolerance)
            $whereClauses[] = "$field = :$field";
            $params[":$field"] = $roundedValue;
        }
    }

    $sql = "SELECT id, repetition FROM public_measurements WHERE " .
           implode(' AND ', $whereClauses) . " LIMIT 1";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return (int)$result['id'];
        }

        return null;

    } catch (PDOException $e) {
        error_log("Error finding matching public measurement: " . $e->getMessage());
        return null;
    }
}

/**
 * Increment the repetition counter for an existing public measurement record
 *
 * @param PDO $pdo Database connection
 * @param int $id The ID of the record to update
 * @return bool True on success, false on failure
 */
function incrementPublicMeasurementRepetition($pdo, $id) {
    try {
        $stmt = $pdo->prepare("
            UPDATE public_measurements
            SET repetition = repetition + 1,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        return true;

    } catch (PDOException $e) {
        error_log("Error incrementing public measurement repetition: " . $e->getMessage());
        return false;
    }
}

/**
 * Insert a new public measurement record with repetition counter initialized to 1
 * All measurement values are rounded to nearest 0.5 before insertion for consistency.
 *
 * @param PDO $pdo Database connection
 * @param string $category Category (women/men/boy/girl)
 * @param string $patternType Pattern type (blouse, kurti, etc.)
 * @param array $measurements Associative array of measurement values
 * @return int|null The ID of the newly inserted record, or null on failure
 */
function insertNewPublicMeasurement($pdo, $category, $patternType, $measurements) {
    // Define all saree blouse measurement fields (14 fields)
    $fields = [
        'blength', 'fshoulder', 'shoulder', 'bnDepth', 'fndepth', 'apex',
        'flength', 'chest', 'bust', 'waist', 'slength', 'saround',
        'sopen', 'armhole'
    ];

    // Round all measurements to nearest 0.5 for consistency
    $roundedMeasurements = [];
    foreach ($fields as $field) {
        $roundedMeasurements[$field] = roundMeasurement($measurements[$field] ?? null);
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO public_measurements (
                category, pattern_type,
                blength, fshoulder, shoulder, bnDepth, fndepth, apex,
                flength, chest, bust, waist, slength, saround, sopen, armhole,
                repetition
            ) VALUES (
                :category, :pattern_type,
                :blength, :fshoulder, :shoulder, :bnDepth, :fndepth, :apex,
                :flength, :chest, :bust, :waist, :slength, :saround, :sopen, :armhole,
                1
            )
        ");

        $stmt->execute([
            ':category' => $category,
            ':pattern_type' => $patternType,
            ':blength' => $roundedMeasurements['blength'],
            ':fshoulder' => $roundedMeasurements['fshoulder'],
            ':shoulder' => $roundedMeasurements['shoulder'],
            ':bnDepth' => $roundedMeasurements['bnDepth'],
            ':fndepth' => $roundedMeasurements['fndepth'],
            ':apex' => $roundedMeasurements['apex'],
            ':flength' => $roundedMeasurements['flength'],
            ':chest' => $roundedMeasurements['chest'],
            ':bust' => $roundedMeasurements['bust'],
            ':waist' => $roundedMeasurements['waist'],
            ':slength' => $roundedMeasurements['slength'],
            ':saround' => $roundedMeasurements['saround'],
            ':sopen' => $roundedMeasurements['sopen'],
            ':armhole' => $roundedMeasurements['armhole']
        ]);

        return (int)$pdo->lastInsertId();

    } catch (PDOException $e) {
        error_log("Error inserting new public measurement: " . $e->getMessage());
        return null;
    }
}

/**
 * Main function to save public measurement with deduplication
 * This is the primary function to call when saving public measurements.
 *
 * Logic:
 * 1. Round all measurements to nearest 0.5
 * 2. Search for existing matching record (all 14 fields within ±0.5")
 * 3. If match found → increment repetition counter
 * 4. If no match → insert new record with repetition = 1
 *
 * @param PDO $pdo Database connection
 * @param string $category Category (women/men/boy/girl)
 * @param string $patternType Pattern type (blouse, kurti, etc.)
 * @param array $measurements Associative array of measurement values
 * @return array Result array with keys: 'status' => 'updated'|'created', 'id' => int
 */
function savePublicMeasurement($pdo, $category, $patternType, $measurements) {
    // Step 1: Try to find existing matching record
    $existingId = findMatchingPublicMeasurement($pdo, $category, $measurements);

    if ($existingId !== null) {
        // Step 2a: Match found - increment repetition
        incrementPublicMeasurementRepetition($pdo, $existingId);
        return [
            'status' => 'updated',
            'id' => $existingId,
            'message' => 'Incremented repetition counter for existing measurement'
        ];
    } else {
        // Step 2b: No match - insert new record
        $newId = insertNewPublicMeasurement($pdo, $category, $patternType, $measurements);
        return [
            'status' => 'created',
            'id' => $newId,
            'message' => 'Created new public measurement record'
        ];
    }
}
