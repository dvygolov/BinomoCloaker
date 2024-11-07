<?php
require_once __DIR__ . '/passwordcheck.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return http_response_code(500);
}
$updatedColumn = json_decode(file_get_contents('php://input'), true);

// Load existing JSON
$filePath = __DIR__ . '/campaigns.json';
$currentData = json_decode(file_get_contents($filePath), true);

// Update widths
foreach ($currentData['columns'] as &$column) {
    if ($column['field'] !== $updatedColumn['field'])
        continue;
    $column['width'] = $updatedColumn['width'];
    break;
}

// Save back to JSON file
file_put_contents($filePath, json_encode($currentData, JSON_PRETTY_PRINT));
echo json_encode(["status" => "success"]);