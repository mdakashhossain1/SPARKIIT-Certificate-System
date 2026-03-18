<?php
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/mail.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/helpers.php';
require_once dirname(__DIR__, 2) . '/includes/cert_pdf_generator.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: submissions');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) {
    header('Location: submissions');
    exit;
}

$db = getDB();

// Fetch submission
$stmt = $db->prepare(
    'SELECT id, name, email, courses_selected, days,
            start_date, end_date, certificate_date, show_certificate
     FROM form_submissions WHERE id = ? LIMIT 1'
);
$stmt->execute([$id]);
$cert = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cert || empty($cert['show_certificate'])) {
    $_SESSION['flash_error'] = 'Certificate is not enabled for this submission.';
    header('Location: edit-submission?id=' . $id);
    exit;
}

// Load all 3 layouts
$layoutRows = $db->query("SELECT type, layout_json FROM certificate_layouts")->fetchAll(PDO::FETCH_ASSOC);
$layouts = [];
foreach ($layoutRows as $lr) {
    $layouts[$lr['type']] = json_decode($lr['layout_json'], true);
}

if (empty($layouts)) {
    $_SESSION['flash_error'] = 'No certificate layouts configured. Please build layouts first.';
    header('Location: edit-submission?id=' . $id);
    exit;
}

// Generate PDFs for all available types
$attachments = [];
$typeLabels  = ['training' => 'Training', 'participation' => 'Participation', 'internship' => 'Internship'];
$safeName    = preg_replace('/[^a-z0-9_-]/i', '_', $cert['name']);

foreach (['training', 'participation', 'internship'] as $type) {
    if (empty($layouts[$type])) continue;
    $pdf = generateCertificatePdf($cert, $type, $layouts[$type]);
    $attachments[$type] = [
        'data'     => $pdf,
        'filename' => $safeName . '_' . $type . '_certificate.pdf',
        'label'    => $typeLabels[$type],
    ];
}

if (empty($attachments)) {
    $_SESSION['flash_error'] = 'Could not generate any certificate PDFs.';
    header('Location: edit-submission?id=' . $id);
    exit;
}

// Build HTML email body
$courses_parsed = json_decode($cert['courses_selected'] ?? '[]', true);
$program_name   = is_array($courses_parsed) ? implode(', ', $courses_parsed) : '';
$certDateStr    = $cert['certificate_date'] ? date('d F Y', strtotime($cert['certificate_date'])) : date('d F Y');
$attachList     = implode(', ', array_column($attachments, 'label'));

$emailBody = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f4f8;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f8;padding:40px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);">

      <!-- Header -->
      <tr>
        <td style="background:linear-gradient(135deg,#5b21b6 0%,#7c3aed 100%);padding:32px 40px;text-align:center;">
          <h1 style="margin:0;color:#fff;font-size:24px;font-weight:700;letter-spacing:-.3px;">🎓 Your Certificates Are Ready!</h1>
          <p style="margin:8px 0 0;color:rgba(255,255,255,.8);font-size:14px;">Congratulations on completing your program with SPARKIIT</p>
        </td>
      </tr>

      <!-- Body -->
      <tr>
        <td style="padding:36px 40px;">
          <p style="margin:0 0 20px;font-size:16px;color:#1e1e2e;">Dear <strong>{$cert['name']}</strong>,</p>
          <p style="margin:0 0 20px;font-size:14px;color:#4b5563;line-height:1.7;">
            Congratulations! We are delighted to inform you that your certificates for <strong>{$program_name}</strong>
            have been issued and are attached to this email.
          </p>

          <!-- Certificate cards -->
          <table width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;">
HTML;

foreach ($attachments as $type => $att) {
    $icons = ['training' => '🎓', 'participation' => '🏅', 'internship' => '💼'];
    $icon  = $icons[$type] ?? '📄';
    $emailBody .= <<<HTML
            <tr>
              <td style="background:#faf9ff;border:1.5px solid #ede9fe;border-radius:10px;padding:16px 20px;margin-bottom:10px;display:block;">
                <span style="font-size:20px;">{$icon}</span>
                <span style="font-size:14px;font-weight:600;color:#5b21b6;margin-left:8px;">{$att['label']} Certificate</span>
                <span style="font-size:12px;color:#9ca3af;margin-left:8px;">— Attached as PDF</span>
              </td>
            </tr>
            <tr><td style="height:8px;"></td></tr>
HTML;
}

$emailBody .= <<<HTML
          </table>

          <p style="margin:0 0 16px;font-size:14px;color:#4b5563;line-height:1.7;">
            You can also view and download your certificates online at any time by visiting our
            <a href="{$_SERVER['HTTP_HOST']}" style="color:#7c3aed;text-decoration:none;font-weight:600;">certificate verification page</a>.
          </p>

          <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:16px 20px;margin:20px 0;">
            <p style="margin:0;font-size:13px;color:#166534;"><strong>📅 Certificate Date:</strong> {$certDateStr}</p>
          </div>

          <p style="margin:24px 0 0;font-size:14px;color:#6b7280;line-height:1.7;">
            If you have any questions, feel free to reach out to us.<br>
            Wish you all the best for your future endeavors!
          </p>
          <p style="margin:16px 0 0;font-size:14px;color:#1e1e2e;font-weight:600;">— Team SPARKIIT</p>
        </td>
      </tr>

      <!-- Footer -->
      <tr>
        <td style="background:#f9f5ff;border-top:1px solid #ede9fe;padding:20px 40px;text-align:center;">
          <p style="margin:0;font-size:12px;color:#9ca3af;">
            This is an automated email from SPARKIIT Certificate System.<br>
            Please do not reply to this email.
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
HTML;

// Send via PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailException;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

try {
    $mail = new PHPMailer(true);

    // Server settings
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = MAIL_ENCRYPTION === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = MAIL_PORT;
    $mail->CharSet    = 'UTF-8';

    // From / To
    $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
    $mail->addAddress($cert['email'], $cert['name']);
    $mail->addReplyTo(MAIL_FROM_EMAIL, MAIL_FROM_NAME);

    // Content
    $mail->isHTML(true);
    $mail->Subject = '🎓 Your SPARKIIT Certificates – ' . $cert['name'];
    $mail->Body    = $emailBody;
    $mail->AltBody = 'Dear ' . $cert['name'] . ', your SPARKIIT certificates (' . $attachList . ') are attached to this email. Congratulations!';

    // Attach all PDFs
    foreach ($attachments as $att) {
        $mail->addStringAttachment($att['data'], $att['filename'], PHPMailer::ENCODING_BASE64, 'application/pdf');
    }

    $mail->send();

    $_SESSION['flash_success'] = 'Certificate email sent successfully to <strong>' . htmlspecialchars($cert['email'], ENT_QUOTES) . '</strong> with ' . count($attachments) . ' PDF attachment(s).';

} catch (MailException $e) {
    $_SESSION['flash_error'] = 'Failed to send email: ' . htmlspecialchars($mail->ErrorInfo, ENT_QUOTES);
} catch (\Throwable $e) {
    $_SESSION['flash_error'] = 'Error generating certificates: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
}

header('Location: edit-submission?id=' . $id);
exit;
