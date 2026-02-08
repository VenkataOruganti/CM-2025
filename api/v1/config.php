<?php
/**
 * =============================================================================
 * API Configuration
 * =============================================================================
 *
 * Central configuration for API v1 endpoints.
 * Handles CORS, authentication, and common utilities.
 */

// Prevent direct access
if (!defined('API_MODE')) {
    define('API_MODE', true);
}

// CORS Headers for mobile app access
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Error reporting for development (disable in production)
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

// Load database configuration
require_once __DIR__ . '/../../config/database.php';

/**
 * Send JSON response
 *
 * @param mixed $data Response data
 * @param int $statusCode HTTP status code
 * @param bool $success Success flag
 */
function apiResponse($data, $statusCode = 200, $success = true) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'timestamp' => time(),
        'data' => $data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Send error response
 *
 * @param string $message Error message
 * @param int $statusCode HTTP status code
 * @param string $errorCode Error code for client
 */
function apiError($message, $statusCode = 400, $errorCode = 'BAD_REQUEST') {
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'timestamp' => time(),
        'error' => [
            'code' => $errorCode,
            'message' => $message
        ]
    ], JSON_PRETTY_PRINT);
    exit();
}

/**
 * Validate required parameters
 *
 * @param array $required List of required parameter names
 * @param array $params Parameters to check (defaults to $_GET + $_POST)
 * @return array Validated parameters
 */
function validateParams($required, $params = null) {
    if ($params === null) {
        $params = array_merge($_GET, $_POST);
    }

    $missing = [];
    $validated = [];

    foreach ($required as $param) {
        if (!isset($params[$param]) || $params[$param] === '') {
            $missing[] = $param;
        } else {
            $validated[$param] = $params[$param];
        }
    }

    if (!empty($missing)) {
        apiError('Missing required parameters: ' . implode(', ', $missing), 400, 'MISSING_PARAMS');
    }

    return $validated;
}

/**
 * Get request body as JSON
 *
 * @return array Decoded JSON body
 */
function getJsonBody() {
    $body = file_get_contents('php://input');
    if (empty($body)) {
        return [];
    }

    $decoded = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        apiError('Invalid JSON in request body', 400, 'INVALID_JSON');
    }

    return $decoded;
}

/**
 * Simple API key authentication (for basic protection)
 * In production, use JWT or OAuth
 *
 * @param string $apiKey API key from request
 * @return bool Valid or not
 */
function validateApiKey($apiKey = null) {
    // For now, skip validation (implement proper auth later)
    // TODO: Implement JWT authentication for production
    return true;
}
