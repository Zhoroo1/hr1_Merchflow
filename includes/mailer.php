<?php
// includes/mailer.php
declare(strict_types=1);

/**
 * Minimal PHPMailer wrapper.
 * Run: composer require phpmailer/phpmailer
 * Or swap to PHP mail() if you prefer.
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php'; // path to Composer autoload

function send_mail(string $toEmail, string $toName, string $subject, string $html, string $text=''): array {
  $mail = new PHPMailer(true);
  try {
    // --- SMTP CONFIG ---
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'YOUR_SMTP_USERNAME@gmail.com';
    $mail->Password   = 'YOUR_APP_PASSWORD_OR_SMTP_PASSWORD';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // --- FROM/TO ---
    $mail->setFrom('no-reply@nextgenmms.local', 'Nextgenmms');
    $mail->addAddress($toEmail, $toName ?: $toEmail);

    // --- CONTENT ---
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $html;
    $mail->AltBody = $text ?: strip_tags($html);

    $mail->send();
    return ['ok'=>true, 'error'=>null];
  } catch (Exception $e) {
    return ['ok'=>false, 'error'=>$mail->ErrorInfo ?: $e->getMessage()];
  }
}
