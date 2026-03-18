<?php
require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

requireAdmin();

$db = getDB();

// Ensure table exists
$db->exec("CREATE TABLE IF NOT EXISTS certificate_layouts (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('training','participation','internship') NOT NULL,
    layout_json JSON NOT NULL,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_type (type)
)");

// Load saved layouts
$existingLayouts = [];
foreach ($db->query("SELECT type, layout_json FROM certificate_layouts")->fetchAll() as $row) {
    $existingLayouts[$row['type']] = json_decode($row['layout_json'], true);
}

$defaults = [
    'name' => [
        'label' => 'Name', 'text' => '{name}',
        'left' => 25.0, 'top' => 43.0, 'width' => 50.0,
        'fontSize' => 32, 'fontWeight' => 'bold', 'fontStyle' => 'normal',
        'fontFamily' => 'Georgia', 'color' => '#1a237e', 'textAlign' => 'center',
    ],
    'description' => [
        'label' => 'Description',
        'text'  => 'This is to certify that {name} has successfully completed {program_name} for {days} days from {start_date} to {end_date}.',
        'left' => 15.0, 'top' => 55.0, 'width' => 70.0,
        'fontSize' => 14, 'fontWeight' => 'normal', 'fontStyle' => 'normal',
        'fontFamily' => 'Montserrat', 'color' => '#333333', 'textAlign' => 'center',
    ],
    'date' => [
        'label' => 'Date', 'text' => '{date}',
        'left' => 35.0, 'top' => 73.0, 'width' => 30.0,
        'fontSize' => 13, 'fontWeight' => 'normal', 'fontStyle' => 'normal',
        'fontFamily' => 'Raleway', 'color' => '#555555', 'textAlign' => 'center',
    ],
];

$typeImages = [
    'training'      => BASE_URL . '/uploads/traning.png',
    'participation' => BASE_URL . '/uploads/particepation.jpg',
    'internship'    => BASE_URL . '/uploads/intenship.png',
];

// Per-type overrides: only the keys that differ from $defaults
$typeOverrides = [
    'training'      => ['name' => ['fontFamily' => 'Kelvinch', 'fontWeight' => 'bold']],
    'participation' => ['name' => ['fontFamily' => 'Pinyon Script', 'fontSize' => 36, 'fontWeight' => 'normal']],
    'internship'    => ['name' => ['fontFamily' => 'Kelvinch', 'fontWeight' => 'bold']],
];

// Which fields are active per certificate type
$typeFields = [
    'training'      => ['name', 'description', 'date'],
    'participation' => ['name', 'description'],
    'internship'    => ['name', 'description', 'date'],
];

