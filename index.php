<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/helpers.php';

$success = false;
$error   = '';

if (isset($_GET['submitted']) && $_GET['submitted'] === '1') {
    $success = true;
}
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}

$batches   = getBatchOptions();
$courses   = getCourses();
$durations = INTERNSHIP_DURATIONS;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enrollment Form – <?= e(ORG_NAME) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
/* ── Google Forms Global ── */
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  background: #f0ebf8;
  font-family: 'Google Sans', Roboto, Arial, sans-serif;
  font-size: 14px;
  color: #202124;
  min-height: 100vh;
}

.gf-page {
  max-width: 640px;
  margin: 0 auto;
  padding: 24px 12px 48px;
}

/* Title card */
.gf-title-card {
  background: #fff;
  border-radius: 8px;
  border-top: 10px solid #673ab7;
  padding: 24px;
  margin-bottom: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.12);
}
.gf-title-card h1 {
  font-size: 26px;
  font-weight: 400;
  color: #202124;
  margin-bottom: 8px;
}
.gf-title-card p {
  font-size: 13px;
  color: #5f6368;
  margin-bottom: 4px;
}
.gf-required-note {
  font-size: 13px;
  color: #d93025;
  margin-top: 8px;
}

/* Question card */
.gf-card {
  background: #fff;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
  padding: 24px;
  margin-bottom: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
  transition: border-color 0.15s;
}
.gf-card.gf-error {
  border-color: #d93025;
  border-left: 4px solid #d93025;
}

.gf-label {
  font-size: 14px;
  font-weight: 500;
  color: #202124;
  margin-bottom: 16px;
  line-height: 1.5;
  display: block;
}
.gf-required {
  color: #d93025;
  margin-left: 3px;
}

