<?php
session_start();
if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
$u = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 p-8">
  <div class="max-w-md mx-auto bg-white p-6 rounded-2xl shadow">
    <h1 class="text-xl font-semibold text-slate-800 mb-4">Profile</h1>
    <p><strong>Name:</strong> <?= htmlspecialchars($u['name']); ?></p>
    <p><strong>Role:</strong> <?= htmlspecialchars($u['role']); ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($u['email'] ?? ''); ?></p>
  </div>
</body>
</html>