// Variables available per field
$variables = [
    'description' => [
        '{name}'         => "Recipient's name",
        '{program_name}' => 'Program name',
        '{days}'         => 'Duration (days)',
        '{start_date}'   => 'Start date',
        '{end_date}'     => 'End date',
    ],
    'date' => [
        '{date}'         => 'Certificate issue date',
        '{start_date}'   => 'Start date',
        '{end_date}'     => 'End date',
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Certificate Builder — <?= e(APP_NAME) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    @font-face {
      font-family: 'Kelvinch';
      src: url('<?= BASE_URL ?>/assets/fonts/Kelvinch-Bold.otf') format('opentype');
      font-weight: bold; font-style: normal;
    }
    @font-face {
      font-family: 'Montserrat';
      src: url('<?= BASE_URL ?>/assets/fonts/Montserrat-Regular.ttf') format('truetype');
      font-weight: normal; font-style: normal;
    }
    @font-face {
      font-family: 'Montserrat';
      src: url('<?= BASE_URL ?>/assets/fonts/Montserrat-Bold.ttf') format('truetype');
      font-weight: bold; font-style: normal;
    }
    @font-face {
      font-family: 'Raleway';
      src: url('<?= BASE_URL ?>/assets/fonts/Raleway-Regular.ttf') format('truetype');
      font-weight: normal; font-style: normal;
    }
    @font-face {
      font-family: 'Pinyon Script';
      src: url('<?= BASE_URL ?>/assets/fonts/PinyonScript-Regular.ttf') format('truetype');
      font-weight: normal; font-style: normal;
    }
    body { font-family: 'Segoe UI', sans-serif; }

    #certCanvas {
      position: absolute;
      top: 0; left: 0;
      width: 2790px;
      height: 1800px;
      box-shadow: 0 0 0 2px #adb5bd;
      overflow: hidden;
      user-select: none;
      cursor: default;
      background: #f8f9fa;
      box-sizing: content-box;
      transform-origin: top left;
    }

    #certBg {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: fill;
      pointer-events: none;
    }

    .cert-element {
      position: absolute;
      cursor: move;
      /* outline instead of border + no padding so offsetLeft/offsetTop = exact text position */
      outline: 2px dashed transparent;
      outline-offset: 2px;
      padding: 0;
      margin: 0;
      box-sizing: content-box;
      min-width: 60px;
    }

    .cert-element:hover  { outline-color: rgba(13,110,253,.45); }
    .cert-element.active { outline-color: #0d6efd; background: rgba(13,110,253,.06); }

    .cert-element .el-text {
      pointer-events: none;
      word-wrap: break-word;
      overflow-wrap: break-word;
      white-space: pre-wrap;
      display: block;
      line-height: 1.4;
    }

    .cert-element .rh {
      position: absolute;
      bottom: -8px;
      right: -8px;
      width: 13px;
      height: 13px;
      background: #0d6efd;
      border: 2px solid #fff;
      border-radius: 3px;
      cursor: ew-resize;
      opacity: 0;
      transition: opacity .15s;
      z-index: 5;
    }

    .cert-element:hover .rh,
    .cert-element.active .rh { opacity: 1; }

    .var-chip {
      cursor: pointer;
      transition: background .12s;
    }
    .var-chip:hover { background: #0d6efd !important; color: #fff !important; }

    /* ── Rich-text editor ── */
    #rtToolbar { gap: 4px; }
    #propText[contenteditable] {
      min-height: 80px;
      max-height: 200px;
      overflow-y: auto;
      white-space: pre-wrap;
      word-break: break-word;
      line-height: 1.5;
      outline: none;
      cursor: text;
    }
    #propText[contenteditable]:focus {
      border-color: #86b7fe;
      box-shadow: 0 0 0 .2rem rgba(13,110,253,.25);
    }
    #rtColorSwatch {
      display: inline-block;
      width: 14px; height: 4px;
      border-radius: 2px;
      background: #e53e3e;
      margin-top: 2px;
    }
  </style>
