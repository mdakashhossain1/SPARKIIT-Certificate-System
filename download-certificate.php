<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/cert_pdf_generator.php';

$id   = isset($_GET['id'])  ? (int)$_GET['id']  : 0;
$type = isset($_GET['type']) ? trim($_GET['type']) : '';

$allowedTypes = ['training', 'participation', 'internship'];

if (!$id || !in_array($type, $allowedTypes, true)) {
    http_response_code(400);
    exit('Invalid request.');
}

$db = getDB();

$stmt = $db->prepare(
    "SELECT id, name, email, courses_selected, days,
            start_date, end_date, certificate_date, show_certificate
     FROM form_submissions WHERE id = ? LIMIT 1"
);
$stmt->execute([$id]);
$cert = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cert || empty($cert['show_certificate'])) {
    http_response_code(403);
    exit('Certificate not available.');
}

$lstmt = $db->prepare("SELECT layout_json FROM certificate_layouts WHERE type = ? LIMIT 1");
$lstmt->execute([$type]);
$layoutRow = $lstmt->fetch(PDO::FETCH_ASSOC);

if (!$layoutRow) {
    http_response_code(404);
    exit('Certificate layout not found.');
}

$layout = json_decode($layoutRow['layout_json'], true);
if (!$layout) {
    http_response_code(500);
    exit('Invalid layout data.');
}

$safeName = preg_replace('/[^a-z0-9_-]/i', '_', $cert['name']);
$filename = $safeName . '_' . $type . '_certificate.pdf';

$pdfData = generateCertificatePdf($cert, $type, $layout);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($pdfData));
echo $pdfData;
exit;
