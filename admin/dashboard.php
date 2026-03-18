<?php
require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

requireAdmin();

$db = getDB();

// Stats
$totalSubmissions = (int) $db->query('SELECT COUNT(*) FROM form_submissions')->fetchColumn();

// Recent submissions
$recentStmt = $db->query(
    'SELECT id, name, email, college_name, batch, certificate_status, submitted_at
     FROM form_submissions ORDER BY submitted_at DESC LIMIT 5'
);
$recentSubmissions = $recentStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard – <?= e(ORG_NAME) ?> Admin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-light">
<div class="d-flex">
  <?php require __DIR__ . '/partials/sidebar.php'; ?>

  <main class="flex-grow-1 p-4" style="min-height:100vh;overflow-y:auto;">
    <!-- Page header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard</h4>
        <p class="text-muted mb-0 small">Welcome back, <?= e($_SESSION['admin_email'] ?? 'Admin') ?></p>
      </div>
      <span class="text-muted small"><?= date('l, d F Y') ?></span>
    </div>

    <!-- Stat cards -->
    <div class="row g-4 mb-4">
      <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="rounded-3 p-3" style="background:#e3f2fd;">
              <i class="bi bi-people-fill fs-3 text-primary"></i>
            </div>
            <div>
              <div class="fs-2 fw-bold text-primary"><?= $totalSubmissions ?></div>
              <div class="text-muted small">Total Submissions</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="rounded-3 p-3" style="background:#e8f5e9;">
              <i class="bi bi-list-ul fs-3 text-success"></i>
            </div>
            <div>
              <a href="certificates/submissions.php" class="text-decoration-none">
                <div class="fs-5 fw-bold text-success">View Submissions</div>
                <div class="text-muted small">Manage all form submissions</div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick links -->
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <a href="certificates/submissions.php" class="card border-0 shadow-sm text-decoration-none text-dark hover-card">
          <div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-list-ul fs-4 text-primary"></i>
            <div>
              <div class="fw-semibold">Manage Submissions</div>
              <small class="text-muted">View all form submissions</small>
            </div>
            <i class="bi bi-chevron-right ms-auto text-muted"></i>
          </div>
        </a>
      </div>
      <div class="col-md-6">
        <a href="<?= BASE_URL ?>/" target="_blank" class="card border-0 shadow-sm text-decoration-none text-dark hover-card">
          <div class="card-body d-flex align-items-center gap-3">
            <i class="bi bi-globe fs-4 text-success"></i>
            <div>
              <div class="fw-semibold">Public Form</div>
              <small class="text-muted">View enrollment form</small>
            </div>
            <i class="bi bi-box-arrow-up-right ms-auto text-muted"></i>
          </div>
        </a>
      </div>
    </div>

    <!-- Recent Submissions -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Submissions</h6>
        <a href="certificates/submissions.php" class="btn btn-sm btn-outline-primary">View All</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>College</th>
                <th>Batch</th>
                <th>Status</th>
                <th>Submitted</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recentSubmissions)): ?>
              <tr>
                <td colspan="7" class="text-center text-muted py-4">No submissions yet.</td>
              </tr>
              <?php else: ?>
                <?php foreach ($recentSubmissions as $row): ?>
                <tr>
                  <td class="text-muted small"><?= $row['id'] ?></td>
                  <td class="fw-semibold"><?= e($row['name']) ?></td>
                  <td class="text-muted small"><?= e($row['email']) ?></td>
                  <td class="small"><?= e($row['college_name']) ?></td>
                  <td class="small"><?= e($row['batch'] ?? '–') ?></td>
                  <td>
                    <?php if ($row['certificate_status'] === 'issued'): ?>
                      <span class="badge bg-success">Issued</span>
                    <?php else: ?>
                      <span class="badge bg-warning text-dark">Pending</span>
                    <?php endif; ?>
                  </td>
                  <td class="small text-muted"><?= date('d M Y', strtotime($row['submitted_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