</head>
<body class="bg-light">
<div class="d-flex">
  <?php require __DIR__ . '/partials/sidebar.php'; ?>

  <main class="flex-grow-1 p-4" style="overflow-x:auto; min-width:0;">

    <!-- Page header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h4 class="mb-0"><i class="bi bi-layout-text-window-reverse me-2 text-primary"></i>Certificate Layout Builder</h4>
        <small class="text-muted">Drag elements to reposition · Use corner handle to resize width</small>
      </div>
      <button id="saveBtn" class="btn btn-success px-4">
        <i class="bi bi-floppy me-1"></i>Save Layout
      </button>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" id="certTabs">
      <li class="nav-item">
        <a class="nav-link active" href="#" data-type="training">
          <i class="bi bi-mortarboard me-1"></i>Training
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" data-type="participation">
          <i class="bi bi-person-check me-1"></i>Participant
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" data-type="internship">
          <i class="bi bi-briefcase me-1"></i>Internship
        </a>
      </li>
    </ul>

    <!-- Builder row -->
    <div class="d-flex gap-3 align-items-start">

      <!-- Certificate canvas -->
      <div>
        <div id="canvasScaler" style="position:relative;overflow:hidden;flex-shrink:0;">
        <div id="certCanvas">
          <img id="certBg" src="" alt="Certificate background">

          <?php foreach (['name','description','date'] as $f): ?>
          <div class="cert-element" data-field="<?= $f ?>">
            <span class="el-text"></span>
            <div class="rh"></div>
          </div>
          <?php endforeach; ?>
        </div><!-- /certCanvas -->
        </div><!-- /canvasScaler -->
        <small class="text-muted mt-1 d-block" style="max-width:900px;">
          <i class="bi bi-info-circle me-1"></i>
          Canvas: 2790 × 1800 px &mdash; displayed scaled, positions saved as % for PDF
        </small>
      </div>

      <!-- Properties sidebar -->
      <div style="width:290px;flex-shrink:0;">
        <div class="card shadow-sm">
          <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-sliders text-primary"></i>
            <span id="propTitle">Properties</span>
          </div>
          <div class="card-body p-3">

            <div id="noSel" class="text-center text-muted py-3">
              <i class="bi bi-cursor-fill d-block fs-2 mb-2 opacity-50"></i>
              Click an element to edit its properties
            </div>

            <div id="propPanel" style="display:none;">

              <div class="mb-3">
                <label class="form-label small fw-semibold mb-1">Text Content</label>
                <!-- Rich-text formatting toolbar -->
                <div id="rtToolbar" class="d-flex mb-1 flex-wrap align-items-center" style="gap:4px;">
                  <div class="btn-group btn-group-sm">
                    <button type="button" id="rtBold"   class="btn btn-outline-secondary" title="Bold (Ctrl+B)"><strong>B</strong></button>
                    <button type="button" id="rtItalic" class="btn btn-outline-secondary fst-italic" title="Italic (Ctrl+I)">I</button>
                    <button type="button" id="rtUnder"  class="btn btn-outline-secondary" title="Underline (Ctrl+U)" style="text-decoration:underline;">U</button>
                  </div>
                  <div class="btn-group btn-group-sm">
                    <button type="button" id="rtColorBtn" class="btn btn-outline-secondary" title="Color selected text" style="position:relative;overflow:hidden;padding-bottom:6px;">
                      <i class="bi bi-type"></i><span id="rtColorSwatch"></span>
                      <input type="color" id="rtColorPick" value="#e53e3e"
                             style="position:absolute;opacity:0;top:0;left:0;width:100%;height:100%;cursor:pointer;border:none;padding:0;">
                    </button>
                    <button type="button" id="rtClearFmt" class="btn btn-outline-secondary" title="Remove inline formatting"><i class="bi bi-eraser-fill"></i></button>
                  </div>
                  <small class="text-muted" style="font-size:10px;line-height:1;">Select text first</small>
                </div>
                <!-- Contenteditable replaces textarea -->
                <div id="propText" contenteditable="true" spellcheck="false"
                     class="form-control form-control-sm"></div>
              </div>

              <div class="row g-2 mb-3">
                <div class="col-7">
                  <label class="form-label small fw-semibold mb-1">Font Size</label>
                  <div class="input-group input-group-sm">
                    <input type="number" id="propSize" class="form-control" min="6" max="120">
                    <span class="input-group-text">px</span>
                  </div>
                </div>
                <div class="col-5">
                  <label class="form-label small fw-semibold mb-1">Color</label>
                  <input type="color" id="propColor" class="form-control form-control-sm form-control-color w-100" style="height:31px;">
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-semibold mb-1">Font Family</label>
                <select id="propFont" class="form-select form-select-sm">
                  <?php foreach (['Kelvinch','Montserrat','Raleway','Pinyon Script','Arial','Georgia','Times New Roman','Verdana','Trebuchet MS','Palatino Linotype','Tahoma','Courier New'] as $f): ?>
                  <option value="<?= e($f) ?>"><?= e($f) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label class="form-label small fw-semibold mb-1">Style</label>
                  <div class="btn-group btn-group-sm w-100">
                    <button type="button" id="btnBold"   class="btn btn-outline-secondary" title="Bold"><i class="bi bi-type-bold"></i></button>
                    <button type="button" id="btnItalic" class="btn btn-outline-secondary" title="Italic"><i class="bi bi-type-italic"></i></button>
                  </div>
                </div>
                <div class="col-6">
                  <label class="form-label small fw-semibold mb-1">Align</label>
                  <div class="btn-group btn-group-sm w-100" id="alignGrp">
                    <button type="button" class="btn btn-outline-secondary aln-btn" data-align="left"   title="Left"><i class="bi bi-text-left"></i></button>
                    <button type="button" class="btn btn-outline-secondary aln-btn" data-align="center" title="Center"><i class="bi bi-text-center"></i></button>
                    <button type="button" class="btn btn-outline-secondary aln-btn" data-align="right"  title="Right"><i class="bi bi-text-right"></i></button>
                  </div>
                </div>
              </div>

              <div class="mb-1">
                <label class="form-label small fw-semibold mb-1">
                  Width &mdash; <span id="widthLbl">50%</span>
                </label>
                <input type="range" id="propWidth" class="form-range" min="5" max="100" step="1">
              </div>

            </div>
          </div>
        </div>

        <!-- Variable chips (description + date fields) -->
        <div class="card shadow-sm mt-3" id="varsCard" style="display:none;">
          <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-braces text-success"></i>Available Variables
          </div>
          <div class="card-body p-2">
            <small class="text-muted d-block mb-2">Click to insert at cursor</small>
            <?php foreach ($variables as $field => $vars): ?>
            <div class="vars-group" data-group="<?= e($field) ?>">
              <?php foreach ($vars as $var => $desc): ?>
              <span class="var-chip badge bg-light text-dark border me-1 mb-1 px-2 py-1"
                    data-var="<?= e($var) ?>" title="<?= e($desc) ?>"><?= e($var) ?></span>
              <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div><!-- /props -->
    </div><!-- /builder row -->

    <!-- Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
      <div id="toast" class="toast align-items-center border-0" role="alert">
        <div class="d-flex">
          <div class="toast-body" id="toastMsg"></div>
          <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
    </div>

  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ── Constants ─────────────────────────────────────────────────────────── */
