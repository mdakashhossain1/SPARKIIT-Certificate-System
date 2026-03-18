<?php
/**
 * Email template: Form submission confirmation
 * Variables: $submission (array), $orgName (string)
 */
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Application Received</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:30px 0;">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.1);">
        <!-- Header -->
        <tr>
          <td style="background:linear-gradient(135deg,#1a237e,#283593);padding:30px 40px;text-align:center;">
            <h1 style="color:#ffffff;margin:0;font-size:24px;letter-spacing:1px;"><?= htmlspecialchars($orgName) ?></h1>
            <p style="color:#c5cae9;margin:8px 0 0;font-size:14px;">Certificate Management System</p>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td style="padding:40px;">
            <h2 style="color:#1a237e;margin:0 0 10px;font-size:20px;">Application Received!</h2>
            <p style="color:#555;line-height:1.6;margin:0 0 20px;">
              Dear <strong><?= htmlspecialchars($submission['name']) ?></strong>,<br><br>
              Thank you for applying to <strong><?= htmlspecialchars($orgName) ?></strong>. We have successfully received your enrollment application and our team will review it shortly.
            </p>

            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9ff;border:1px solid #e8eaf6;border-radius:6px;margin:20px 0;">
              <tr>
                <td style="padding:20px;">
                  <h3 style="color:#1a237e;margin:0 0 15px;font-size:16px;border-bottom:2px solid #e8eaf6;padding-bottom:10px;">Your Application Details</h3>
                  <table width="100%" cellpadding="5" cellspacing="0">
                    <tr>
                      <td style="color:#666;font-size:13px;width:40%;vertical-align:top;"><strong>Name:</strong></td>
                      <td style="color:#333;font-size:13px;"><?= htmlspecialchars($submission['name']) ?></td>
                    </tr>
                    <tr>
                      <td style="color:#666;font-size:13px;vertical-align:top;"><strong>Email:</strong></td>
                      <td style="color:#333;font-size:13px;"><?= htmlspecialchars($submission['email']) ?></td>
                    </tr>
                    <tr>
                      <td style="color:#666;font-size:13px;vertical-align:top;"><strong>College:</strong></td>
                      <td style="color:#333;font-size:13px;"><?= htmlspecialchars($submission['college_name']) ?></td>
                    </tr>
                    <?php if (!empty($submission['courses_selected'])): ?>
                    <tr>
                      <td style="color:#666;font-size:13px;vertical-align:top;"><strong>Courses:</strong></td>
                      <td style="color:#333;font-size:13px;">
                        <?php
                          $courses = json_decode($submission['courses_selected'], true) ?? [];
                          echo htmlspecialchars(implode(', ', $courses));
                        ?>
                      </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                      <td style="color:#666;font-size:13px;vertical-align:top;"><strong>Batch:</strong></td>
                      <td style="color:#333;font-size:13px;"><?= htmlspecialchars($submission['batch'] ?? '-') ?></td>
                    </tr>
                    <tr>
                      <td style="color:#666;font-size:13px;vertical-align:top;"><strong>Program:</strong></td>
                      <td style="color:#333;font-size:13px;"><?= htmlspecialchars(ucfirst($submission['total_program'] ?? '-')) ?></td>
                    </tr>
                    <?php if (!empty($submission['internship_duration'])): ?>
                    <tr>
                      <td style="color:#666;font-size:13px;vertical-align:top;"><strong>Duration:</strong></td>
                      <td style="color:#333;font-size:13px;"><?= htmlspecialchars($submission['internship_duration']) ?></td>
                    </tr>
                    <?php endif; ?>
                  </table>
                </td>
              </tr>
            </table>

            <p style="color:#555;line-height:1.6;margin:20px 0;">
              We will be in touch soon regarding next steps. If you have any questions, please contact us at <a href="mailto:info@SPARKIIT.com" style="color:#1a237e;">info@SPARKIIT.com</a>.
            </p>

            <p style="color:#555;line-height:1.6;margin:0;">
              Best regards,<br>
              <strong>The <?= htmlspecialchars($orgName) ?> Team</strong>
            </p>
          </td>
        </tr>
        <!-- Footer -->
        <tr>
          <td style="background:#f8f9ff;padding:20px 40px;text-align:center;border-top:1px solid #e8eaf6;">
            <p style="color:#999;font-size:12px;margin:0;">
              &copy; <?= date('Y') ?> <?= htmlspecialchars($orgName) ?>. All rights reserved.<br>
              This is an automated email. Please do not reply directly to this message.
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
