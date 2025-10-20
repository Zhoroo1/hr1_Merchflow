<?php
declare(strict_types=1);
ob_start();
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../mail_config.php'; // has sendOTP($toEmail, $otp)

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
date_default_timezone_set('Asia/Manila');

function jbad($m,$c=400){ http_response_code($c); echo json_encode(['ok'=>false,'error'=>$m]); exit; }
function jok($d=[]){ echo json_encode(['ok'=>true,'data'=>$d]); exit; }

// Accept JSON or form
$raw = file_get_contents('php://input');
$in  = json_decode($raw ?: '[]', true);
if (!is_array($in) || !$in) $in = $_POST;

$email = trim((string)($in['email'] ?? ''));
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) jbad('Please enter a valid email');

// Find active user by email
$st = $pdo->prepare("SELECT id, email, is_active FROM users WHERE email = ? LIMIT 1");
$st->execute([$email]);
$user = $st->fetch(PDO::FETCH_ASSOC);
if (!$user) jbad('Email not found', 404);
if ((int)$user['is_active'] !== 1) jbad('Account is inactive', 403);

// Ensure otp_codes table exists
$pdo->exec("
  CREATE TABLE IF NOT EXISTS otp_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    code  VARCHAR(10) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_user (user_id),
    INDEX idx_email (email),
    INDEX idx_exp (expires_at)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Optional throttle: deny if a code was sent in last 60s
$chk = $pdo->prepare("SELECT created_at FROM otp_codes WHERE email=? ORDER BY id DESC LIMIT 1");
$chk->execute([$email]);
$last = $chk->fetchColumn();
if ($last && (time() - strtotime($last)) < 60) jbad('Please wait a minute before requesting another code.');

// Generate 6-digit OTP
$otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expires = date('Y-m-d H:i:s', time() + 300); // 5 minutes

// Save
$ins = $pdo->prepare("INSERT INTO otp_codes (user_id,email,code,expires_at,created_at) VALUES (?,?,?,?,NOW())");
$ins->execute([(int)$user['id'], $email, $otp, $expires]);

// Send to the user's email (NOT to the SMTP account)
$ok = sendOTP($email, $otp); // sendOTP comes from mail_config.php
if (!$ok) jbad('Failed to send OTP (mail transport error)');

jok(['sent'=>true,'expires_at'=>$expires]);