const IMAGES      = <?= json_encode($typeImages, JSON_UNESCAPED_SLASHES) ?>;
const SAVED          = <?= json_encode($existingLayouts) ?>;
const DEFS           = <?= json_encode($defaults) ?>;
const TYPE_FIELDS    = <?= json_encode($typeFields) ?>;
const TYPE_OVERRIDES = <?= json_encode($typeOverrides) ?>;
const CW = 2790, CH = 1800;        // canvas pixel dimensions
const DISPLAY_W = 900;              // display width in px
let canvasScale = 1;

function scaleCanvas() {
    const scaler = document.getElementById('canvasScaler');
    scaler.style.width  = DISPLAY_W + 'px';
    canvasScale = DISPLAY_W / CW;
    scaler.style.height = Math.round(CH * canvasScale) + 'px';
    document.getElementById('certCanvas').style.transform = 'scale(' + canvasScale + ')';
}

scaleCanvas();

/* ── State ─────────────────────────────────────────────────────────────── */
let curType    = 'training';
let selEl      = null;             // currently selected DOM element
const layouts  = {};               // cached layout objects per type

/* ── Layout helpers ────────────────────────────────────────────────────── */
function getLayout(type) {
    if (layouts[type]) return layouts[type];
    const saved = SAVED[type] || {};
    const lyt = {};
    ['name','description','date'].forEach(f => {
        const base = Object.assign({}, DEFS[f], (TYPE_OVERRIDES[type] || {})[f] || {});
        const src  = saved[f] ? saved[f] : base;
        lyt[f] = {
            label:      DEFS[f].label,
            text:       src.text,
            left:       +src.left,
            top:        +src.top,
            width:      +src.width,
            fontSize:   +src.fontSize,
            fontWeight: src.fontWeight  || 'normal',
            fontStyle:  src.fontStyle   || 'normal',
            fontFamily: src.fontFamily  || 'Arial',
            color:      src.color       || '#000000',
            textAlign:  src.textAlign   || 'left',
        };
    });
    layouts[type] = lyt;
    return lyt;
}

