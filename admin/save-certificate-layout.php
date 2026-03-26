<?php
require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

requireAdmin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true);
$type   = $input['type']   ?? '';
$layout = $input['layout'] ?? null;

$valid = ['training', 'participation', 'internship'];
if (!in_array($type, $valid, true) || !is_array($layout)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Allowed fields per type (participation only uses name)
$type_fields = [
    'training'      => ['name', 'description', 'date'],
    'participation' => ['name', 'description'],
    'internship'    => ['name', 'description', 'date'],
];
$allowed_keys = ['label','text','left','top','width','fontSize','fontWeight','fontStyle','fontFamily','color','textAlign'];
$clean = [];
foreach ($type_fields[$type] as $f) {
    if (!isset($layout[$f]) || !is_array($layout[$f])) continue;
    $clean[$f] = array_intersect_key($layout[$f], array_flip($allowed_keys));
}

if (count($clean) !== count($type_fields[$type])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Incomplete layout data']);
    exit;
}

try {
    $db = getDB();
    $db->exec("CREATE TABLE IF NOT EXISTS certificate_layouts (
        id   INT AUTO_INCREMENT PRIMARY KEY,
        type ENUM('training','participation','internship') NOT NULL,
        layout_json JSON NOT NULL,
        updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_type (type)
    )");

    $stmt = $db->prepare(
        "INSERT INTO certificate_layouts (type, layout_json)
         VALUES (?, ?)
         ON DUPLICATE KEY UPDATE layout_json = VALUES(layout_json), updated_at = CURRENT_TIMESTAMP"
    );
    $stmt->execute([$type, json_encode($clean)]);

    echo json_encode(['success' => true, 'message' => 'Layout saved successfully']);
} catch (PDOException $e) {
    error_log('save-certificate-layout: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error — check server logs']);
}
