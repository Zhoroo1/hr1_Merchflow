<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';   // USE_2FA / DEV_MAIL_REDIRECT / OTP_TTL
require_once __DIR__ . '/mail_config.php';

try {
  $db = (new Database())->getConnection();
} catch (Throwable $e) {
  header('Location: login.php?error=server'); exit;
}

/* -------- Inputs -------- */
$email = strtolower(trim($_POST['email'] ?? ''));
$pass  = (string)($_POST['password'] ?? '');
if ($email === '' || $pass === '') { header('Location: login.php?error=1'); exit; }

/* -------- Fetch user -------- */
$stmt = $db->prepare("SELECT id, name, role, email, password_hash FROM users WHERE LOWER(email) = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

/* -------- Verify password (supports bcrypt or legacy md5) -------- */
$ok = false;
if ($user && !empty($user['password_hash'])) {
  $hash = (string)$user['password_hash'];
  if (strpos($hash, '$2y$') === 0) {            // bcrypt
    $ok = password_verify($pass, $hash);
  } elseif (strlen($hash) === 32) {             // legacy md5
    $ok = (md5($pass) === strtolower($hash));
  }
}
if (!$ok) { header('Location: login.php?error=1'); exit; }

/* -------- Build session user + target redirect by role -------- */
$role = strtolower((string)$user['role']);
$sessionUser = [
  'id'    => (int)$user['id'],
  'name'  => trim((string)$user['name']),
  'email' => (string)$user['email'],
  'role'  => $role,
];

$target = in_array($role, ['admin','hr manager','superadmin'], true)
  ? 'index.php'               // admin / hr
  : 'employee_home.php';      // employee landing page

/* -------- 2FA flow (if enabled) -------- */
if (USE_2FA) {
  // Do NOT set $_SESSION['user'] yet.
  $otp = (string)random_int(100000, 999999);

  $_SESSION['pending_user']     = $sessionUser;
  $_SESSION['2fa_code']         = $otp;
  $_SESSION['2fa_expires']      = time() + (int)OTP_TTL;
  $_SESSION['post_login_target']= $target;   // so verify_2fa.php knows where to go

  // During dev, optionally redirect all mail to a safe inbox
  $to = (DEV_MAIL_REDIRECT !== '') ? DEV_MAIL_REDIRECT : $sessionUser['email'];

  if (sendOTP($to, $otp)) {
    $_SESSION['2fa_last_sent'] = time();
    header('Location: verify_2fa.php'); exit;
  }
  // Failed to send OTP — clean up & show error
  unset($_SESSION['pending_user'], $_SESSION['2fa_code'], $_SESSION['2fa_expires'], $_SESSION['2fa_last_sent'], $_SESSION['post_login_target']);
  header('Location: login.php?error=otp'); exit;
}

/* -------- No 2FA: finalize login -------- */
$_SESSION['user'] = $sessionUser;
header("Location: {$target}"); exit;