/* Underline text input */
.gf-input {
  width: 100%;
  border: none;
  border-bottom: 1px solid #9e9e9e;
  outline: none;
  padding: 6px 0;
  font-size: 14px;
  color: #202124;
  background: transparent;
  transition: border-color 0.2s;
  max-width: 400px;
}
.gf-input:focus {
  border-bottom: 2px solid #673ab7;
}
.gf-input::placeholder { color: #9e9e9e; }

/* Underline select */
.gf-select {
  width: 100%;
  max-width: 400px;
  border: none;
  border-bottom: 1px solid #9e9e9e;
  outline: none;
  padding: 6px 0;
  font-size: 14px;
  color: #202124;
  background: transparent;
  cursor: pointer;
  appearance: none;
  -webkit-appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%235f6368' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 4px center;
}
.gf-select:focus { border-bottom: 2px solid #673ab7; }

/* Textarea */
.gf-textarea {
  width: 100%;
  border: none;
  border-bottom: 1px solid #9e9e9e;
  outline: none;
  padding: 6px 0;
  font-size: 14px;
  color: #202124;
  background: transparent;
  resize: vertical;
  min-height: 60px;
}
.gf-textarea:focus { border-bottom: 2px solid #673ab7; }

/* Radio option row */
.gf-radio-row {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 10px 4px;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.12s;
}
.gf-radio-row:hover { background: #f1f3f4; }
.gf-radio-native { display: none; }
.gf-radio-circle {
  width: 20px; height: 20px; min-width: 20px;
  border-radius: 50%;
  border: 2px solid #757575;
  display: flex; align-items: center; justify-content: center;
  transition: border-color 0.15s;
}
.gf-radio-circle::after {
  content: '';
  width: 10px; height: 10px;
  border-radius: 50%;
  background: #673ab7;
  opacity: 0;
  transition: opacity 0.15s;
}
.gf-radio-native:checked ~ .gf-radio-circle { border-color: #673ab7; }
.gf-radio-native:checked ~ .gf-radio-circle::after { opacity: 1; }
.gf-radio-text { font-size: 14px; color: #202124; }

/* Error message */
.gf-error-msg {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #d93025;
  font-size: 12px;
  margin-top: 8px;
}
.gf-error-msg i { font-size: 14px; }

/* Submit row */
.gf-submit-row {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-top: 8px;
}
.gf-btn-submit {
  background: #673ab7;
  color: #fff;
  border: none;
  border-radius: 4px;
  padding: 10px 24px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  letter-spacing: 0.3px;
  transition: background 0.15s, box-shadow 0.15s;
}
.gf-btn-submit:hover { background: #5e35b1; box-shadow: 0 2px 6px rgba(103,58,183,0.4); }
.gf-btn-submit:disabled { background: #9e9e9e; cursor: not-allowed; }
.gf-btn-reset {
  background: none;
  border: none;
  color: #673ab7;
  font-size: 14px;
  cursor: pointer;
  padding: 10px 12px;
  border-radius: 4px;
}
.gf-btn-reset:hover { background: #f3e5f5; }

/* Batch grid */
.batch-grid-wrap {
  overflow-x: auto;
  margin-top: 8px;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
}
.batch-grid {
  border-collapse: collapse;
  min-width: 700px;
  width: 100%;
}
.batch-grid thead tr {
  border-bottom: 1px solid #e0e0e0;
}
.batch-col-header {
  font-size: 12px;
  font-weight: 500;
  color: #5f6368;
  text-align: center;
  padding: 10px 6px 8px;
  white-space: nowrap;
}
.batch-row-label {
  font-size: 13px;
  font-weight: 500;
  color: #202124;
  padding: 10px 12px;
  border-right: 1px solid #e0e0e0;
  background: #fafafa;
  min-width: 90px;
  vertical-align: middle;
}
.batch-row-desc {
  color: #c0392b;
  font-size: 12px;
  font-weight: 500;
  line-height: 1.5;
  white-space: normal;
}
.batch-grid tbody tr {
  border-bottom: 1px solid #f0f0f0;
}
.batch-grid tbody tr:last-child { border-bottom: none; }
.batch-cell {
  text-align: center;
  padding: 8px 4px;
}
.batch-radio-label {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  padding: 4px;
  border-radius: 50%;
  transition: background 0.12s;
}
.batch-radio-label:hover { background: #f1f3f4; }
.batch-radio-label .gf-radio-circle { margin: 0; }

/* Success banner */
.gf-success {
  background: #e6f4ea;
  border: 1px solid #a8d5b5;
  border-radius: 8px;
  padding: 16px 20px;
  margin-bottom: 12px;
  color: #137333;
  font-size: 14px;
}
.gf-error-banner {
  background: #fce8e6;
  border: 1px solid #f5c6c2;
  border-radius: 8px;
  padding: 16px 20px;
  margin-bottom: 12px;
  color: #d93025;
  font-size: 14px;
}
</style>
</head>
<body>

<div class="gf-page">
  <form id="enrollmentForm" action="submit-form.php" method="POST" novalidate>

    <?php if ($success): ?>
    <div class="gf-success">
      <i class="bi bi-check-circle-fill me-2"></i>
      <strong>Application Submitted Successfully!</strong><br>
      A confirmation email has been sent to your registered email address.
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="gf-error-banner">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <strong>Error:</strong> <?= $error ?>
    </div>
    <?php endif; ?>

    <!-- Title Card -->
    <div class="gf-title-card">
      <h1><?= e(ORG_NAME) ?> – Enrollment Form</h1>
      <p>Industrial Training &amp; Internship Application</p>
      <div class="gf-required-note">* Indicates required question</div>
    </div>

    <!-- Full Name -->
    <div class="gf-card" id="card_name">
      <label class="gf-label" for="name">NAME <span class="gf-required">*</span></label>
      <input type="text" class="gf-input" id="name" name="name"
             placeholder="Your answer" required
             value="<?= isset($_POST['name']) ? e($_POST['name']) : '' ?>">
      <div class="gf-error-msg" id="err_name" style="display:none;">
        <i class="bi bi-exclamation-circle-fill"></i> This is a required question
      </div>
    </div>

    <!-- Contact Number -->
    <div class="gf-card" id="card_contact">
      <label class="gf-label" for="contact">CONTACT NUMBER <span class="gf-required">*</span></label>
      <input type="tel" class="gf-input" id="contact" name="contact"
             placeholder="Your answer" required pattern="[0-9]{10}"
             value="<?= isset($_POST['contact']) ? e($_POST['contact']) : '' ?>">
      <div class="gf-error-msg" id="err_contact" style="display:none;">
        <i class="bi bi-exclamation-circle-fill"></i> Enter a valid 10-digit number
      </div>
    </div>

    <!-- Email -->
    <div class="gf-card" id="card_email">
      <label class="gf-label" for="email">EMAIL ID <span class="gf-required">*</span></label>
      <input type="email" class="gf-input" id="email" name="email"
             placeholder="Your answer" required
             value="<?= isset($_POST['email']) ? e($_POST['email']) : '' ?>">
      <div class="gf-error-msg" id="err_email" style="display:none;">
        <i class="bi bi-exclamation-circle-fill"></i> Enter a valid email address
      </div>
    </div>

    <!-- WhatsApp -->
    <div class="gf-card" id="card_whatsapp">
      <label class="gf-label" for="whatsapp">WHATSAPP NUMBER <span class="gf-required">*</span></label>
      <input type="tel" class="gf-input" id="whatsapp" name="whatsapp"
             placeholder="Your answer" required pattern="[0-9]{10}"
             value="<?= isset($_POST['whatsapp']) ? e($_POST['whatsapp']) : '' ?>">
      <div class="gf-error-msg" id="err_whatsapp" style="display:none;">
        <i class="bi bi-exclamation-circle-fill"></i> Enter a valid 10-digit number
      </div>
    </div>

    <!-- College -->
    <div class="gf-card" id="card_college">
      <label class="gf-label" for="college_name">COLLEGE NAME <span class="gf-required">*</span></label>
      <input type="text" class="gf-input" id="college_name" name="college_name"
             placeholder="Your answer" required
             value="<?= isset($_POST['college_name']) ? e($_POST['college_name']) : '' ?>">
      <div class="gf-error-msg" id="err_college" style="display:none;">
        <i class="bi bi-exclamation-circle-fill"></i> This is a required question
      </div>
    </div>

    <!-- Courses -->
    <div class="gf-card" id="card_courses">
      <span class="gf-label">AVAILABLE DOMAINS FOR INDUSTRIAL TRAINING AND INTERNSHIP PROGRAM <span class="gf-required">*</span></span>
      <?php foreach ($courses as $course):
        $cid = 'course_' . preg_replace('/\W+/', '_', $course);
        $checked = (isset($_POST['courses']) && (is_array($_POST['courses'])
          ? in_array($course, $_POST['courses'], true)
          : $_POST['courses'] === $course)) ? 'checked' : '';
      ?>
      <label class="gf-radio-row" for="<?= e($cid) ?>">
        <input class="gf-radio-native course-radio" type="radio"
               name="courses[]" id="<?= e($cid) ?>" value="<?= e($course) ?>" required <?= $checked ?>>
        <span class="gf-radio-circle"></span>
        <span class="gf-radio-text"><?= e($course) ?></span>
      </label>
      <?php endforeach; ?>
      <?php if (empty($courses)): ?>
        <p style="color:#9e9e9e;font-size:13px;margin-top:8px;">No courses available. Please contact admin.</p>
      <?php endif; ?>
      <div class="gf-error-msg" id="err_courses" style="display:none;">
        <i class="bi bi-exclamation-circle-fill"></i> This is a required question
      </div>
    </div>

    <!-- Select Batch -->
    <div class="gf-card" id="card_batch">
      <span class="gf-label">SELECT BATCH <span class="gf-required">*</span></span>
      <div class="batch-grid-wrap">
        <table class="batch-grid">
          <thead>
            <tr>
              <th class="batch-row-label"></th>
              <?php foreach (['January','February','March','April','May','June','July','August','September','October','November','December'] as $mon): ?>
              <th class="batch-col-header"><?= $mon ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="batch-row-label batch-row-desc">Choose<br>your<br>desired<br>batch</td>
              <?php foreach (['January','February','March','April','May','June','July','August','September','October','November','December'] as $mon):
                $val     = $mon;
                $checked = (isset($_POST['batch']) && $_POST['batch'] === $val) ? 'checked' : '';
                $bid     = 'batch_' . $mon;
              ?>
              <td class="batch-cell">
                <label for="<?= $bid ?>" class="batch-radio-label">
                  <input class="gf-radio-native batch-radio" type="radio"
                         name="batch" id="<?= $bid ?>" value="<?= e($val) ?>" <?= $checked ?>>
                  <span class="gf-radio-circle"></span>
                </label>
              </td>
              <?php endforeach; ?>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="gf-error-msg" id="err_batch" style="display:none;">
        <i class="bi bi-exclamation-circle-fill"></i> This is a required question
      </div>
    </div>

    <!-- Year -->
    <div class="gf-card" id="card_year">
      <span class="gf-label">YEAR <span class="gf-required">*</span></span>
      <?php foreach (['first_year' => 'First Year', 'second_year' => 'Second Year', 'third_year' => 'Third Year', 'fourth_year' => 'Fourth Year', 'graduated' => 'Graduated'] as $val => $label):
        $checked = (isset($_POST['year']) && $_POST['year'] === $val) ? 'checked' : '';
      ?>
      <label class="gf-radio-row" for="year_<?= $val ?>">
        <input class="gf-radio-native" type="radio" name="year"
               id="year_<?= $val ?>" value="<?= $val ?>" required <?= $checked ?>>
        <span class="gf-radio-circle"></span>
        <span class="gf-radio-text"><?= e($label) ?></span>
      </label>
      <?php endforeach; ?>
      <div class="gf-error-msg" id="err_year" style="display:none;">
        <i class="bi bi-exclamation-circle-fill"></i> This is a required question
      </div>
    </div>

    <!-- Total Program -->
    <div class="gf-card" id="card_program">
      <span class="gf-label">TOTAL PROGRAM SELECTED <span class="gf-required">*</span></span>
      <?php foreach (['training' => 'Training', 'internship' => 'Internship'] as $val => $label):
        $checked = (isset($_POST['total_program']) && $_POST['total_program'] === $val) ? 'checked' : '';
      ?>
      <label class="gf-radio-row" for="prog_<?= $val ?>">
        <input class="gf-radio-native" type="radio" name="total_program"
               id="prog_<?= $val ?>" value="<?= $val ?>" required <?= $checked ?>>
        <span class="gf-radio-circle"></span>
        <span class="gf-radio-text"><?= e($label) ?></span>
      </label>
      <?php endforeach; ?>
      <div class="gf-error-msg" id="err_program" style="display:none;">
        <i class="bi bi-exclamation-circle-fill"></i> This is a required question
      </div>
    </div>

    <!-- Duration -->
    <div class="gf-card">
      <label class="gf-label" for="internship_duration">DURATION OF INTERNSHIP</label>
      <select class="gf-select" id="internship_duration" name="internship_duration">
        <option value="">Choose</option>
        <?php foreach ($durations as $d): ?>
        <option value="<?= e($d) ?>" <?= (isset($_POST['internship_duration']) && $_POST['internship_duration'] === $d) ? 'selected' : '' ?>>
          <?= e($d) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Total Price -->
    <div class="gf-card">
      <label class="gf-label" for="total_price">TOTAL PRICE (₹)</label>
      <input type="number" class="gf-input" id="total_price" name="total_price"
             placeholder="e.g. 5000" min="0" step="0.01"
             value="<?= isset($_POST['total_price']) ? e($_POST['total_price']) : '' ?>">
    </div>

    <!-- Executive Name -->
    <div class="gf-card">
      <label class="gf-label" for="executive_name">EXECUTIVE NAME</label>
      <input type="text" class="gf-input" id="executive_name" name="executive_name"
             placeholder="Your answer"
             value="<?= isset($_POST['executive_name']) ? e($_POST['executive_name']) : '' ?>">
    </div>

    <!-- Remarks -->
    <div class="gf-card">
      <label class="gf-label" for="remarks">REMARKS</label>
      <textarea class="gf-textarea" id="remarks" name="remarks"
                placeholder="Your answer"><?= isset($_POST['remarks']) ? e($_POST['remarks']) : '' ?></textarea>
    </div>

    <!-- Start Date -->
    <div class="gf-card">
      <label class="gf-label" for="start_date">START DATE</label>
      <input type="date" class="gf-input" id="start_date" name="start_date"
             value="<?= isset($_POST['start_date']) ? e($_POST['start_date']) : '' ?>">
    </div>

    <!-- End Date -->
    <div class="gf-card">
      <label class="gf-label" for="end_date">END DATE</label>
      <input type="date" class="gf-input" id="end_date" name="end_date"
             value="<?= isset($_POST['end_date']) ? e($_POST['end_date']) : '' ?>">
    </div>

    <!-- Days -->
    <div class="gf-card">
      <label class="gf-label" for="days">DAYS</label>
      <input type="number" class="gf-input" id="days" name="days" min="1"
             placeholder="Auto-calculated from dates above"
             value="<?= isset($_POST['days']) ? e($_POST['days']) : '' ?>">
      <div style="font-size:12px;color:#9e9e9e;margin-top:6px;">Filled automatically when both dates are selected</div>
    </div>

    <!-- Certificate Date -->
    <div class="gf-card">
      <label class="gf-label" for="certificate_date">DATE</label>
      <input type="date" class="gf-input" id="certificate_date" name="certificate_date"
             value="<?= isset($_POST['certificate_date']) ? e($_POST['certificate_date']) : '' ?>">
      <div style="font-size:12px;color:#9e9e9e;margin-top:6px;">Date that will appear on the certificate</div>
    </div>

    <!-- Submit -->
    <div class="gf-submit-row">
      <button type="submit" class="gf-btn-submit" id="submitBtn">Submit</button>
      <button type="reset" class="gf-btn-reset">Clear form</button>
    </div>

  </form>

  <footer style="text-align:center;color:#9e9e9e;font-size:12px;margin-top:32px;padding-bottom:16px;">
    &copy; <?= date('Y') ?> <?= e(ORG_NAME) ?> &nbsp;|&nbsp; Certificate Management System
  </footer>
</div>

<script>
(function () {
  const form = document.getElementById('enrollmentForm');
  if (!form) return;

  function showError(cardId, errId, show) {
    const card = document.getElementById(cardId);
    const err  = document.getElementById(errId);
    if (card) card.classList.toggle('gf-error', show);
    if (err)  err.style.display = show ? 'flex' : 'none';
  }

  form.addEventListener('submit', function (e) {
    let valid = true;

    // Name
    const name = document.getElementById('name');
    const nameOk = name && name.value.trim() !== '';
    showError('card_name', 'err_name', !nameOk);
    if (!nameOk) { valid = false; }

    // Contact
    const contact = document.getElementById('contact');
    const contactOk = contact && /^[0-9]{10}$/.test(contact.value.trim());
    showError('card_contact', 'err_contact', !contactOk);
    if (!contactOk) { valid = false; }

    // Email
    const email = document.getElementById('email');
    const emailOk = email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim());
    showError('card_email', 'err_email', !emailOk);
    if (!emailOk) { valid = false; }

    // WhatsApp
    const wa = document.getElementById('whatsapp');
    const waOk = wa && /^[0-9]{10}$/.test(wa.value.trim());
    showError('card_whatsapp', 'err_whatsapp', !waOk);
    if (!waOk) { valid = false; }

    // College
    const college = document.getElementById('college_name');
    const collegeOk = college && college.value.trim() !== '';
    showError('card_college', 'err_college', !collegeOk);
    if (!collegeOk) { valid = false; }

    // Courses
    const courseOk = !!document.querySelector('.course-radio:checked');
    showError('card_courses', 'err_courses', !courseOk);
    if (!courseOk) { valid = false; }

    // Batch
    const batchOk = !!document.querySelector('input[name="batch"]:checked');
    showError('card_batch', 'err_batch', !batchOk);
    if (!batchOk) { valid = false; }

    // Year
    const yearOk = !!document.querySelector('input[name="year"]:checked');
    showError('card_year', 'err_year', !yearOk);
    if (!yearOk) { valid = false; }

    // Program
    const programOk = !!document.querySelector('input[name="total_program"]:checked');
    showError('card_program', 'err_program', !programOk);
    if (!programOk) { valid = false; }

    if (!valid) {
      e.preventDefault();
      // Scroll to first error card
      const firstError = document.querySelector('.gf-card.gf-error');
      if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    // Disable button on submit
    const btn = document.getElementById('submitBtn');
    if (btn) { btn.disabled = true; btn.textContent = 'Submitting…'; }
  });

  // Numbers-only enforcement for tel and price fields
  ['contact','whatsapp'].forEach(function(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('keydown', function(ev) {
      const allowed = ['Backspace','Delete','ArrowLeft','ArrowRight','ArrowUp','ArrowDown','Tab','Home','End'];
      if (allowed.indexOf(ev.key) === -1 && !/^[0-9]$/.test(ev.key)) {
        ev.preventDefault();
      }
    });
    el.addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '');
    });
  });

  const priceEl = document.getElementById('total_price');
  if (priceEl) {
    priceEl.addEventListener('keydown', function(ev) {
      const allowed = ['Backspace','Delete','ArrowLeft','ArrowRight','ArrowUp','ArrowDown','Tab','Home','End','.'];
      if (allowed.indexOf(ev.key) === -1 && !/^[0-9]$/.test(ev.key)) {
        ev.preventDefault();
      }
    });
    priceEl.addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9.]/g, '');
    });
  }

  // Auto-calculate days from start/end date
  (function() {
    const s = document.getElementById('start_date');
    const en = document.getElementById('end_date');
    const d = document.getElementById('days');
    function calcDays() {
      if (!s.value || !en.value) return;
      const diff = (new Date(en.value) - new Date(s.value)) / 86400000;
      if (diff >= 0) d.value = Math.round(diff) + 1;
    }
    if (s && en && d) {
      s.addEventListener('change', calcDays);
      en.addEventListener('change', calcDays);
    }
  })();

  // Clear error on input
  ['name','contact','email','whatsapp','college_name'].forEach(function(id) {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', function() {
      const card = document.getElementById('card_' + (id === 'college_name' ? 'college' : id));
      const err  = document.getElementById('err_'  + (id === 'college_name' ? 'college' : id));
      if (card) card.classList.remove('gf-error');
      if (err)  err.style.display = 'none';
    });
  });

  document.querySelectorAll('.course-radio').forEach(function(r) {
    r.addEventListener('change', function() { showError('card_courses','err_courses',false); });
  });
  document.querySelectorAll('input[name="batch"]').forEach(function(r) {
    r.addEventListener('change', function() { showError('card_batch','err_batch',false); });
  });
  document.querySelectorAll('input[name="year"]').forEach(function(r) {
    r.addEventListener('change', function() { showError('card_year','err_year',false); });
  });
  document.querySelectorAll('input[name="total_program"]').forEach(function(r) {
    r.addEventListener('change', function() { showError('card_program','err_program',false); });
  });
})();
</script>
</body>
</html>
