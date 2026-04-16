<?php
// get_scores.php
// Returns the full scores_data.json contents as a JSON array.
// This endpoint is called by scores_private.html.
// NOTE: scores_private.html is itself PIN-protected on the client side.
// For stronger security, add server-side authentication here too
// (e.g. check a session variable or HTTP Basic Auth).

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$scoresFile = __DIR__ . '/scores_data.json';

if (!file_exists($scoresFile)) {
    // No scores yet — return empty array
    echo json_encode([]);
    exit;
}

$data = file_get_contents($scoresFile);
$scores = json_decode($data, true);

if (!is_array($scores)) {
    // File exists but is malformed
    http_response_code(500);
    echo json_encode(['error' => 'Score data is malformed']);
    exit;
}

echo json_encode($scores);
