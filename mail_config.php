<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * INTERNAL: low-level sender, with TLS(587) → SMTPS(465) fallback + debug to PHP error_log
 */
function __send_mail_core(
    string $to,
    string $subject,
    string $html,
    string $alt,
    string $fromEmail,
    string $fromName,
    string $user,
    string $pass
): array {
    $mail = new PHPMailer(true);

    // pipe PHPMailer's debug to PHP error_log (won’t show to users)
    $mail->SMTPDebug   = SMTP::DEBUG_OFF; // set to DEBUG_SERVER if you need verbose logs
    $mail->Debugoutput = static function ($str) {
        error_log('[MAIL] ' . $str);
    };

    try {
        // First attempt: TLS 587
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $user;
        $mail->Password   = $pass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);
        $mail->addReplyTo($fromEmail, $fromName);

        $mail->isHTML(true);
        $mail->CharSet  = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->Subject  = $subject;
        $mail->Body     = $html; // HTML body as-is
        $mail->AltBody  = $alt;  // Plain-text fallback

        $mail->send();
        return [true, null];

    } catch (Exception $e1) {
        // Fallback: SSL 465
        try {
            $mail->clearAddresses();
            $mail->clearReplyTos();

            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $user;
            $mail->Password   = $pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);
            $mail->addReplyTo($fromEmail, $fromName);

            $mail->isHTML(true);
            $mail->CharSet  = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->Subject  = $subject;
            $mail->Body     = $html;
            $mail->AltBody  = $alt;

            $mail->send();
            return [true, null];
        } catch (Exception $e2) {
            $msg = $mail->ErrorInfo ?: ($e2->getMessage() ?: $e1->getMessage());
            error_log('[MAIL][FAIL] ' . $msg);
            return [false, $msg];
        }
    }
}

/**
 * OTP sender (existing behavior)
 */
function sendOTP(string $toEmail, string $otp): bool {
    // Gmail/App Password ng sender (ito ang lalabas sa "From")
    $USER = 'danv66215@gmail.com';  // sender account
    $PASS = 'ogloqshjqaomkxvo';     // 16-char app password

    $sub  = 'Your HR1 Nextgenmms OTP';
    $html = "Your OTP code is: <b>{$otp}</b>. Valid for 5 minutes.";
    $alt  = "Your OTP code is: {$otp} (valid 5 minutes).";

    // IMPORTANT: $toEmail ang tatanggap (walang pinapalitan)
    [$ok, $err] = __send_mail_core(
        $toEmail, $sub, $html, $alt,
        $USER, 'HR1 Nextgenmms', $USER, $PASS
    );

    if (!$ok) {
        error_log('[sendOTP] mail error: ' . ($err ?? 'unknown'));
    }
    return $ok;
}

/**
 * Generic HR mail sender – used by user creation / “notify applicant”
 * Returns [bool $ok, ?string $error]
 */
function sendHRMail(string $toEmail, string $subject, string $htmlBody, ?string $altBody=null): array {
    $USER = 'danv66215@gmail.com';  // Gmail account
    $PASS = 'ogloqshjqaomkxvo';     // App Password (16 chars)

    // If looks like HTML, send as-is; otherwise convert line breaks.
    $looksHtml = (strpos($htmlBody, '<') !== false);
    $html = $looksHtml ? $htmlBody : nl2br($htmlBody);
    $alt  = $altBody ?: strip_tags($htmlBody);

    return __send_mail_core(
        $toEmail, $subject, $html, $alt,
        $USER, 'HR1 Nextgenmms', $USER, $PASS
    );
}
