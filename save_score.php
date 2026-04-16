<?php
// save_score.php
// Receives a JSON score record via POST and appends it to scores_data.json
// Deploy this file in the same directory as the simulator HTML files.

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Read and validate the incoming JSON
$raw = file_get_contents('php://input');
$entry = json_decode($raw, true);

if (!$entry || !is_array($entry)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Sanitise required fields
$required = ['name', 'paper', 'date', 'raw', 'grade'];
foreach ($required as $field) {
    if (!isset($entry[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing field: $field"]);
        exit;
    }
}

// Strip any HTML from string fields as a basic precaution
$entry['name']  = htmlspecialchars(strip_tags((string)$entry['name']),  ENT_QUOTES, 'UTF-8');
$entry['paper'] = htmlspecialchars(strip_tags((string)$entry['paper']), ENT_QUOTES, 'UTF-8');

// Add a server-side timestamp for audit purposes
$entry['server_time'] = date('c');

// Path to the scores file — keep it outside the web root if possible,
// or restrict access via .htaccess. Change this path if needed.
$scoresFile = __DIR__ . '/scores_data.json';

// Load existing scores
$scores = [];
if (file_exists($scoresFile)) {
    $existing = file_get_contents($scoresFile);
    $decoded  = json_decode($existing, true);
    if (is_array($decoded)) {
        $scores = $decoded;
    }
}

// Append the new entry
$scores[] = $entry;

// Write back atomically using a temp file
$tmp = $scoresFile . '.tmp';
$written = file_put_contents($tmp, json_encode($scores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($written === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to write score data. Check file permissions.']);
    exit;
}

rename($tmp, $scoresFile);

http_response_code(200);
echo json_encode(['status' => 'ok', 'count' => count($scores)]);
