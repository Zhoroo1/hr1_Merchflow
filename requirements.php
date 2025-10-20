<?php
require_once __DIR__.'/includes/rbac.php';
// Only admin / HR Manager / Recruiter can open admin area
require_role(['admin','hr manager','recruiter']);   // blocks 'employee'

session_start();
if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
$u = $_SESSION['user'];

require_once __DIR__ . '/includes/db.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
date_default_timezone_set('Asia/Manila');

function isActive($page){
  $is = basename($_SERVER['PHP_SELF']) === $page;
  return $is ? 'bg-rose-900/60 text-rose-500'
             : 'text-slate-300 hover:text-rose-500 hover:bg-rose-900/40';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>New Hire Requirements | HR1 Nextgenmms</title>
  <link rel="icon" type="image/png" href="assets/logo3.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
</head>
<body class="bg-slate-50 text-slate-800">
  <div class="flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 min-h-screen p-4 space-y-2">
      <div class="text-center mb-6">
        <img src="assets/logo3.jpg" class="w-24 mx-auto rounded-full mb-2">
        <h2 class="text-rose-500 font-bold text-xl">Nextgenmms</h2>
      </div>
      <nav class="space-y-1 text-sm">
        <a href="dashboard.php" class="block px-3 py-2 rounded-lg <?= isActive('dashboard.php') ?>"><i class="fa fa-home mr-2"></i>Dashboard</a>
        <a href="applicants.php" class="block px-3 py-2 rounded-lg <?= isActive('applicants.php') ?>"><i class="fa fa-user mr-2"></i>Applicants</a>
        <a href="recruitment.php" class="block px-3 py-2 rounded-lg <?= isActive('recruitment.php') ?>"><i class="fa fa-briefcase mr-2"></i>Recruitment</a>
        <a href="requirements.php" class="block px-3 py-2 rounded-lg <?= isActive('requirements.php') ?>"><i class="fa fa-file mr-2"></i>Requirements</a>
        <a href="logout.php" class="block px-3 py-2 rounded-lg text-slate-400 hover:text-rose-500"><i class="fa fa-sign-out-alt mr-2"></i>Logout</a>
      </nav>
    </aside>

    <!-- Content -->
    <main class="flex-1 p-8">
      <h1 class="text-2xl font-semibold text-rose-700 mb-4">New Hire Requirements</h1>
      <p class="text-slate-500 mb-6 text-sm">Track uploaded documents of newly hired employees. Click the file name to download or preview.</p>

      <div class="overflow-x-auto bg-white rounded-2xl shadow ring-1 ring-slate-200">
        <table class="min-w-full text-sm text-left border-collapse">
          <thead class="bg-slate-100 text-slate-600 font-semibold">
            <tr>
              <th class="px-4 py-3">#</th>
              <th class="px-4 py-3">Applicant</th>
              <th class="px-4 py-3">Email</th>
              <th class="px-4 py-3">Document</th>
              <th class="px-4 py-3">File</th>
              <th class="px-4 py-3">Uploaded</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $q = $pdo->query("
            SELECT u.id, a.name AS applicant_name, a.email,
                   u.file_key, u.file_path, u.uploaded_at
            FROM newhire_uploads u
            JOIN applicants a ON a.id = u.applicant_id
            ORDER BY u.uploaded_at DESC
          ");
          $rows = $q->fetchAll(PDO::FETCH_ASSOC);
          if (!$rows) {
            echo '<tr><td colspan="6" class="text-center text-slate-400 py-6">No uploaded files yet.</td></tr>';
          } else {
            $i=1;
            foreach ($rows as $r):
              $fileUrl = htmlspecialchars($r['file_path']);
          ?>
            <tr class="border-b border-slate-100 hover:bg-rose-50/30">
              <td class="px-4 py-2"><?= $i++ ?></td>
              <td class="px-4 py-2 font-medium text-slate-700"><?= htmlspecialchars($r['applicant_name']) ?></td>
              <td class="px-4 py-2 text-slate-500"><?= htmlspecialchars($r['email']) ?></td>
              <td class="px-4 py-2 capitalize"><?= htmlspecialchars(str_replace('_',' ',$r['file_key'])) ?></td>
              <td class="px-4 py-2">
                <a href="<?= $fileUrl ?>" target="_blank" class="text-rose-600 hover:underline">
                  <i class="fa fa-file-alt mr-1"></i><?= basename($fileUrl) ?>
                </a>
              </td>
              <td class="px-4 py-2 text-slate-400"><?= date('M d, Y g:i A', strtotime($r['uploaded_at'])) ?></td>
            </tr>
          <?php endforeach; } ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>
