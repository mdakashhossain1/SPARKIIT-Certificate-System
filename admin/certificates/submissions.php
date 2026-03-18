<?php
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/helpers.php';

requireAdmin();

$db = getDB();

// Pagination
$perPage = 20;
$page    = max(1, (int) ($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

// Search / filter
$search = trim($_GET['search'] ?? '');

$where  = [];
$params = [];
if ($search !== '') {
    $where[]  = '(s.name LIKE ? OR s.email LIKE ? OR s.college_name LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$totalRows = (int) $db->prepare("SELECT COUNT(*) FROM form_submissions s $whereSQL")->execute($params) ? null : 0;
$countStmt = $db->prepare("SELECT COUNT(*) FROM form_submissions s $whereSQL");
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $perPage));

$stmt = $db->prepare(
    "SELECT s.*
     FROM form_submissions s
     $whereSQL
     ORDER BY s.submitted_at DESC
     LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$submissions = $stmt->fetchAll();

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError   = $_SESSION['flash_error']   ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Submissions – <?= e(ORG_NAME) ?> Admin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-light">
<div class="d-flex">
  <?php require dirname(__DIR__) . '/partials/sidebar.php'; ?>

  <main class="flex-grow-1 p-4" style="min-width:0;">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-people-fill me-2 text-primary"></i>Form Submissions</h4>
        <p class="text-muted mb-0 small"><?= $totalRows ?> total record(s)</p>
      </div>
    </div>

    <?php if ($flashSuccess): ?>
    <div class="alert alert-success alert-dismissible">
      <i class="bi bi-check-circle-fill me-2"></i><?= e($flashSuccess) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if ($flashError): ?>
    <div class="alert alert-danger alert-dismissible">
      <i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($flashError) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
          <div class="col-md-5">
            <label class="form-label small fw-semibold mb-1">Search</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" class="form-control" name="search" placeholder="Name, email, or college…" value="<?= e($search) ?>">
            </div>
          </div>
          <div class="col-md-auto">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="submissions.php" class="btn btn-outline-secondary ms-1">Reset</a>
          </div>
        </form>
      </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" style="font-size:0.88rem;">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>College</th>
                <th>Courses</th>
                <th>Batch</th>
                <th>Program</th>
                <th>Duration</th>
                <th>Price</th>
                <th>Executive</th>
                <th>Submitted</th>
                <th>Status</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($submissions)): ?>
              <tr><td colspan="13" class="text-center text-muted py-5">No submissions found.</td></tr>
              <?php else: ?>
                <?php foreach ($submissions as $row): ?>
                <?php
                  $courses = json_decode($row['courses_selected'] ?? '[]', true) ?? [];
                  $isIssued = $row['certificate_status'] === 'issued';
                ?>
                <tr>
                  <td class="text-muted"><?= $row['id'] ?></td>
                  <td class="fw-semibold"><?= e($row['name']) ?></td>
                  <td><?= e($row['email']) ?></td>
                  <td><?= e($row['college_name']) ?></td>
                  <td>
                    <?php if ($courses): ?>
                    <span title="<?= e(implode(', ', $courses)) ?>" class="d-inline-block text-truncate" style="max-width:120px;">
                      <?= e(implode(', ', $courses)) ?>
                    </span>
                    <?php else: ?>–<?php endif; ?>
                  </td>
                  <td><?= e($row['batch'] ?? '–') ?></td>
                  <td><?= e(ucfirst($row['total_program'] ?? '–')) ?></td>
                  <td><?= e($row['internship_duration'] ?? '–') ?></td>
                  <td><?= $row['total_price'] ? e('₹' . number_format((float)$row['total_price'], 0)) : '–' ?></td>
                  <td><?= e($row['executive_name'] ?? '–') ?></td>
                  <td class="text-muted"><?= date('d M Y', strtotime($row['submitted_at'])) ?></td>
                  <td>
                    <?php if ($isIssued): ?>
                      <span class="badge bg-success">Issued</span>
                    <?php else: ?>
                      <span class="badge bg-warning text-dark">Pending</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <!-- View modal trigger -->
                    <button class="btn btn-sm btn-outline-secondary" title="View Details"
                      data-bs-toggle="modal" data-bs-target="#viewModal"
                      data-id="<?= $row['id'] ?>"
                      data-name="<?= e($row['name']) ?>"
                      data-email="<?= e($row['email']) ?>"
                      data-contact="<?= e($row['contact']) ?>"
                      data-whatsapp="<?= e($row['whatsapp'] ?? '') ?>"
                      data-college="<?= e($row['college_name']) ?>"
                      data-courses="<?= e(implode(', ', $courses)) ?>"
                      data-batch="<?= e($row['batch'] ?? '') ?>"
                      data-program="<?= e(ucfirst($row['total_program'] ?? '')) ?>"
                      data-duration="<?= e($row['internship_duration'] ?? '') ?>"
                      data-price="<?= e($row['total_price'] ? '₹' . number_format((float)$row['total_price'], 0) : '') ?>"
                      data-executive="<?= e($row['executive_name'] ?? '') ?>"
                      data-remarks="<?= e($row['remarks'] ?? '') ?>"
                      data-submitted="<?= e(date('d M Y H:i', strtotime($row['submitted_at']))) ?>"
                      data-cert-status="<?= e($row['certificate_status']) ?>">
                      <i class="bi bi-eye"></i>
                    </button>

                    <a href="edit-submission.php?id=<?= $row['id'] ?>"
                       class="btn btn-sm btn-outline-warning ms-1" title="Edit Submission">
                      <i class="bi bi-pencil-fill"></i>
                    </a>

                    <button class="btn btn-sm btn-outline-danger ms-1" title="Delete Submission"
                      data-bs-toggle="modal" data-bs-target="#deleteModal"
                      data-id="<?= $row['id'] ?>"
                      data-name="<?= e($row['name']) ?>">
                      <i class="bi bi-trash-fill"></i>
                    </button>

                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="mt-4">
      <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
          <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
      </ul>
    </nav>
    <?php endif; ?>
  </main>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(135deg,#1a237e,#283593);">
        <h5 class="modal-title text-white"><i class="bi bi-person-circle me-2"></i>Submission Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3" id="viewModalContent"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="bi bi-trash-fill me-2"></i>Delete Submission</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-1">Are you sure you want to delete the submission for:</p>
        <p class="fw-bold fs-6 mb-3" id="delete_student_name"></p>
        <div class="alert alert-warning small mb-0">
          <i class="bi bi-exclamation-triangle-fill me-1"></i>
          This will permanently delete the submission. This action <strong>cannot be undone</strong>.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form method="POST" action="delete-submission.php" id="deleteForm">
          <input type="hidden" name="id" id="delete_id">
          <button type="submit" class="btn btn-danger">
            <i class="bi bi-trash-fill me-1"></i> Yes, Delete
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Populate view modal
document.getElementById('viewModal').addEventListener('show.bs.modal', function(e) {
  const btn = e.relatedTarget;
  const d   = btn.dataset;
  const content = document.getElementById('viewModalContent');

  const rows = [
    ['Name',          d.name],
    ['Email',         d.email],
    ['Contact',       d.contact],
    ['WhatsApp',      d.whatsapp],
    ['College',       d.college],
    ['Domain',        d.domain],
    ['Courses',       d.courses],
    ['Batch',         d.batch],
    ['Program',       d.program],
    ['Duration',      d.duration],
    ['Price',         d.price],
    ['Executive',     d.executive],
    ['Remarks',       d.remarks],
    ['Submitted',     d.submitted],
    ['Cert. Status',  d.certStatus],
  ];

  content.innerHTML = rows.filter(r => r[1]).map(([label, val]) =>
    `<div class="col-md-6">
      <div class="border rounded p-2 bg-light h-100">
        <div class="text-muted small fw-semibold">${label}</div>
        <div>${val || '—'}</div>
      </div>
    </div>`
  ).join('');
});

// Populate delete modal
document.getElementById('deleteModal').addEventListener('show.bs.modal', function(e) {
  const btn = e.relatedTarget;
  document.getElementById('delete_id').value          = btn.dataset.id;
  document.getElementById('delete_student_name').textContent = btn.dataset.name;
});
</script>
</body>
</html>
