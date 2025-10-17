<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__.'/PHPMailer/src/Exception.php';
require __DIR__.'/PHPMailer/src/PHPMailer.php';
require __DIR__.'/PHPMailer/src/SMTP.php';

function sendOTP(string $toEmail, string $toName, string $otp): array {
  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'youremail@gmail.com';     // SENDER
    $mail->Password   = 'your-app-password';       // App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('youremail@gmail.com', 'HR1 MerchFlow');
    $mail->addAddress($toEmail, $toName);          // ← DITO PAPADALA (from form)
    $mail->isHTML(true);
    $mail->Subject = 'Your HR1 MerchFlow OTP';
    $mail->Body = "
      <p>Hi <b>{$toName}</b>,</p>
      <p>Your one-time code:</p>
      <h2 style='letter-spacing:2px;color:#E11D48'>{$otp}</h2>
      <p>This will expire shortly. If you didn’t request this, ignore this email.</p>
    ";
    $mail->send();
    return [true, 'sent'];
  } catch (Exception $e) {
    return [false, $mail->ErrorInfo];
  }
}
