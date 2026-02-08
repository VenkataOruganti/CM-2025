<?php
/**
 * AJAX endpoint to save paper size selection to session
 * This allows the paper size selection to persist during the user's session
 */

session_start();

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['paper_size'])) {
    echo json_encode(['success' => false, 'message' => 'Paper size not provided']);
    exit;
}

$paperSize = strtoupper(trim($input['paper_size']));

// Validate paper size (must be one of the allowed values)
$allowedSizes = ['A0', 'A2', 'A3', 'A4', 'LETTER', 'US_LETTER', 'USLETTER', 'LEGAL', 'TABLOID'];

if (!in_array($paperSize, $allowedSizes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid paper size']);
    exit;
}

// Save to session
$_SESSION['paper_size'] = $paperSize;

echo json_encode([
    'success' => true,
    'message' => 'Paper size saved',
    'paper_size' => $paperSize
]);
