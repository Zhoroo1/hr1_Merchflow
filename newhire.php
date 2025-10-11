<?php
session_start();
require_once __DIR__ . '/includes/db.php'; // must set $pdo (PDO)

function bad_exit(string $msg) {
  http_response_code(400);
  echo "<h2 style='font-family:sans-serif;color:#b91c1c'>".$msg."</h2>";
  exit;
}

$token = $_GET['t'] ?? '';
if ($token === '') bad_exit('Invalid or missing link.');

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// validate token
$st = $pdo->prepare("SELECT id, name, email, onboarding_token_expires
                     FROM applicants
                     WHERE onboarding_token=?");
$st->execute([$token]);
$app = $st->fetch(PDO::FETCH_ASSOC);
if (!$app) bad_exit('Link not found.');
if ($app['onboarding_token_expires'] && strtotime($app['onboarding_token_expires']) < time()) {
  bad_exit('This link has expired. Please contact HR to request a new link.');
}

$APP_ID = (int)$app['id'];

// handle upload (UNCHANGED)
$fields = [
  'gov_id1'   => 'Gov ID #1 (Front/Back)',
  'gov_id2'   => 'Gov ID #2 (optional)',
  'sss'       => 'SSS',
  'pagibig'   => 'Pag-IBIG',
  'philhealth'=> 'PhilHealth',
  'tin'       => 'TIN',
  'nbi'       => 'NBI / Police Clearance',
  'photo2x2'  => '2x2 Photo',
  'diploma'   => 'Diploma / COE / TOR (optional)',
];

$errs = [];
$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // max 5 MB per file
  $maxBytes = 5 * 1024 * 1024;
  $destDir  = __DIR__ . "/uploads/newhire/" . $APP_ID;
  if (!is_dir($destDir)) @mkdir($destDir, 0775, true);

  foreach ($fields as $key => $label) {
    if (!isset($_FILES[$key]) || $_FILES[$key]['error'] === UPLOAD_ERR_NO_FILE) continue;

    $f = $_FILES[$key];
    if ($f['error'] !== UPLOAD_ERR_OK) { $errs[]="$label: upload error."; continue; }
    if ($f['size'] > $maxBytes)       { $errs[]="$label: file too large (max 5MB)."; continue; }

    // allow jpg/png/pdf
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','pdf'])) { $errs[]="$label: only JPG/PNG/PDF allowed."; continue; }

    $safeName = $key . "_" . date('Ymd_His') . "." . $ext;
    $absPath  = $destDir . "/" . $safeName;
    $relPath  = "uploads/newhire/{$APP_ID}/{$safeName}";

    if (!move_uploaded_file($f['tmp_name'], $absPath)) {
      $errs[]="$label: failed to save file.";
      continue;
    }

    // record (upsert)
    $ins = $pdo->prepare("
      INSERT INTO newhire_uploads (applicant_id, file_key, file_path)
      VALUES (?,?,?)
      ON DUPLICATE KEY UPDATE file_path=VALUES(file_path), uploaded_at=NOW()
    ");
    $ins->execute([$APP_ID, $key, $relPath]);

    $saved = true;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>O!Save Careers – New Hire</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Hero background + overlay for readability (use your header image) */
    .hero{
      background-image: url('assets/logo1.jpg');
      background-size: cover; background-position: center; background-repeat: no-repeat;
      position: relative;
    }
    .hero::before{
      content:""; position:absolute; inset:0;
      background: linear-gradient(180deg, rgba(225,29,72,.76), rgba(225,29,72,.55));
    }
    .hero > .inner{ position: relative; }
    /* nicer file button */
    input[type="file"]::file-selector-button{
      background-color:#e11d48; color:#fff; border:none;
      padding:.5rem .75rem; border-radius:.625rem; margin-right:.75rem; cursor:pointer;
      font-size:.875rem;
    }
    input[type="file"]::file-selector-button:hover{ background-color:#be123c; }
  </style>
</head>
<body class="bg-slate-50 text-slate-800">

  <!-- HERO -->
  <header class="hero text-white">
    <div class="inner max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="inline-flex items-center gap-3 bg-white/15 rounded-2xl px-4 py-2 ring-1 ring-white/20">
        <div class="w-9 h-9 rounded-xl bg-white text-rose-600 grid place-items-center">
          <!-- simple icon block -->
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M14.59 2.59a2 2 0 0 1 2.82 0l3.99 3.99a2 2 0 0 1 0 2.82l-9.9 9.9A2 2 0 0 1 10 20H6a2 2 0 0 1-2-2v-4c0-.53.21-1.04.59-1.41l9.9-9.9Z"/><path d="M15 6 6 15v3h3L18 9l-3-3Z" fill="#fff"/></svg>
        </div>
        <div class="leading-tight">
          <div class="text-2xl sm:text-3xl font-extrabold">O!Save Careers – New Hire</div>
          <div class="text-sm opacity-90">Submit your government IDs and standard requirements below.</div>
        </div>
      </div>
    </div>
  </header>

  <!-- MAIN -->
  <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm">
      <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
        <h4 class="text-lg font-semibold">New Hire — Requirements</h4>
        <div class="text-xs text-slate-500">Max 5MB per file • JPG/PNG/PDF</div>
      </div>

      <div class="p-5 space-y-5">
        <!-- greeting + expiry -->
        <div class="text-sm text-slate-600">
          Hi <b><?= htmlspecialchars($app['name'] ?? 'New Hire') ?></b>. Your secure link expires on
          <b><?= htmlspecialchars(date('M d, Y', strtotime($app['onboarding_token_expires'] ?? '+0 day'))) ?></b>.
        </div>

        <?php if ($saved): ?>
          <div class="rounded-lg bg-green-50 text-green-700 px-4 py-3 ring-1 ring-green-200">
            Files uploaded successfully. You can add more if needed.
          </div>
        <?php endif; ?>

        <?php if ($errs): ?>
          <div class="rounded-lg bg-rose-50 text-rose-700 px-4 py-3 ring-1 ring-rose-200">
            <ul class="list-disc ml-5">
              <?php foreach ($errs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- FORM (posts to same page; keeps your PHP logic) -->
        <form method="post" enctype="multipart/form-data" class="space-y-6">
          <!-- Top two inputs to match screenshot layout (Applicant ID shown but read-only) -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Applicant ID <span class="text-rose-600">*</span></label>
              <input value="<?= htmlspecialchars((string)$APP_ID) ?>" readonly
                     class="block w-full rounded-xl border border-slate-300 px-3 py-2 bg-slate-50 text-slate-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Email (for verification)</label>
              <input value="<?= htmlspecialchars($app['email'] ?? '') ?>" readonly
                     class="block w-full rounded-xl border border-slate-300 px-3 py-2 bg-slate-50 text-slate-500">
            </div>
          </div>

          <!-- Files grid -->
          <div class="grid md:grid-cols-2 gap-5">
            <?php foreach ($fields as $key => $label): ?>
              <div class="<?= in_array($key, ['diploma']) ? 'md:col-span-2' : '' ?>">
                <label class="block text-sm font-medium text-slate-700 mb-1">
                  <?= htmlspecialchars($label) ?>
                  <?php if (!str_contains($label, 'optional')): ?><span class="text-rose-600">*</span><?php endif; ?>
                </label>
                <input type="file" name="<?= htmlspecialchars($key) ?>" accept=".jpg,.jpeg,.png,.pdf"
                       class="block w-full rounded-xl border border-slate-300 px-3 py-2 bg-white">
              </div>
            <?php endforeach; ?>
          </div>

          <div class="flex flex-wrap items-center justify-between pt-2 gap-3">
            <label class="text-sm text-slate-700">
              <input type="checkbox" required class="mr-2 align-middle rounded border-slate-300">
              I hereby declare that all the files I will submit are accurate and complete.
            </label>
            <button class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2 rounded-xl">
              Submit Documents
            </button>
          </div>
        </form>
      </div>
    </div>

    <p class="text-xs text-slate-500 mt-4">Having trouble? You can reply to the hiring email thread with your files as a fallback.</p>
  </main>

  <!-- FOOTER -->
  <footer class="bg-slate-900 text-white/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-center text-sm">
      © <?= date('Y'); ?> O!Save. All rights reserved.
    </div>
  </footer>
</body>
</html>
