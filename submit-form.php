<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

// --- Collect & Validate ---
$errors = [];

$name                = trim($_POST['name'] ?? '');
$contact             = trim($_POST['contact'] ?? '');
$email               = trim($_POST['email'] ?? '');
$whatsapp            = trim($_POST['whatsapp'] ?? '');
$college_name        = trim($_POST['college_name'] ?? '');
$coursesRaw          = $_POST['courses'] ?? [];
$courses             = is_array($coursesRaw) ? $coursesRaw : [$coursesRaw];
$batch               = trim($_POST['batch'] ?? '');
$year                = trim($_POST['year'] ?? '');
$total_program       = trim($_POST['total_program'] ?? '');
$internship_duration = trim($_POST['internship_duration'] ?? '');
$total_price         = trim($_POST['total_price'] ?? '');
$executive_name      = trim($_POST['executive_name'] ?? '');
$remarks             = trim($_POST['remarks'] ?? '');
$start_date          = trim($_POST['start_date'] ?? '');
$end_date            = trim($_POST['end_date'] ?? '');
$days                = trim($_POST['days'] ?? '');
$certificate_date    = trim($_POST['certificate_date'] ?? '');

// Sanitize date fields
$start_date       = $start_date       && strtotime($start_date)       ? $start_date       : null;
$end_date         = $end_date         && strtotime($end_date)         ? $end_date         : null;
$certificate_date = $certificate_date && strtotime($certificate_date) ? $certificate_date : null;
$days             = $days !== '' && is_numeric($days) ? (int)$days : null;

if (empty($name))         $errors[] = 'Full Name is required.';
if (empty($contact) || !preg_match('/^[0-9]{10}$/', $contact))
    $errors[] = 'Valid 10-digit Contact Number is required.';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
    $errors[] = 'Valid Email ID is required.';
if (empty($whatsapp) || !preg_match('/^[0-9]{10}$/', $whatsapp))
    $errors[] = 'Valid WhatsApp Number is required.';
if (empty($college_name)) $errors[] = 'College Name is required.';
if (empty($courses))      $errors[] = 'Please select at least one Course.';
if (empty($batch))        $errors[] = 'Please select a Batch.';
if (!in_array($total_program, ['training', 'internship'], true))
    $errors[] = 'Please select Total Program.';

// Sanitize courses against allowed list
$allowedCourses = getCourses();
$courses = array_filter($courses, fn($c) => in_array($c, $allowedCourses, true));

// Validate year
$allowedYears = ['first_year', 'second_year', 'third_year', 'fourth_year', 'graduated'];
$year = in_array($year, $allowedYears) ? $year : null;

if (!empty($errors)) {
    header('Location: /?error=' . urlencode(implode(' | ', $errors)));
    exit;
}

try {
    $db   = getDB();

    $stmt = $db->prepare(
        'INSERT INTO form_submissions
         (name, contact, email, whatsapp, college_name, courses_selected,
          batch, year, total_program, internship_duration, total_price, executive_name,
          remarks, certificate_status, start_date, end_date, days, certificate_date)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $name,
        $contact,
        $email,
        $whatsapp,
        $college_name,
        json_encode(array_values($courses)),
        $batch,
        $year,
        $total_program,
        $internship_duration ?: null,
        $total_price !== '' ? (float) $total_price : null,
        $executive_name ?: null,
        $remarks ?: null,
        'pending',
        $start_date,
        $end_date,
        $days,
        $certificate_date,
    ]);

    $submission = [
        'name'               => $name,
        'email'              => $email,
        'college_name'       => $college_name,
        'courses_selected'   => json_encode(array_values($courses)),
        'batch'              => $batch,
        'total_program'      => $total_program,
        'internship_duration'=> $internship_duration,
    ];

    ob_start();
    $orgName = ORG_NAME;
    require __DIR__ . '/templates/email/form-confirmation.php';
    $emailBody = ob_get_clean();

    sendMail(
        $email,
        $name,
        'Your Application Has Been Received – ' . ORG_NAME,
        $emailBody
    );

    header('Location: /?submitted=1');
    exit;

} catch (Exception $e) {
    error_log('Form submission error: ' . $e->getMessage());
    header('Location: /?error=' . urlencode('A server error occurred. Please try again later.'));
    exit;
}
