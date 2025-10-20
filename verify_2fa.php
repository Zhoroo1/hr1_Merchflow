<?php
session_start();
if (empty($_SESSION['pending_user'])) { header("Location: login.php"); exit; }

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/mail_config.php';

/*
  This version forces OTP delivery to the actual user email stored in
  $_SESSION['pending_user']['email'] (no DEV_MAIL_REDIRECT override).
*/

/* Masked email for display only */
$actualEmail = $_SESSION['pending_user']['email'] ?? '';
function mask_email($e){
  if (!filter_var($e, FILTER_VALIDATE_EMAIL)) return $e;
  [$u,$d] = explode('@',$e,2);
  $uMask = strlen($u)<=2 ? $u[0].'*' : substr($u,0,2).str_repeat('*', max(1, strlen($u)-4)).substr($u,-2);
  $dParts = explode('.', $d);
  $dParts[0] = substr($dParts[0],0,1).'***';
  return $uMask.'@'.implode('.', $dParts);
}
$displayEmail = mask_email($actualEmail);

/* Handle POST verify */
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'verify') {
  $code = trim($_POST['otp'] ?? '');
  if ($code !== '' && isset($_SESSION['2fa_code'], $_SESSION['2fa_expires']) &&
      time() < (int)$_SESSION['2fa_expires'] && hash_equals((string)$_SESSION['2fa_code'], $code)) {

    $_SESSION['user'] = $_SESSION['pending_user'];
    unset($_SESSION['pending_user'], $_SESSION['2fa_code'], $_SESSION['2fa_expires'], $_SESSION['2fa_last_sent']);
    header('Location: index.php'); exit;
  } else {
    $err = 'Invalid or expired code.';
  }
}

/* Handle resend */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'resend') {
  $now = time();
  $last = (int)$_SESSION['2fa_last_sent'] ?? 0;
  if ($now - $last < (int)OTP_RESEND_COOLDOWN) {
    $err = 'Please wait a bit before requesting another code.';
  } else {
    $otp = (string)random_int(100000, 999999);
    $_SESSION['2fa_code']    = $otp;
    $_SESSION['2fa_expires'] = $now + (int)OTP_TTL;

    // ALWAYS send to the real user email (no DEV redirect)
    $to = $actualEmail;
    error_log('[OTP RESEND] sending to='.$to);

    if ($to && filter_var($to, FILTER_VALIDATE_EMAIL) && sendOTP($to, $otp)) {
      $_SESSION['2fa_last_sent'] = $now;
      $err = 'A new code has been sent.';
    } else {
      $err = 'Failed to send OTP. Try again later.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Verify OTP | HR1 MerchFlow</title>
  <link rel="icon" type="image/png" href="assets/logo3.png">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-r from-pink-500 via-red-400 to-blue-500 p-4">

  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
    <div class="p-8">
      <!-- logo -->
      <div class="text-center mb-6">
        <div class="mx-auto mb-4 w-[84px] h-[84px] rounded-lg overflow-hidden bg-white shadow-[0_12px_28px_rgba(59,130,246,0.22)] ring-1 ring-slate-300/60">
          <img src="assets/logo2.jpg" alt="O!Save Logo" class="w-full h-full object-cover" />
        </div>
        <h1 class="text-2xl font-bold text-red-600">Two-Factor Verification</h1>
        <p class="text-gray-600 mt-1">
          We sent a 6-digit verification code to your email.
        </p>
      </div>

      <?php if ($err): ?>
        <div class="mb-4 rounded-lg bg-red-50 text-red-700 px-3 py-2 text-sm"><?= htmlspecialchars($err) ?></div>
      <?php endif; ?>

      <form method="post" class="space-y-4">
        <input type="hidden" name="action" value="verify">
        <label class="block text-sm text-gray-700">Enter Code</label>
        <input
          type="text" name="otp" inputmode="numeric" pattern="[0-9]{6}"
          placeholder="6-digit code" maxlength="6" required
          class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-400 focus:outline-none tracking-widest text-center text-lg"
        />
        <button type="submit"
          class="w-full bg-gradient-to-r from-red-500 to-pink-500 text-white py-2.5 rounded-lg hover:opacity-90 font-semibold">
          Verify
        </button>
      </form>

      <form method="post" class="mt-4 flex items-center justify-between text-sm text-gray-600">
        <input type="hidden" name="action" value="resend">
        <button class="text-red-600 hover:underline" type="submit">Resend code</button>
        <a class="hover:underline" href="login.php">Back to login</a>
      </form>
    </div>

    <div class="hidden md:block">
      <!-- optional right-side banner if you want symmetry like login -->
    </div>
  </div>

</body>
</html>
