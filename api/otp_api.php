<?php
declare(strict_types=1);
ob_start();
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../mail_config.php'; // sendOTP()

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
date_default_timezone_set('Asia/Manila');

function read_json() {
  $raw = file_get_contents('php://input');
  $j = json_decode($raw, true);
  return is_array($j) ? $j : [];
}
function out($d){ echo json_encode(['ok'=>true,'data'=>$d]); exit; }
function fail($m,$c=400){ http_response_code($c); echo json_encode(['ok'=>false,'error'=>$m]); exit; }

$in = read_json();
$act = strtolower((string)($in['action'] ?? ''));

/* ---------- SEND OTP ---------- */
if ($act === 'send') {
  $email = trim((string)($in['email'] ?? ''));
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) fail('Invalid email');

  // hanapin ang user sa DB para siguradong existing ang mailbox
  $st = $pdo->prepare("SELECT id, email FROM users WHERE email = ? AND is_active = 1");
  $st->execute([$email]);
  $u = $st->fetch(PDO::FETCH_ASSOC);
  if (!$u) fail('Email not found or inactive', 404);

  // generate + save OTP (valid 5 minutes)
  $otp = random_int(100000, 999999);
  $exp = date('Y-m-d H:i:s', time() + 5*60);
  $pdo->prepare("UPDATE users SET otp_code = ?, otp_expires = ? WHERE id = ?")
      ->execute([(string)$otp, $exp, (int)$u['id']]);

  // IMPORTANT: ipadala sa email na HINANAP sa DB (hindi hard-coded)
  $ok = sendOTP($u['email'], (string)$otp);
  if (!$ok) fail('Failed to send OTP. Please try again.');

  out(['sent'=>true,'expires'=>$exp]);
}

/* ---------- VERIFY OTP ---------- */
if ($act === 'verify') {
  $email = trim((string)($in['email'] ?? ''));
  $code  = trim((string)($in['otp'] ?? ''));
  if ($email === '' || $code === '') fail('Email and OTP required');

  $st = $pdo->prepare("SELECT id, otp_code, otp_expires FROM users WHERE email = ?");
  $st->execute([$email]);
  $u = $st->fetch(PDO::FETCH_ASSOC);
  if (!$u) fail('Invalid email', 404);

  if (!$u['otp_code'] || !$u['otp_expires']) fail('No OTP requested');
  if (time() > strtotime($u['otp_expires'])) fail('OTP expired', 410);
  if (hash_equals((string)$u['otp_code'], $code) === false) fail('Incorrect OTP', 401);

  // success → clear OTP (optional)
  $pdo->prepare("UPDATE users SET otp_code = NULL, otp_expires = NULL WHERE id = ?")
      ->execute([(int)$u['id']]);

  out(['verified'=>true]);
}

fail('Unknown action',404);
