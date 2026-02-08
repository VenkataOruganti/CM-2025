<?php
/**
 * =============================================================================
 * API: Pattern Nodes Endpoint
 * =============================================================================
 *
 * Returns pattern nodes and paths as JSON for mobile app rendering.
 * Mobile app can reconstruct SVG from this data and generate PDF locally.
 *
 * ENDPOINT: GET /api/v1/pattern/nodes.php
 *
 * PARAMETERS:
 *   - measurement_id (required): Measurement ID to generate pattern for
 *   - customer_id (optional): Customer ID (alternative to measurement_id)
 *   - patterns (optional): Comma-separated list of patterns to include
 *                          Default: "front,back,sleeve"
 *                          Options: front, back, sleeve, patti
 *
 * RESPONSE: JSON with nodes, paths, and metadata for each pattern
 *
 * EXAMPLE:
 *   GET /api/v1/pattern/nodes.php?measurement_id=97
 *   GET /api/v1/pattern/nodes.php?measurement_id=97&patterns=front,sleeve
 *
 * DATA SIZE: ~5-10KB (vs ~150KB for full SVG)
 */

require_once __DIR__ . '/../config.php';

// =============================================================================
// VALIDATE REQUEST
// =============================================================================

$measurementId = $_GET['measurement_id'] ?? $_GET['id'] ?? null;
$customerId = $_GET['customer_id'] ?? null;

if (!$measurementId && !$customerId) {
    apiError('Either measurement_id or customer_id is required', 400, 'MISSING_PARAMS');
}

// Which patterns to include
$requestedPatterns = isset($_GET['patterns'])
    ? array_map('trim', explode(',', strtolower($_GET['patterns'])))
    : ['front', 'back', 'sleeve'];

// Compact mode - removes labels, codes, and extra data for minimal transfer
$compactMode = isset($_GET['compact']) && $_GET['compact'] === '1';

// =============================================================================
// LOAD MEASUREMENTS FROM DATABASE
// =============================================================================

try {
    global $pdo;

    if ($measurementId) {
        $stmt = $pdo->prepare("
            SELECT m.*, c.customer_name, c.id as cust_id
            FROM measurements m
            LEFT JOIN customers c ON m.customer_id = c.id
            WHERE m.id = ?
        ");
        $stmt->execute([$measurementId]);
    } else {
        $stmt = $pdo->prepare("
            SELECT m.*, c.customer_name, c.id as cust_id
            FROM measurements m
            LEFT JOIN customers c ON m.customer_id = c.id
            WHERE m.customer_id = ?
            ORDER BY m.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$customerId]);
    }

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        apiError('Measurement not found', 404, 'NOT_FOUND');
    }

} catch (PDOException $e) {
    apiError('Database error: ' . $e->getMessage(), 500, 'DB_ERROR');
}

// =============================================================================
// SET UP PATTERN GENERATION CONTEXT
// =============================================================================

// Define constants for API mode
define('COMPOSITE_MODE', true);
define('PATTERN_CONFIG_LOADED', true);

// Set up GET parameters for pattern files
$_GET['id'] = $row['id'];
$_GET['measurement_id'] = $row['id'];
$_GET['customer_id'] = $row['cust_id'] ?? $row['customer_id'];

// Load shared configuration (this loads measurements into global vars)
require_once __DIR__ . '/../../../patterns/saree_blouses/blousePatterns/patternConfig.php';

// =============================================================================
// GENERATE PATTERN DATA
// =============================================================================

$patternOutput = [
    'metadata' => [
        'measurement_id' => intval($row['id']),
        'customer_id' => intval($row['cust_id'] ?? $row['customer_id']),
        'customer_name' => $row['customer_name'] ?? 'Customer',
        'generated_at' => time(),
        'api_version' => '1.0',
        'scale' => $scale // pixels per inch (25.4)
    ],
    'measurements' => [
        'bust' => floatval($bust),
        'chest' => floatval($chest),
        'waist' => floatval($waist),
        'shoulder' => floatval($shoulder ?? $fshoulder),
        'fshoulder' => floatval($fshoulder),
        'bnDepth' => floatval($bnDepth),
        'fndepth' => floatval($fndepth ?? $frontNeckDepth ?? 0),
        'armhole' => floatval($armhole),
        'blength' => floatval($blength),
        'flength' => floatval($flength),
        'apex' => floatval($apex),
        'slength' => floatval($slength),
        'saround' => floatval($saround),
        'sopen' => floatval($sopen)
    ],
    'derived' => [
        'qBust' => floatval($qBust),
        'qWaist' => floatval($qWaist),
        'qChest' => floatval($qChest),
        'halfShoulder' => floatval($halfShoulder),
        'armHoleHeight' => floatval($armHoleHeight),
        'armHoleDepth' => floatval($armHoleDepth)
    ],
    'patterns' => []
];

// =============================================================================
// GENERATE EACH REQUESTED PATTERN
// =============================================================================

// Capture output and include pattern files
ob_start();

// Front pattern (includes patti)
if (in_array('front', $requestedPatterns)) {
    include __DIR__ . '/../../../patterns/saree_blouses/blousePatterns/sariBlouseFront.php';

    // Extract nodes and paths from frontPatternData
    if (isset($frontPatternData)) {
        $patternOutput['patterns']['front'] = extractNodesAndPaths(
            $frontPatternData,
            $frontNodes ?? [],
            'front'
        );
    }

    // If patti is requested separately or included with front
    if (in_array('patti', $requestedPatterns) && isset($pattiNodes)) {
        $patternOutput['patterns']['patti'] = [
            'name' => 'PATTI',
            'viewBox' => isset($pattiSvgWidth, $pattiSvgHeight)
                ? "0 0 $pattiSvgWidth $pattiSvgHeight"
                : "0 0 300 200",
            'nodes' => formatNodes($pattiNodes),
            'paths' => [],
            'labels' => []
        ];
    }
}

