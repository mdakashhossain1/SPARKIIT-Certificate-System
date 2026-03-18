<?php
// Load .env file into $_ENV FIRST — must run before database.php constants are defined
(function () {
    $envFile = dirname(__DIR__) . '/.env';
    if (!is_file($envFile)) return;
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, '"\'');
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
})();

// Load helper functions AFTER .env is parsed so DB constants pick up correct values
require_once __DIR__ . '/../includes/helpers.php';

define('APP_NAME', $_ENV['APP_NAME'] ?? 'SPARKIIT Certificate System');
define('ORG_NAME', $_ENV['ORG_NAME'] ?? 'SPARKIIT');
define('ORG_WEBSITE', $_ENV['ORG_WEBSITE'] ?? 'https://SPARKIIT.com');
define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost/SPARKIIT-certifcate');
define('BASE_PATH', dirname(__DIR__));
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('UPLOADS_URL', BASE_URL . '/uploads');

// Courses are fetched dynamically from DB via getCourses() in includes/helpers.php

// Internship duration options
define('INTERNSHIP_DURATIONS', [
    '1 Month',
    '2 Months',
    '3 Months',
    '6 Months',
    '1 Year',
]);