function syncFromEl(el) {
    const f = el.dataset.field;
    const cfg = layouts[curType][f];
    cfg.left  = (el.offsetLeft / CW) * 100;
    cfg.top   = (el.offsetTop  / CH) * 100;
    cfg.width = (el.offsetWidth / CW) * 100;
}

/* ── Render ─────────────────────────────────────────────────────────────── */
function renderCanvas(type) {
    document.getElementById('certBg').src = IMAGES[type];
    const lyt    = getLayout(type);
    const active = TYPE_FIELDS[type];
    document.querySelectorAll('.cert-element').forEach(el => {
        const f = el.dataset.field;
        const on = active.includes(f);
        el.style.display = on ? '' : 'none';
        if (on) applyToEl(el, lyt[f]);
    });
    deselect();
}

function applyToEl(el, cfg) {
    el.style.left  = cfg.left  + '%';
    el.style.top   = cfg.top   + '%';
    el.style.width = cfg.width + '%';
    const span = el.querySelector('.el-text');
    span.innerHTML        = cfg.text;   // HTML for inline rich-text support
    span.style.fontSize   = cfg.fontSize   + 'px';
    span.style.fontWeight = cfg.fontWeight;
    span.style.fontStyle  = cfg.fontStyle;
    span.style.fontFamily = cfg.fontFamily;
    span.style.color      = cfg.color;
    span.style.textAlign  = cfg.textAlign;
}

/* ── Drag ───────────────────────────────────────────────────────────────── */
let drag = null;

document.querySelectorAll('.cert-element').forEach(el => {
    el.addEventListener('mousedown', function(e) {
        if (e.target.classList.contains('rh')) return;
        e.preventDefault();
        select(this);
        drag = { el: this, sx: e.clientX, sy: e.clientY, sl: this.offsetLeft, st: this.offsetTop };
    });
});

/* ── Resize ─────────────────────────────────────────────────────────────── */
let rsz = null;

document.querySelectorAll('.rh').forEach(h => {
    h.addEventListener('mousedown', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const el = this.closest('.cert-element');
        select(el);
        rsz = { el, sx: e.clientX, sw: el.offsetWidth };
    });
});

/* ── Global mouse move / up ─────────────────────────────────────────────── */
document.addEventListener('mousemove', function(e) {
    if (drag) {
        const dx = (e.clientX - drag.sx) / canvasScale;
        const dy = (e.clientY - drag.sy) / canvasScale;
        const nl = Math.max(0, Math.min(drag.sl + dx, CW - drag.el.offsetWidth));
        const nt = Math.max(0, Math.min(drag.st + dy, CH - drag.el.offsetHeight));
        drag.el.style.left = nl + 'px';
        drag.el.style.top  = nt + 'px';
        syncFromEl(drag.el);
    }
    if (rsz) {
        const dx = (e.clientX - rsz.sx) / canvasScale;
        const nw = Math.max(50, Math.min(rsz.sw + dx, CW - rsz.el.offsetLeft));
        rsz.el.style.width = nw + 'px';
        syncFromEl(rsz.el);
        if (selEl === rsz.el) refreshWidthUI(rsz.el);
    }
});

document.addEventListener('mouseup', () => { drag = null; rsz = null; });

// Deselect on canvas background click
document.getElementById('certCanvas').addEventListener('mousedown', function(e) {
    if (e.target === this || e.target === document.getElementById('certBg')) deselect();
});

