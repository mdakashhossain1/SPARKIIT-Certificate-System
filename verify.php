<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/db.php';

$result      = null;
$searched    = false;
$error       = '';
$certLayouts = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searched = true;
    $query    = trim($_POST['query'] ?? '');

    if ($query === '') {
        $error = 'Please enter your registered email address.';
    } else {
        $db  = getDB();
        $sql = "SELECT id, name, email, contact, college_name,
                       courses_selected, batch, year, total_program, internship_duration,
                       submitted_at, show_certificate, remarks,
                       start_date, end_date, days, certificate_date
                FROM form_submissions
                WHERE LOWER(email) = LOWER(:query)
                ORDER BY submitted_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':query' => $query]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($result)) {
            $layoutRows = $db->query("SELECT type, layout_json FROM certificate_layouts")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($layoutRows as $lr) {
                $certLayouts[$lr['type']] = json_decode($lr['layout_json'], true);
            }
        }

        if (empty($result)) {
            $error = 'No enrollment found for that email address.';
        }
    }
}

$cert = (!empty($result)) ? $result[0] : null; // show first record
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verify Certificate – <?= e(ORG_NAME) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
/* ── Custom Fonts ── */
@font-face { font-family:'Kelvinch'; src:url('<?= BASE_URL ?>/assets/fonts/Kelvinch-Bold.otf') format('opentype'); font-weight:bold; font-style:normal; }
@font-face { font-family:'Montserrat'; src:url('<?= BASE_URL ?>/assets/fonts/Montserrat-Regular.ttf') format('truetype'); font-weight:normal; font-style:normal; }
@font-face { font-family:'Montserrat'; src:url('<?= BASE_URL ?>/assets/fonts/Montserrat-Bold.ttf') format('truetype'); font-weight:bold; font-style:normal; }
@font-face { font-family:'Raleway'; src:url('<?= BASE_URL ?>/assets/fonts/Raleway-Regular.ttf') format('truetype'); font-weight:normal; font-style:normal; }
@font-face { font-family:'Pinyon Script'; src:url('<?= BASE_URL ?>/assets/fonts/PinyonScript-Regular.ttf') format('truetype'); font-weight:normal; font-style:normal; }

