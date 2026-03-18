<?php
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/helpers.php';

requireAdmin();

$db = getDB();

// Add certificate columns if they don't exist yet
foreach ([
    "ALTER TABLE form_submissions ADD COLUMN start_date        DATE       NULL AFTER remarks",
    "ALTER TABLE form_submissions ADD COLUMN end_date          DATE       NULL AFTER start_date",
    "ALTER TABLE form_submissions ADD COLUMN days              INT        NULL AFTER end_date",
    "ALTER TABLE form_submissions ADD COLUMN certificate_date  DATE       NULL AFTER days",
    "ALTER TABLE form_submissions ADD COLUMN show_certificate  TINYINT(1) NOT NULL DEFAULT 0 AFTER certificate_date",
] as $sql) {
    try { $db->exec($sql); } catch (PDOException $e) { /* column already exists */ }
}

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    header('Location: submissions.php');
    exit;
}

// Fetch submission
$stmt = $db->prepare('SELECT * FROM form_submissions WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    $_SESSION['flash_error'] = 'Submission not found.';
    header('Location: submissions.php');
    exit;
}

$errors   = [];
$success  = false;
$prevShowCert = (int)($row['show_certificate'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect
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
    $show_certificate    = isset($_POST['show_certificate']) ? 1 : 0;

    // Validate
    if (empty($name))    $errors[] = 'Name is required.';
    if (empty($contact) || !preg_match('/^[0-9]{10}$/', $contact))
        $errors[] = 'Valid 10-digit contact number is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Valid email is required.';
    if (empty($whatsapp) || !preg_match('/^[0-9]{10}$/', $whatsapp))
        $errors[] = 'Valid 10-digit WhatsApp number is required.';
    if (empty($college_name)) $errors[] = 'College name is required.';
    if (!in_array($total_program, ['training', 'internship'], true))
        $errors[] = 'Please select a program.';

    $allowedYears = ['first_year', 'second_year', 'third_year', 'fourth_year', 'graduated'];
    $year = in_array($year, $allowedYears, true) ? $year : null;

    $allowedCourses = getCourses();
    $courses = array_filter($courses, fn($c) => in_array($c, $allowedCourses, true));

    if (empty($errors)) {
        // Validate date fields
        $start_date       = $start_date       && strtotime($start_date)       ? $start_date       : null;
        $end_date         = $end_date         && strtotime($end_date)         ? $end_date         : null;
        $certificate_date = $certificate_date && strtotime($certificate_date) ? $certificate_date : null;
        $days             = $days !== '' && is_numeric($days) ? (int)$days : null;

        $upd = $db->prepare(
            'UPDATE form_submissions SET
               name=?, contact=?, email=?, whatsapp=?, college_name=?,
               courses_selected=?, batch=?, year=?,
               total_program=?, internship_duration=?, total_price=?,
               executive_name=?, remarks=?,
               start_date=?, end_date=?, days=?, certificate_date=?, show_certificate=?
             WHERE id=?'
        );
        $upd->execute([
            $name, $contact, $email, $whatsapp, $college_name,
            json_encode(array_values($courses)),
            $batch ?: null,
            $year,
            $total_program,
            $internship_duration ?: null,
            $total_price !== '' ? (float)$total_price : null,
            $executive_name ?: null,
            $remarks ?: null,
            $start_date,
            $end_date,
            $days,
            $certificate_date,
            $show_certificate,
            $id,
        ]);

        // Refresh row
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $success = true;
    }
}

$allCourses = getCourses();
$selectedCourses = json_decode($row['courses_selected'] ?? '[]', true) ?? [];
$durations = INTERNSHIP_DURATIONS;
$months = ['January','February','March','April','May','June',
           'July','August','September','October','November','December'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Submission #<?= $id ?> – <?= e(ORG_NAME) ?> Admin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-light">
<div class="d-flex">
  <?php require dirname(__DIR__) . '/partials/sidebar.php'; ?>

  <main class="flex-grow-1 p-4" style="min-width:0;max-width:860px;">

    <div class="d-flex align-items-center gap-3 mb-4">
      <a href="submissions.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back
      </a>
      <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-pencil-fill me-2 text-warning"></i>Edit Submission #<?= $id ?></h4>
        <small class="text-muted">Submitted <?= date('d M Y H:i', strtotime($row['submitted_at'])) ?></small>
      </div>
    </div>

    <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible">
      <i class="bi bi-check-circle-fill me-2"></i> <?= $_SESSION['flash_success'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible">
      <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= e($_SESSION['flash_error']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible">
      <i class="bi bi-check-circle-fill me-2"></i> Submission updated successfully.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($errors): ?>
    <div class="alert alert-danger">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <?= implode('<br>', array_map('e', $errors)) ?>
    </div>
    <?php endif; ?>

    <form method="POST">
      <!-- Personal Info -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold border-bottom">
          <i class="bi bi-person-fill me-2 text-primary"></i>Personal Information
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="name"
                     value="<?= e($row['name']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" name="email"
                     value="<?= e($row['email']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Contact Number <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="contact"
                     value="<?= e($row['contact']) ?>" maxlength="10"
                     oninput="this.value=this.value.replace(/[^0-9]/g,'')" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">WhatsApp Number <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="whatsapp"
                     value="<?= e($row['whatsapp'] ?? '') ?>" maxlength="10"
                     oninput="this.value=this.value.replace(/[^0-9]/g,'')" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">College Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="college_name"
                     value="<?= e($row['college_name']) ?>" required>
            </div>
          </div>
        </div>
      </div>

      <!-- Program Info -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold border-bottom">
          <i class="bi bi-journal-fill me-2 text-success"></i>Program Information
        </div>
        <div class="card-body">
          <div class="row g-3">

            <!-- Total Program -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Total Program <span class="text-danger">*</span></label>
              <select class="form-select" name="total_program" required>
                <option value="training"   <?= $row['total_program'] === 'training'   ? 'selected' : '' ?>>Training</option>
                <option value="internship" <?= $row['total_program'] === 'internship' ? 'selected' : '' ?>>Internship</option>
              </select>
            </div>

            <!-- Courses -->
            <div class="col-12">
              <label class="form-label fw-semibold">Course / Domain</label>
              <div class="row g-2">
                <?php foreach ($allCourses as $course): ?>
                <div class="col-md-4 col-sm-6">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="courses[]" id="c_<?= e(preg_replace('/\W+/','_',$course)) ?>"
                           value="<?= e($course) ?>"
                           <?= in_array($course, $selectedCourses, true) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="c_<?= e(preg_replace('/\W+/','_',$course)) ?>">
                      <?= e($course) ?>
                    </label>
                  </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($allCourses)): ?>
                  <p class="text-muted small">No courses available.</p>
                <?php endif; ?>
              </div>
            </div>

            <!-- Batch (month only) -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Batch (Month)</label>
              <select class="form-select" name="batch">
                <option value="">– Select month –</option>
                <?php foreach ($months as $m): ?>
                <option value="<?= $m ?>" <?= ($row['batch'] ?? '') === $m ? 'selected' : '' ?>><?= $m ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Year -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Year</label>
              <select class="form-select" name="year">
                <option value="">– Select –</option>
                <?php foreach (['first_year'=>'First Year','second_year'=>'Second Year','third_year'=>'Third Year','fourth_year'=>'Fourth Year','graduated'=>'Graduated'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= ($row['year'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Duration -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Internship Duration</label>
              <select class="form-select" name="internship_duration">
                <option value="">– None –</option>
                <?php foreach ($durations as $d): ?>
                <option value="<?= e($d) ?>" <?= ($row['internship_duration'] ?? '') === $d ? 'selected' : '' ?>><?= e($d) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Price -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Total Price (₹)</label>
              <input type="number" class="form-control" name="total_price"
                     min="0" step="0.01"
                     value="<?= e($row['total_price'] ?? '') ?>"
                     oninput="this.value=this.value.replace(/[^0-9.]/g,'')">
            </div>

          </div>
        </div>
      </div>

      <!-- Certificate Dates -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold border-bottom">
          <i class="bi bi-calendar3 me-2 text-info"></i>Certificate Dates
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Start Date</label>
              <input type="date" class="form-control" name="start_date" id="start_date"
                     value="<?= e($row['start_date'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">End Date</label>
              <input type="date" class="form-control" name="end_date" id="end_date"
                     value="<?= e($row['end_date'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Days</label>
              <input type="number" class="form-control" name="days" id="days"
                     min="1" placeholder="Auto-calculated"
                     value="<?= e($row['days'] ?? '') ?>">
              <div class="form-text">Auto-fills from dates above</div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Certificate Date</label>
              <input type="date" class="form-control" name="certificate_date" id="certificate_date"
                     value="<?= e($row['certificate_date'] ?? '') ?>">
              <div class="form-text">Date printed on certificate</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Admin Info -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold border-bottom">
          <i class="bi bi-sticky-fill me-2 text-warning"></i>Admin Notes
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Executive Name</label>
              <input type="text" class="form-control" name="executive_name"
                     value="<?= e($row['executive_name'] ?? '') ?>">
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <div class="w-100">
                <label class="form-label fw-semibold d-block">Show Certificate on Verify Page</label>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" role="switch"
                         name="show_certificate" id="show_certificate"
                         style="width:3em;height:1.6em;"
                         <?= !empty($row['show_certificate']) ? 'checked' : '' ?>>
                  <label class="form-check-label ms-2 fw-semibold" id="toggleLabel"
                         style="color:<?= !empty($row['show_certificate']) ? '#198754' : '#dc3545' ?>">
                    <?= !empty($row['show_certificate']) ? 'YES — Certificate visible' : 'NO — Certificate hidden' ?>
                  </label>
                </div>
                <div class="form-text">When ON, the rendered certificate is shown on the public verification page.</div>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Remarks</label>
              <textarea class="form-control" name="remarks" rows="3"><?= e($row['remarks'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="d-flex gap-2 flex-wrap align-items-center">
        <button type="submit" class="btn btn-warning fw-semibold">
          <i class="bi bi-save-fill me-1"></i> Save Changes
        </button>
        <a href="submissions.php" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>

    <?php if (!empty($row['show_certificate'])): ?>
    <!-- Send Certificate Email -->
    <div class="card border-0 shadow-sm mt-4" style="border-left:4px solid #7c3aed !important;">
      <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
          <h6 class="mb-1 fw-semibold"><i class="bi bi-envelope-fill me-2 text-purple" style="color:#7c3aed;"></i>Send Certificates via Email</h6>
          <p class="mb-0 text-muted small">
            Email all 3 certificate PDFs (Training, Participation &amp; Internship) directly to
            <strong><?= e($row['email']) ?></strong>
          </p>
        </div>
        <form method="POST" action="send-certificate-email.php"
              onsubmit="return confirm('Send all certificates to <?= e(addslashes($row['email'])) ?>?');">
          <input type="hidden" name="id" value="<?= $id ?>">
          <button type="submit" class="btn btn-purple fw-semibold"
                  style="background:#7c3aed;color:#fff;border:none;">
            <i class="bi bi-send-fill me-1"></i> Send Certificate Email
          </button>
        </form>
      </div>
    </div>
    <?php endif; ?>

  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    const s = document.getElementById('start_date');
    const e = document.getElementById('end_date');
    const d = document.getElementById('days');

    function calcDays() {
        if (!s.value || !e.value) return;
        const diff = (new Date(e.value) - new Date(s.value)) / 86400000;
        if (diff >= 0) d.value = Math.round(diff) + 1; // inclusive
    }

    s.addEventListener('change', calcDays);
    e.addEventListener('change', calcDays);
})();

// Toggle label
(function () {
    const chk = document.getElementById('show_certificate');
    const lbl = document.getElementById('toggleLabel');
    if (!chk) return;
    chk.addEventListener('change', function () {
        if (this.checked) {
            lbl.textContent = 'YES — Certificate visible';
            lbl.style.color = '#198754';
        } else {
            lbl.textContent = 'NO — Certificate hidden';
            lbl.style.color = '#dc3545';
        }
    });
})();
</script>
</body>
</html>
