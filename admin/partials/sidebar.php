<?php
// Detect current page for active state
$currentFile = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));

function isActive(string $file, string $currentFile): string {
    return $currentFile === $file ? 'active' : '';
}
?>
<nav class="sidebar d-flex flex-column" style="width:260px;min-height:100vh;background:linear-gradient(180deg,#1a237e,#283593);color:#fff;flex-shrink:0;">
  <!-- Brand -->
  <div class="p-3 border-bottom border-white border-opacity-10">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-award-fill fs-4 text-warning"></i>
      <div>
        <div class="fw-bold"><?= e(ORG_NAME) ?></div>
        <small class="text-white-50">Certificate System</small>
      </div>
    </div>
  </div>

  <!-- Navigation -->
  <ul class="nav flex-column p-3 gap-1 flex-grow-1">
    <li class="nav-item">
      <a href="<?= BASE_URL ?>/admin/dashboard" class="nav-link d-flex align-items-center gap-2 rounded px-3 py-2 text-white <?= isActive('dashboard.php', $currentFile) ?>" style="<?= isActive('dashboard.php', $currentFile) ? 'background:rgba(255,255,255,0.15);' : '' ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>
    </li>
    <li class="nav-item">
      <a href="<?= BASE_URL ?>/admin/certificates/submissions" class="nav-link d-flex align-items-center gap-2 rounded px-3 py-2 text-white <?= isActive('submissions.php', $currentFile) ?>" style="<?= isActive('submissions.php', $currentFile) ? 'background:rgba(255,255,255,0.15);' : '' ?>">
        <i class="bi bi-people-fill"></i> Submissions
      </a>
    </li>
    <li class="nav-item">
      <a href="<?= BASE_URL ?>/admin/certificate-builder" class="nav-link d-flex align-items-center gap-2 rounded px-3 py-2 text-white <?= isActive('certificate-builder.php', $currentFile) ?>" style="<?= isActive('certificate-builder.php', $currentFile) ? 'background:rgba(255,255,255,0.15);' : '' ?>">
        <i class="bi bi-layout-text-window-reverse"></i> Cert Builder
      </a>
    </li>

  </ul>

  <!-- User info + logout -->
  <div class="p-3 border-top border-white border-opacity-10">
    <div class="d-flex align-items-center justify-content-between">
      <div>
        <i class="bi bi-person-circle me-1"></i>
        <small><?= e($_SESSION['admin_email'] ?? 'Admin') ?></small>
      </div>
      <a href="<?= BASE_URL ?>/admin/logout" class="btn btn-sm btn-outline-light py-0 px-2" title="Logout">
        <i class="bi bi-box-arrow-right"></i>
      </a>
    </div>
  </div>
</nav>
