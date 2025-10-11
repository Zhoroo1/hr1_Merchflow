<?php
session_start();
if (!empty($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
$error = $_GET['error'] ?? '';
$msg = '';
if ($error === '1')        $msg = 'Invalid email or password.';
elseif ($error === 'otp')  $msg = 'Failed to send OTP. Please try again.';
elseif ($error === 'need') $msg = 'Please enter the OTP we sent to your email.';
elseif ($error === 'server') $msg = 'Server error. Please try again later.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>HR1 MerchFlow | Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-r from-pink-500 via-red-400 to-blue-500">
  <div class="bg-white rounded-2xl shadow-xl flex flex-col md:flex-row overflow-hidden w-[950px] h-[560px]">

    <!-- Left panel (Login Form) -->
    <div class="flex-1 flex items-center justify-center p-10">
      <form action="login_handler.php" method="POST" class="w-full max-w-sm">

        <div class="text-center mb-6">
          <div class="mx-auto mb-4 w-[84px] h-[84px] rounded-lg overflow-hidden bg-white shadow-[0_12px_28px_rgba(59,130,246,0.22)] ring-1 ring-slate-300/60">
            <img src="assets/logo2.jpg" alt="O!Save Logo" class="w-full h-full object-cover" />
          </div>
          <div class="text-gray-500">Login to your account</div>
        </div>

        <?php if ($msg): ?>
          <p class="bg-red-100 text-red-700 p-2 rounded mb-3 text-sm text-center"><?= htmlspecialchars($msg) ?></p>
        <?php endif; ?>

        <div class="mb-4">
          <label class="block text-sm mb-1">Email Address</label>
          <input type="email" name="email" required
                 class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-400 focus:outline-none">
        </div>

        <div class="mb-6">
          <label class="block text-sm mb-1">Password</label>
          <input type="password" name="password" required
                 class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-400 focus:outline-none">
        </div>

        <button type="submit"
                class="w-full bg-gradient-to-r from-red-500 to-pink-500 text-white py-2 rounded-lg hover:opacity-90 font-semibold">
          Login
        </button>

      </form>
    </div>

    <!-- Right panel -->
    <div class="flex-1 relative hidden md:flex items-center justify-center">
      <img src="assets/Osave.png" alt="O!Save" class="object-cover w-full h-full">
      <div class="absolute bottom-6 right-6 bg-red-600 text-white px-5 py-2 rounded-lg font-bold shadow-md">
        Visit O!Save
      </div>
    </div>
  </div>
</body>
</html>
