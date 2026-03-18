<?php
/**
 * Generate batch options: Month Year from Jan 2024 to Dec 2027
 */
function getBatchOptions(): array {
    $batches = [];
    $months  = ['January','February','March','April','May','June',
                 'July','August','September','October','November','December'];
    for ($y = 2024; $y <= 2027; $y++) {
        foreach ($months as $m) {
            $batches[] = $m . ' ' . $y;
        }
    }
    return $batches;
}

/**
 * Escape HTML output
 */
function e(mixed $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Fetch active course titles from the local courses table.
 * Returns a flat array of course title strings.
 */
function getCourses(): array {
    static $courses = null;
    if ($courses !== null) {
        return $courses;
    }
    try {
        require_once __DIR__ . '/db.php';
        $db      = getDB();
        $stmt    = $db->query("SELECT title FROM courses WHERE status = 'active' ORDER BY title ASC");
        $courses = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log('getCourses() DB error: ' . $e->getMessage());
        $courses = [];
    }
    return $courses;
}

/**
 * Format price to INR string
 */
function formatPrice(mixed $amount): string {
    return '₹' . number_format((float) $amount, 2);
}
