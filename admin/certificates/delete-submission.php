<?php
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: submissions');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
if (!$id) {
    $_SESSION['flash_error'] = 'Invalid submission ID.';
    header('Location: submissions');
    exit;
}

try {
    $db = getDB();
    $db->prepare('DELETE FROM form_submissions WHERE id = ?')->execute([$id]);
    $_SESSION['flash_success'] = 'Submission #' . $id . ' has been deleted.';
} catch (Exception $e) {
    error_log('Delete submission error: ' . $e->getMessage());
    $_SESSION['flash_error'] = 'Failed to delete submission. Please try again.';
}

header('Location: submissions');
exit;