/* ── Selection ──────────────────────────────────────────────────────────── */
function select(el) {
    deselect();
    el.classList.add('active');
    selEl = el;

    const f   = el.dataset.field;
    const cfg = layouts[curType][f];

    document.getElementById('noSel').style.display      = 'none';
    document.getElementById('propPanel').style.display  = '';
    document.getElementById('propTitle').textContent    = cfg.label + ' Properties';

    const propTextEl = document.getElementById('propText');
    propTextEl.innerHTML = cfg.text;   // rich HTML
    document.getElementById('propSize').value  = cfg.fontSize;
    document.getElementById('propColor').value = cfg.color;
    document.getElementById('propFont').value  = cfg.fontFamily;
    refreshWidthUI(el);

    setToggle('btnBold',   cfg.fontWeight === 'bold');
    setToggle('btnItalic', cfg.fontStyle  === 'italic');
    setAlign(cfg.textAlign);

    const hasVars = f === 'description' || f === 'date';
    document.getElementById('varsCard').style.display = hasVars ? '' : 'none';
    document.querySelectorAll('.vars-group').forEach(g => {
        g.style.display = g.dataset.group === f ? '' : 'none';
    });
}

function deselect() {
    document.querySelectorAll('.cert-element').forEach(e => e.classList.remove('active'));
    selEl = null;
    document.getElementById('noSel').style.display     = '';
    document.getElementById('propPanel').style.display = 'none';
    document.getElementById('propTitle').textContent   = 'Properties';
    document.getElementById('varsCard').style.display  = 'none';
}

function refreshWidthUI(el) {
    const pct = Math.round((el.offsetWidth / CW) * 100);
    document.getElementById('propWidth').value   = pct;
    document.getElementById('widthLbl').textContent = pct + '%';
}

function setToggle(id, active) {
    const btn = document.getElementById(id);
    btn.classList.toggle('btn-primary',         active);
    btn.classList.toggle('btn-outline-secondary', !active);
}

function setAlign(align) {
    document.querySelectorAll('.aln-btn').forEach(b => {
        const on = b.dataset.align === align;
        b.classList.toggle('btn-primary',         on);
        b.classList.toggle('btn-outline-secondary', !on);
    });
}

/* ── Property controls ──────────────────────────────────────────────────── */
function applyProps() {
    if (!selEl) return;
    const f   = selEl.dataset.field;
    const cfg = layouts[curType][f];
    cfg.text       = document.getElementById('propText').innerHTML;  // rich HTML
    cfg.fontSize   = parseInt(document.getElementById('propSize').value) || 14;
    cfg.color      = document.getElementById('propColor').value;
    cfg.fontFamily = document.getElementById('propFont').value;
    applyToEl(selEl, cfg);
}

// propText is now contenteditable — use 'input' event
document.getElementById('propText').addEventListener('input', applyProps);

['propSize','propColor','propFont'].forEach(id => {
    const el = document.getElementById(id);
    el.addEventListener('input',  applyProps);
    el.addEventListener('change', applyProps);
});

document.getElementById('propWidth').addEventListener('input', function() {
    if (!selEl) return;
    const pct = +this.value;
    document.getElementById('widthLbl').textContent = pct + '%';
    selEl.style.width = pct + '%';
    syncFromEl(selEl);
    layouts[curType][selEl.dataset.field].width = pct;
});

document.getElementById('btnBold').addEventListener('click', function() {
    if (!selEl) return;
    const cfg = layouts[curType][selEl.dataset.field];
    cfg.fontWeight = cfg.fontWeight === 'bold' ? 'normal' : 'bold';
    selEl.querySelector('.el-text').style.fontWeight = cfg.fontWeight;
    setToggle('btnBold', cfg.fontWeight === 'bold');
});

document.getElementById('btnItalic').addEventListener('click', function() {
    if (!selEl) return;
    const cfg = layouts[curType][selEl.dataset.field];
    cfg.fontStyle = cfg.fontStyle === 'italic' ? 'normal' : 'italic';
    selEl.querySelector('.el-text').style.fontStyle = cfg.fontStyle;
    setToggle('btnItalic', cfg.fontStyle === 'italic');
});

document.querySelectorAll('.aln-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!selEl) return;
        const align = this.dataset.align;
        const cfg   = layouts[curType][selEl.dataset.field];
        cfg.textAlign = align;
        selEl.querySelector('.el-text').style.textAlign = align;
        setAlign(align);
    });
});

