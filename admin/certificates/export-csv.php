<?php
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

requireAdmin();

$db = getDB();

// Optional search filter (mirrors submissions page)
$search = trim($_GET['search'] ?? '');
$where  = [];
$params = [];
if ($search !== '') {
    $where[]  = '(name LIKE ? OR email LIKE ? OR college_name LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $db->prepare(
    "SELECT id, name, email, contact, whatsapp, college_name,
            courses_selected, batch, year, total_program,
            internship_duration, total_price, executive_name,
            remarks, start_date, end_date, days, certificate_date,
            show_certificate, certificate_status, submitted_at
     FROM form_submissions
     $whereSQL
     ORDER BY submitted_at DESC"
);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = 'submissions_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');

// UTF-8 BOM so Excel opens correctly
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');

// Header row
fputcsv($out, [
    'ID', 'Name', 'Email', 'Contact', 'WhatsApp', 'College',
    'Courses', 'Batch', 'Year', 'Program', 'Duration',
    'Price (INR)', 'Executive', 'Remarks',
    'Start Date', 'End Date', 'Days', 'Certificate Date',
    'Show Certificate', 'Certificate Status', 'Submitted At',
]);

foreach ($rows as $row) {
    $courses = json_decode($row['courses_selected'] ?? '[]', true);
    $coursesStr = is_array($courses) ? implode('; ', $courses) : '';

    $yearLabels = [
        'first_year'  => 'First Year',
        'second_year' => 'Second Year',
        'third_year'  => 'Third Year',
        'fourth_year' => 'Fourth Year',
        'graduated'   => 'Graduated',
    ];

    fputcsv($out, [
        $row['id'],
        $row['name'],
        $row['email'],
        $row['contact'],
        $row['whatsapp'] ?? '',
        $row['college_name'],
        $coursesStr,
        $row['batch'] ?? '',
        $yearLabels[$row['year'] ?? ''] ?? ($row['year'] ?? ''),
        ucfirst($row['total_program'] ?? ''),
        $row['internship_duration'] ?? '',
        $row['total_price'] ?? '',
        $row['executive_name'] ?? '',
        $row['remarks'] ?? '',
        $row['start_date'] ?? '',
        $row['end_date'] ?? '',
        $row['days'] ?? '',
        $row['certificate_date'] ?? '',
        $row['show_certificate'] ? 'Yes' : 'No',
        ucfirst($row['certificate_status'] ?? ''),
        $row['submitted_at'],
    ]);
}

fclose($out);
exit;
