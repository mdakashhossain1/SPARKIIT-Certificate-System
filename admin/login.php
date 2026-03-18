<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/includes/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Already logged in
if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Both email and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        if (adminLogin($email, $password)) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login – <?= e(ORG_NAME) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
  body { background: linear-gradient(135deg, #1a237e 0%, #283593 50%, #1565c0 100%); min-height: 100vh; }
  .login-card { max-width: 420px; border-radius: 16px; overflow: hidden; }
  .login-header { background: rgba(255,255,255,0.05); }
  .form-control:focus { border-color: #3949ab; box-shadow: 0 0 0 0.25rem rgba(57,73,171,0.25); }
</style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">
<div class="w-100 px-3">
  <div class="card login-card shadow-lg mx-auto">
    <div class="login-header text-center py-4" style="background:linear-gradient(135deg,#1a237e,#3949ab);">
      <i class="bi bi-shield-lock-fill text-white" style="font-size:3rem;"></i>
      <h4 class="text-white mt-2 mb-0"><?= e(ORG_NAME) ?></h4>
      <p class="text-white-50 small mb-0">Certificate System Admin</p>
    </div>
    <div class="card-body p-4">
      <h5 class="text-center mb-4 text-muted">Sign In to Continue</h5>

      <?php if ($error): ?>
      <div class="alert alert-danger py-2 small">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= e($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div class="mb-3">
          <label class="form-label" for="email">Email Address</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope-fill text-muted"></i></span>
            <input type="email" class="form-control" id="email" name="email"
                   placeholder="admin@SPARKIIT.com" required autofocus
                   value="<?= isset($_POST['email']) ? e($_POST['email']) : '' ?>">
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label" for="password">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill text-muted"></i></span>
            <input type="password" class="form-control" id="password" name="password"
                   placeholder="Enter your password" required>
            <button class="btn btn-outline-secondary" type="button" id="togglePwd" tabindex="-1">
              <i class="bi bi-eye" id="eyeIcon"></i>
            </button>
          </div>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-primary py-2">
            <i class="bi bi-box-arrow-in-right me-1"></i> Login
          </button>
        </div>
      </form>
    </div>
  </div>
  <p class="text-center text-white-50 small mt-4">
    &copy; <?= date('Y') ?> <?= e(ORG_NAME) ?> | All rights reserved
  </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('togglePwd').addEventListener('click', function() {
  const pwd  = document.getElementById('password');
  const icon = document.getElementById('eyeIcon');
  if (pwd.type === 'password') {
    pwd.type = 'text';
    icon.className = 'bi bi-eye-slash';
  } else {
    pwd.type = 'password';
    icon.className = 'bi bi-eye';
  }
});
</script>
</body>
</html>