/* ── Rich-text toolbar ──────────────────────────────────────────────────── */
(function () {
    // Save/restore selection so button clicks don't lose contenteditable focus
    let savedRange = null;

    function saveSelection() {
        const sel = window.getSelection();
        if (sel && sel.rangeCount > 0) savedRange = sel.getRangeAt(0).cloneRange();
    }

    function restoreSelection() {
        if (!savedRange) return;
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(savedRange);
    }

    document.getElementById('propText').addEventListener('mouseup',  saveSelection);
    document.getElementById('propText').addEventListener('keyup',    saveSelection);
    document.getElementById('propText').addEventListener('focusout', saveSelection);

    function rtExec(cmd, value) {
        document.getElementById('propText').focus();
        restoreSelection();
        document.execCommand(cmd, false, value || null);
        saveSelection();
        applyProps();
    }

    document.getElementById('rtBold').addEventListener('mousedown', function(e) {
        e.preventDefault(); rtExec('bold');
    });
    document.getElementById('rtItalic').addEventListener('mousedown', function(e) {
        e.preventDefault(); rtExec('italic');
    });
    document.getElementById('rtUnder').addEventListener('mousedown', function(e) {
        e.preventDefault(); rtExec('underline');
    });
    document.getElementById('rtClearFmt').addEventListener('mousedown', function(e) {
        e.preventDefault();
        rtExec('removeFormat');
    });

    const colorPick = document.getElementById('rtColorPick');
    const colorSwatch = document.getElementById('rtColorSwatch');
    colorPick.addEventListener('input', function() {
        colorSwatch.style.background = this.value;
    });
    colorPick.addEventListener('change', function() {
        colorSwatch.style.background = this.value;
        document.getElementById('propText').focus();
        restoreSelection();
        document.execCommand('foreColor', false, this.value);
        saveSelection();
        applyProps();
    });
})();

/* ── Variable chips ─────────────────────────────────────────────────────── */
document.querySelectorAll('.var-chip').forEach(chip => {
    chip.addEventListener('click', function() {
        const propTextEl = document.getElementById('propText');
        const v = this.dataset.var;
        propTextEl.focus();
        // Insert plain text at cursor inside contenteditable
        const sel = window.getSelection();
        if (sel && sel.rangeCount > 0) {
            const range = sel.getRangeAt(0);
            range.deleteContents();
            const textNode = document.createTextNode(v);
            range.insertNode(textNode);
            range.setStartAfter(textNode);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
        } else {
            propTextEl.innerHTML += v;
        }
        applyProps();
    });
});

/* ── Tabs ───────────────────────────────────────────────────────────────── */
document.querySelectorAll('#certTabs .nav-link').forEach(tab => {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('#certTabs .nav-link').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        curType = this.dataset.type;
        renderCanvas(curType);
    });
});

/* ── Save ───────────────────────────────────────────────────────────────── */
document.getElementById('saveBtn').addEventListener('click', async function() {
    // Only sync visible (active) elements for this type
    document.querySelectorAll('.cert-element').forEach(el => {
        if (TYPE_FIELDS[curType].includes(el.dataset.field)) syncFromEl(el);
    });

    // Build payload with only the active fields
    const activeLayout = {};
    TYPE_FIELDS[curType].forEach(f => { activeLayout[f] = layouts[curType][f]; });

    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

    try {
        const res  = await fetch('<?= BASE_URL ?>/admin/save-certificate-layout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: curType, layout: activeLayout }),
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'danger');
        if (data.success) SAVED[curType] = layouts[curType];
    } catch {
        showToast('Network error — could not save', 'danger');
    } finally {
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-floppy me-1"></i>Save Layout';
    }
});

function showToast(msg, type) {
    const t = document.getElementById('toast');
    t.className = `toast align-items-center text-bg-${type} border-0`;
    document.getElementById('toastMsg').textContent = msg;
    bootstrap.Toast.getOrCreateInstance(t, { delay: 3000 }).show();
}

/* ── Boot ───────────────────────────────────────────────────────────────── */
renderCanvas(curType);
</script>
</body>
</html>
