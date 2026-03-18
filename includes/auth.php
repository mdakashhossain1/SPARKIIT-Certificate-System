<?php
function requireAdmin(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . getAdminBaseUrl() . '/login');
        exit;
    }
}

function getAdminBaseUrl(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Determine subdirectory
    $script   = $_SERVER['SCRIPT_NAME'] ?? '';
    // Find the /admin/ segment
    $pos = strpos($script, '/admin/');
    if ($pos !== false) {
        $base = substr($script, 0, $pos);
    } else {
        $base = dirname(dirname($script));
    }
    return $protocol . '://' . $host . $base . '/admin';
}

function adminLogin(string $email, string $password): bool {
    require_once dirname(__DIR__) . '/includes/db.php';
    $db   = getDB();
    $stmt = $db->prepare('SELECT id, password FROM admins WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);
        $_SESSION['admin_id']    = $admin['id'];
        $_SESSION['admin_email'] = $email;
        return true;
    }
    return false;
}

function adminLogout(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
