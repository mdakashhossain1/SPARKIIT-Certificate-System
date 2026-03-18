<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/mail.php';

/**
 * Send an email via SMTP using PHPMailer.
 *
 * @param string      $toEmail
 * @param string      $toName
 * @param string      $subject
 * @param string      $htmlBody
 * @param string|null $attachmentPath  Full filesystem path to file
 * @param string|null $attachmentName  Display name for attachment
 * @return bool
 */
function sendMail(
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody,
    ?string $attachmentPath = null,
    ?string $attachmentName = null
): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo(MAIL_FROM_EMAIL, MAIL_FROM_NAME);

        if ($attachmentPath && file_exists($attachmentPath)) {
            $name = $attachmentName ?? basename($attachmentPath);
            $mail->addAttachment($attachmentPath, $name);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('PHPMailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}