// Back pattern
if (in_array('back', $requestedPatterns)) {
    include __DIR__ . '/../../../patterns/saree_blouses/blousePatterns/sariBlouseBack.php';

    if (isset($backPatternData)) {
        $patternOutput['patterns']['back'] = extractNodesAndPaths(
            $backPatternData,
            $backNodes ?? [],
            'back'
        );
    }
}

// Sleeve pattern
if (in_array('sleeve', $requestedPatterns)) {
    include __DIR__ . '/../../../patterns/saree_blouses/blousePatterns/sariSleeve.php';

    if (isset($sleevePatternData)) {
        $patternOutput['patterns']['sleeve'] = extractNodesAndPaths(
            $sleevePatternData,
            $sleeveNodes ?? [],
            'sleeve'
        );
    }
}

ob_end_clean();

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

/**
 * Extract nodes and paths from pattern data
 */
function extractNodesAndPaths($patternData, $nodes, $patternType) {
    global $compactMode;

    $result = [
        'name' => $patternData['name'] ?? strtoupper($patternType),
        'viewBox' => '',
        'nodes' => formatNodes($nodes),
        'paths' => []
    ];

    // Add type only in non-compact mode
    if (!$compactMode) {
        $result['type'] = $patternData['type'] ?? $patternType;
        $result['labels'] = [];
    }

    // Extract viewBox from SVG content
    if (isset($patternData['svg_content'])) {
        if (preg_match('/viewBox="([^"]+)"/', $patternData['svg_content'], $match)) {
            $result['viewBox'] = $match[1];
        }

        // Extract paths from SVG
        $result['paths'] = extractPathsFromSvg($patternData['svg_content']);

        // Extract text labels from SVG (skip in compact mode)
        if (!$compactMode) {
            $result['labels'] = extractLabelsFromSvg($patternData['svg_content']);
        }
    }

    // Add any pre-calculated paths
    if (isset($patternData['paths'])) {
        foreach ($patternData['paths'] as $key => $path) {
            if (is_string($path)) {
                $result['paths'][$key] = $path;
            }
        }
    }

    return $result;
}

/**
 * Format nodes array for JSON output
 */
function formatNodes($nodes, $compact = false) {
    global $compactMode;
    $compact = $compact || $compactMode;
    $formatted = [];

    foreach ($nodes as $key => $node) {
        if ($compact) {
            // Compact: just [x, y] array
            $formatted[$key] = [round(floatval($node['x']), 1), round(floatval($node['y']), 1)];
        } else {
            $formatted[$key] = [
                'x' => round(floatval($node['x']), 2),
                'y' => round(floatval($node['y']), 2)
            ];

            // Include label if present
            if (isset($node['label'])) {
                $formatted[$key]['label'] = $node['label'];
            }

            // Include code/formula if present (useful for debugging)
            if (isset($node['code'])) {
                $formatted[$key]['code'] = $node['code'];
            }
        }
    }

    return $formatted;
}

/**
 * Extract SVG paths from SVG content
 */
function extractPathsFromSvg($svgContent) {
    $paths = [];
    $pathIndex = 0;

    // Match <path> elements
    preg_match_all('/<path[^>]*d="([^"]+)"[^>]*>/i', $svgContent, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $pathData = $match[1];
        $fullTag = $match[0];

        // Determine path type from stroke color or class
        $pathType = 'outline';
        if (strpos($fullTag, '#DC2626') !== false || strpos($fullTag, 'red') !== false) {
            $pathType = 'cutting_' . $pathIndex;
        } elseif (strpos($fullTag, '#808080') !== false || strpos($fullTag, 'gray') !== false) {
            $pathType = 'fold_' . $pathIndex;
        } elseif (strpos($fullTag, 'stroke-dasharray') !== false) {
            $pathType = 'dashed_' . $pathIndex;
        } else {
            $pathType = 'path_' . $pathIndex;
        }

        $paths[$pathType] = $pathData;
        $pathIndex++;
    }

    // Match <line> elements and convert to path format
    preg_match_all('/<line[^>]*x1="([^"]+)"[^>]*y1="([^"]+)"[^>]*x2="([^"]+)"[^>]*y2="([^"]+)"[^>]*>/i', $svgContent, $lineMatches, PREG_SET_ORDER);

    foreach ($lineMatches as $i => $match) {
        $paths['line_' . $i] = sprintf('M %s,%s L %s,%s', $match[1], $match[2], $match[3], $match[4]);
    }

    return $paths;
}

/**
 * Extract text labels from SVG content
 */
function extractLabelsFromSvg($svgContent) {
    $labels = [];

    // Match <text> elements
    preg_match_all('/<text[^>]*x="([^"]+)"[^>]*y="([^"]+)"[^>]*(?:transform="([^"]*)")?[^>]*>([^<]+)<\/text>/i', $svgContent, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $label = [
            'x' => round(floatval($match[1]), 2),
            'y' => round(floatval($match[2]), 2),
            'text' => trim($match[4])
        ];

        // Extract rotation if present
        if (!empty($match[3]) && preg_match('/rotate\(([^,\)]+)/', $match[3], $rotMatch)) {
            $label['rotation'] = floatval($rotMatch[1]);
        }

        $labels[] = $label;
    }

    return $labels;
}

// =============================================================================
// SEND RESPONSE
// =============================================================================

// In compact mode, use minimal JSON encoding (no pretty print)
if ($compactMode) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => true,
        'data' => $patternOutput
    ]);
    exit();
}

apiResponse($patternOutput);