/* ── Reset ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ── Base ── */
body {
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
  font-size: 14px;
  color: #1e1e2e;
  background: #f4f4f8;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* ── Top Bar ── */
.topbar {
  background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);
  padding: 14px 32px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 2px 12px rgba(91,33,182,.25);
  flex-shrink: 0;
}
.topbar-icon { font-size: 22px; color: #fff; }
.topbar h1 { font-size: 18px; font-weight: 600; color: #fff; letter-spacing: -.2px; }
.topbar p  { font-size: 12px; color: rgba(255,255,255,.75); margin-top: 1px; }

/* ── Main Layout ── */
.layout {
  display: flex;
  flex: 1;
  overflow: hidden;
  height: calc(100vh - 61px);
}

/* ── Left Panel ── */
.left-panel {
  width: 360px;
  min-width: 320px;
  flex-shrink: 0;
  background: #fff;
  border-right: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

.search-box {
  padding: 24px 20px 20px;
  border-bottom: 1px solid #f0f0f4;
  background: #faf9ff;
}
.search-box h2 {
  font-size: 13px;
  font-weight: 600;
  color: #7c3aed;
  text-transform: uppercase;
  letter-spacing: .5px;
  margin-bottom: 14px;
}
.search-input-wrap {
  display: flex;
  align-items: center;
  border: 1.5px solid #ddd6fe;
  border-radius: 8px;
  overflow: hidden;
  background: #fff;
  transition: border-color .2s;
}
.search-input-wrap:focus-within { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,.1); }
.search-input-wrap input {
  flex: 1;
  border: none;
  outline: none;
  padding: 10px 14px;
  font-size: 13px;
  background: transparent;
  color: #1e1e2e;
}
.search-input-wrap input::placeholder { color: #9ca3af; }
.search-input-wrap button {
  background: #7c3aed;
  border: none;
  color: #fff;
  padding: 10px 16px;
  cursor: pointer;
  font-size: 15px;
  transition: background .15s;
}
.search-input-wrap button:hover { background: #6d28d9; }

.error-msg {
  margin-top: 12px;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 8px;
  padding: 10px 14px;
  color: #dc2626;
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* ── Info Panel (inside left) ── */
.info-panel { padding: 20px; flex: 1; }

.enrolled-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #f0fdf4;
  color: #16a34a;
  border: 1px solid #bbf7d0;
  border-radius: 20px;
  padding: 5px 14px;
  font-size: 12px;
  font-weight: 600;
  margin-bottom: 18px;
}

.student-name {
  font-size: 20px;
  font-weight: 700;
  color: #1e1e2e;
  margin-bottom: 4px;
  line-height: 1.2;
}
.student-sub {
  font-size: 12px;
  color: #6b7280;
  margin-bottom: 20px;
}

.info-section-title {
  font-size: 11px;
  font-weight: 700;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: .7px;
  margin: 20px 0 10px;
}

.info-row {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 9px 0;
  border-bottom: 1px solid #f3f4f6;
}
.info-row:last-child { border-bottom: none; }
.info-icon {
  width: 28px; height: 28px;
  background: #ede9fe;
  border-radius: 7px;
  display: flex; align-items: center; justify-content: center;
  color: #7c3aed;
  font-size: 13px;
  flex-shrink: 0;
  margin-top: 1px;
}
.info-label { font-size: 11px; color: #9ca3af; margin-bottom: 1px; }
.info-value { font-size: 13px; color: #1e1e2e; font-weight: 500; line-height: 1.3; }

.remarks-box {
  margin-top: 16px;
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: 8px;
  padding: 12px 14px;
  font-size: 13px;
  color: #92400e;
  display: flex;
  gap: 8px;
  align-items: flex-start;
}
.remarks-box i { margin-top: 1px; flex-shrink: 0; }

/* ── Empty state ── */
.empty-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  text-align: center;
  color: #9ca3af;
}
.empty-state i { font-size: 48px; color: #ddd6fe; margin-bottom: 16px; }
.empty-state h3 { font-size: 16px; font-weight: 600; color: #6b7280; margin-bottom: 6px; }
.empty-state p { font-size: 13px; }

/* ── Right Panel ── */
.right-panel {
  flex: 1;
  overflow-y: auto;
  padding: 28px 32px 48px;
  background: #f4f4f8;
}

/* ── Cert tabs navigation ── */
.cert-tabs {
  display: flex;
  gap: 8px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}
.cert-tab-btn {
  padding: 8px 18px;
  border-radius: 20px;
  border: 1.5px solid #ddd6fe;
  background: #fff;
  color: #7c3aed;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all .15s;
}
.cert-tab-btn.active,
.cert-tab-btn:hover {
  background: #7c3aed;
  color: #fff;
  border-color: #7c3aed;
}

/* ── Cert block ── */
.cert-block { display: none; }
.cert-block.active { display: block; }

.cert-block-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
  flex-wrap: wrap;
  gap: 10px;
}
.cert-block-title {
  display: flex;
  align-items: center;
  gap: 10px;
}
.cert-block-title-icon {
  width: 36px; height: 36px;
  background: #ede9fe;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  color: #7c3aed;
  font-size: 18px;
}
.cert-block-title h3 {
  font-size: 16px;
  font-weight: 600;
  color: #1e1e2e;
}
.cert-block-title span {
  font-size: 12px;
  color: #6b7280;
}

.dl-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #7c3aed;
  color: #fff;
  text-decoration: none;
  border-radius: 8px;
  padding: 9px 18px;
  font-size: 13px;
  font-weight: 500;
  transition: background .15s, transform .1s;
  box-shadow: 0 2px 8px rgba(124,58,237,.3);
}
.dl-btn:hover { background: #6d28d9; color: #fff; transform: translateY(-1px); }

/* ── Certificate preview ── */
.cert-render-wrapper {
  position: relative;
  width: 100%;
  padding-bottom: 64.516129%; /* 1800/2790 — establishes correct aspect-ratio height */
  overflow: hidden;
  border-radius: 12px;
  box-shadow: 0 4px 24px rgba(0,0,0,.15);
  background: #e5e7eb;
}
/* Background image fills the wrapper exactly */
.cert-render-wrapper > .cert-bg-img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: fill;
  display: block;
}
/* Text elements: left/top/width are CSS % (same values stored in DB) — no transform needed.
   Font-size is set by JS: storedPx × (wrapperWidth / 2790). */
.cert-text-el {
  position: absolute;
  line-height: 1.4;
  word-break: break-word;
  overflow-wrap: break-word;
  white-space: pre-wrap;
  pointer-events: none;
  box-sizing: border-box;
  padding: 0; margin: 0;
}

/* ── No certificates state ── */
.no-cert-msg {
  background: #fff;
  border: 1.5px dashed #ddd6fe;
  border-radius: 12px;
  padding: 48px 24px;
  text-align: center;
  color: #9ca3af;
}
.no-cert-msg i { font-size: 40px; color: #ddd6fe; margin-bottom: 12px; display: block; }
.no-cert-msg p { font-size: 14px; }

/* ── Right empty state ── */
.right-empty {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  color: #9ca3af;
  padding: 40px;
}
.right-empty-icon {
  width: 80px; height: 80px;
  background: #ede9fe;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 36px;
  color: #7c3aed;
  margin: 0 auto 20px;
}
.right-empty h2 { font-size: 18px; font-weight: 600; color: #4b5563; margin-bottom: 8px; }
.right-empty p  { font-size: 14px; max-width: 320px; line-height: 1.6; }
</style>
</head>
<body>

<!-- Top Bar -->
<div class="topbar">
  <i class="bi bi-patch-check-fill topbar-icon"></i>
  <div>
    <h1><?= e(ORG_NAME) ?> – Certificate Verification</h1>
    <p>Verify your enrollment and download your certificates</p>
  </div>
</div>

<!-- Main Layout -->
<div class="layout">

  <!-- ── LEFT PANEL ── -->
  <div class="left-panel">

    <!-- Search -->
    <div class="search-box">
      <h2><i class="bi bi-search me-1"></i> Find Your Record</h2>
      <form method="POST" action="">
        <div class="search-input-wrap">
          <input type="text" name="query" placeholder="Enter registered email…"
                 value="<?= e($_POST['query'] ?? '') ?>" autocomplete="off" required>
          <button type="submit"><i class="bi bi-arrow-right"></i></button>
        </div>
      </form>
      <?php if ($searched && $error): ?>
      <div class="error-msg">
        <i class="bi bi-x-circle-fill"></i>
        <?= e($error) ?>
      </div>
      <?php endif; ?>
    </div>

    <?php if ($cert): ?>
    <!-- Info Panel -->
    <div class="info-panel">
      <div class="enrolled-badge">
        <i class="bi bi-check-circle-fill"></i> Enrollment Verified
      </div>
      <div class="student-name"><?= e($cert['name']) ?></div>
      <div class="student-sub"><?= e($cert['college_name']) ?></div>

      <div class="info-section-title">Contact Details</div>

      <div class="info-row">
        <div class="info-icon"><i class="bi bi-envelope-fill"></i></div>
        <div>
          <div class="info-label">Email</div>
          <div class="info-value"><?= e($cert['email']) ?></div>
        </div>
      </div>
      <div class="info-row">
        <div class="info-icon"><i class="bi bi-phone-fill"></i></div>
        <div>
          <div class="info-label">Contact</div>
          <div class="info-value"><?= e($cert['contact']) ?></div>
        </div>
      </div>

      <div class="info-section-title">Program Details</div>

      <div class="info-row">
        <div class="info-icon"><i class="bi bi-book-fill"></i></div>
        <div>
          <div class="info-label">Courses</div>
          <div class="info-value"><?php
            $parsed = json_decode($cert['courses_selected'] ?? '[]', true);
            echo e(is_array($parsed) ? implode(', ', $parsed) : $cert['courses_selected']);
          ?></div>
        </div>
      </div>
      <?php if (!empty($cert['batch'])): ?>
      <div class="info-row">
        <div class="info-icon"><i class="bi bi-calendar3"></i></div>
        <div>
          <div class="info-label">Batch</div>
          <div class="info-value"><?= e($cert['batch']) ?></div>
        </div>
      </div>
      <?php endif; ?>
      <?php if (!empty($cert['year'])): ?>
      <div class="info-row">
        <div class="info-icon"><i class="bi bi-mortarboard-fill"></i></div>
        <div>
          <div class="info-label">Year</div>
          <div class="info-value"><?= e(str_replace('_', ' ', ucwords($cert['year']))) ?></div>
        </div>
      </div>
      <?php endif; ?>
      <?php if (!empty($cert['total_program'])): ?>
      <div class="info-row">
        <div class="info-icon"><i class="bi bi-award-fill"></i></div>
        <div>
          <div class="info-label">Program</div>
          <div class="info-value"><?= e(ucfirst($cert['total_program'])) ?></div>
        </div>
      </div>
      <?php endif; ?>
      <?php if (!empty($cert['internship_duration'])): ?>
      <div class="info-row">
        <div class="info-icon"><i class="bi bi-clock-fill"></i></div>
        <div>
          <div class="info-label">Duration</div>
          <div class="info-value"><?= e($cert['internship_duration']) ?></div>
        </div>
      </div>
      <?php endif; ?>
      <div class="info-row">
        <div class="info-icon"><i class="bi bi-calendar-check-fill"></i></div>
        <div>
          <div class="info-label">Enrolled On</div>
          <div class="info-value"><?= date('d F Y', strtotime($cert['submitted_at'])) ?></div>
        </div>
      </div>

      <?php if (!empty($cert['remarks'])): ?>
      <div class="remarks-box">
        <i class="bi bi-info-circle-fill"></i>
        <span><?= e($cert['remarks']) ?></span>
      </div>
      <?php endif; ?>
    </div><!-- /info-panel -->

    <?php else: ?>
    <div class="empty-state">
      <i class="bi bi-person-circle"></i>
      <h3>No Record Yet</h3>
      <p>Enter your registered email address to find your enrollment and certificates.</p>
    </div>
    <?php endif; ?>

  </div><!-- /left-panel -->

  <!-- ── RIGHT PANEL ── -->
  <div class="right-panel">

    <?php if ($cert && !empty($cert['show_certificate'])): ?>

      <?php
      $courses_parsed = json_decode($cert['courses_selected'] ?? '[]', true);
      $program_name   = is_array($courses_parsed) ? implode(', ', $courses_parsed) : '';
      $fmt = fn($d) => $d ? date('jS F Y', strtotime($d)) : '';
      $vars = [
          '{name}'         => $cert['name'],
          '{program_name}' => $program_name,
          '{days}'         => $cert['days'] ?? '',
          '{start_date}'   => $fmt($cert['start_date']),
          '{end_date}'     => $fmt($cert['end_date']),
          '{date}'         => $fmt($cert['certificate_date']),
      ];
      $allCertTypes = [
          'training'      => ['label' => 'Training',      'icon' => 'bi-mortarboard-fill',      'bg' => BASE_URL . '/uploads/traning.png'],
          'participation' => ['label' => 'Participation', 'icon' => 'bi-person-check-fill',      'bg' => BASE_URL . '/uploads/particepation.jpg'],
          'internship'    => ['label' => 'Internship',    'icon' => 'bi-briefcase-fill',         'bg' => BASE_URL . '/uploads/intenship.png'],
      ];

      // Collect available types
      $availTypes = [];
      foreach ($allCertTypes as $cType => $cInfo) {
          if (!empty($certLayouts[$cType])) $availTypes[] = $cType;
      }
      ?>

      <?php if (!empty($availTypes)): ?>

      <!-- Tab buttons -->
      <div class="cert-tabs">
        <?php foreach ($availTypes as $i => $cType): ?>
        <button class="cert-tab-btn <?= $i === 0 ? 'active' : '' ?>"
                onclick="switchCert('<?= $cType ?>')">
          <i class="bi <?= e($allCertTypes[$cType]['icon']) ?>"></i>
          <?= e($allCertTypes[$cType]['label']) ?> Certificate
        </button>
        <?php endforeach; ?>
      </div>

      <!-- Certificate blocks -->
      <?php foreach ($availTypes as $i => $cType):
          $cInfo  = $allCertTypes[$cType];
          $layout = $certLayouts[$cType];
          $elId   = 'certInner_' . $cert['id'] . '_' . $cType;
      ?>
      <div class="cert-block <?= $i === 0 ? 'active' : '' ?>" id="block_<?= $cType ?>">

        <div class="cert-block-header">
          <div class="cert-block-title">
            <div class="cert-block-title-icon">
              <i class="bi <?= e($cInfo['icon']) ?>"></i>
            </div>
            <div>
              <h3><?= e($cInfo['label']) ?> Certificate</h3>
              <span><?= e($cert['name']) ?></span>
            </div>
          </div>
          <a class="dl-btn" href="download-certificate.php?id=<?= $cert['id'] ?>&type=<?= urlencode($cType) ?>">
            <i class="bi bi-download"></i> Download PDF
          </a>
        </div>

        <div class="cert-render-wrapper">
          <img class="cert-bg-img" src="<?= e($cInfo['bg']) ?>" alt="<?= e($cInfo['label']) ?> Certificate">
          <?php foreach ($layout as $field => $cfg):
            // cfg['text'] may contain HTML for inline rich-text — do NOT escape
            $text      = strtr($cfg['text'] ?? '', $vars);
            // Positions are ALREADY percentages — use directly as CSS %.
            // left:X% = X% of wrapper width  = X% of cert width  ✓
            // top:Y%  = Y% of wrapper height = Y% of cert height ✓ (wrapper height set by padding-bottom trick)
            // width:W% = W% of wrapper width ✓
            // font-size is in px at 2790px scale → set via JS: storedPx × (wrapperWidth/2790)
            $leftPct   = $cfg['left'];
            $topPct    = $cfg['top'];
            $widPct    = $cfg['width'];
            $fontSize  = (int)($cfg['fontSize'] ?? 14);
          ?>
          <div class="cert-text-el"
               data-base-font-size="<?= $fontSize ?>"
               style="left:<?= e($leftPct) ?>%;top:<?= e($topPct) ?>%;width:<?= e($widPct) ?>%;font-size:<?= $fontSize ?>px;font-weight:<?= e($cfg['fontWeight'] ?? 'normal') ?>;font-style:<?= e($cfg['fontStyle'] ?? 'normal') ?>;font-family:'<?= e($cfg['fontFamily'] ?? 'Arial') ?>',sans-serif;color:<?= e($cfg['color'] ?? '#000') ?>;text-align:<?= e($cfg['textAlign'] ?? 'left') ?>;"><?= $text ?></div>
          <?php endforeach; ?>
        </div>

      </div>
      <?php endforeach; ?>

      <?php else: ?>
      <div class="no-cert-msg">
        <i class="bi bi-hourglass-split"></i>
        <p>Certificate layouts are not configured yet. Please contact the administrator.</p>
      </div>
      <?php endif; ?>

    <?php elseif ($cert && empty($cert['show_certificate'])): ?>
      <div class="right-empty">
        <div class="right-empty-icon"><i class="bi bi-hourglass-split"></i></div>
        <h2>Certificate Pending</h2>
        <p>Your enrollment is verified but your certificate is not yet ready. Please check back later.</p>
      </div>

    <?php else: ?>
      <div class="right-empty">
        <div class="right-empty-icon"><i class="bi bi-file-earmark-check"></i></div>
        <h2>Your Certificates Appear Here</h2>
        <p>Enter your registered email address on the left to verify your enrollment and access your certificates.</p>
      </div>
    <?php endif; ?>

  </div><!-- /right-panel -->

</div><!-- /layout -->

<script>
function switchCert(type) {
    document.querySelectorAll('.cert-block').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.cert-tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('block_' + type).classList.add('active');
    event.currentTarget.classList.add('active');
    scaleFonts();
}

/**
 * Scale font sizes so they appear proportional to the 2790px design space.
 *
 * Why: positions (left/top/width) are stored as percentages and applied
 * directly as CSS %, which is always accurate. Only font-size needs JS help
 * because CSS has no "% of container width" unit for font sizes.
 *
 * Formula: visibleFontSize = storedPx × (wrapperWidth / 2790)
 */
function scaleFonts() {
    document.querySelectorAll('.cert-render-wrapper').forEach(function(wrapper) {
        var ww = wrapper.offsetWidth;
        if (!ww) return;
        var scale = ww / 2790;
        wrapper.querySelectorAll('.cert-text-el').forEach(function(el) {
            el.style.fontSize = (parseFloat(el.dataset.baseFontSize) * scale) + 'px';
        });
    });
}

// ResizeObserver gives instant, accurate recalculation whenever the wrapper
// changes size (initial layout, window resize, sidebar changes, etc.)
if (window.ResizeObserver) {
    var ro = new ResizeObserver(scaleFonts);
    document.querySelectorAll('.cert-render-wrapper').forEach(function(w) {
        ro.observe(w);
    });
}
// Fallback for older browsers
window.addEventListener('resize', scaleFonts);
// Run immediately and after fonts/images settle
scaleFonts();
document.fonts && document.fonts.ready.then(scaleFonts);
</script>
</body>
</html>
