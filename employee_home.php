<?php
require_once __DIR__.'/includes/rbac.php';
// Only employees (or store managers if you want) can view the employee portal
require_role(['employee']);  // change to ['employee','store manager'] if needed

/* =======================================================================
   Employee Portal — same UI + palette as your system (rose/Slate)
   ======================================================================= */
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
$u = $_SESSION['user'];

/* Optional guard (safe to keep even if walang role field) */
if (isset($u['role']) && !preg_match('~^employee$~i', (string)$u['role'])) {
  // Non-employee? send back to admin dashboard.
  header('Location: index.php'); exit;
}

require_once __DIR__ . '/includes/db.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
date_default_timezone_set('Asia/Manila');
try { $pdo->exec("SET time_zone = '+08:00'"); } catch (Throwable $e) {}

/* ---------- BRAND ---------- */
$brandName = 'Nextgenmms';
$brandLogo = 'assets/logo2.jpg';

/* ---------- Tiny helpers (schema-flexible, read-only) ---------- */
function tbl_exists(PDO $pdo, string $t): bool {
  try { return (bool)$pdo->query("SHOW TABLES LIKE ".$pdo->quote($t))->fetch(); }
  catch(Throwable $e){ return false; }
}
function col_exists(PDO $pdo, string $t, string $c): bool {
  if (!tbl_exists($pdo,$t)) return false;
  try { return (bool)$pdo->query("SHOW COLUMNS FROM `$t` LIKE ".$pdo->quote($c))->fetch(); }
  catch(Throwable $e){ return false; }
}
function pick_col(PDO $pdo, string $t, array $cands): ?string {
  foreach ($cands as $c) if (col_exists($pdo,$t,$c)) return $c; return null;
}

/* ---------- Handle settings toggle (POST) ---------- */
if (($_POST['act'] ?? '') === 'toggle_reminders') {
  $enable = isset($_POST['enabled']) ? 1 : 0;
  $uid = (int)($u['id'] ?? 0);

  if ($uid) {
    if (col_exists($pdo,'users','reminders_enabled')) {
      $st = $pdo->prepare("UPDATE users SET reminders_enabled=? WHERE id=?");
      $st->execute([$enable, $uid]);
    } elseif (tbl_exists($pdo,'reminder_subscriptions')) {
      // upsert
      $st = $pdo->prepare("INSERT INTO reminder_subscriptions (user_id,enabled)
                           VALUES (?,?)
                           ON DUPLICATE KEY UPDATE enabled=VALUES(enabled)");
      $st->execute([$uid,$enable]);
    }
  }
  // prevent form resubmission
  header('Location: '.$_SERVER['REQUEST_URI']); exit;
}

/* ---------- Data (employee-centric) ---------- */
$ann = []; // recognitions/announcements
if (tbl_exists($pdo,'recognitions')) {
  $dc = pick_col($pdo,'recognitions',['date','event_date','created_at']);
  $tc = pick_col($pdo,'recognitions',['title','name','activity']);
  $sc = pick_col($pdo,'recognitions',['description','details','note']);
  if ($dc && $tc) {
    $ann = $pdo->query("
      SELECT `$dc` AS dt, `$tc` AS title, ".($sc?"`$sc`":"NULL")." AS descr
      FROM recognitions ORDER BY `$dc` DESC LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
}

$myReviews = [];
if (tbl_exists($pdo,'evaluations')) {
  $scoreC = pick_col($pdo,'evaluations',['score','rating']);
  $perC   = pick_col($pdo,'evaluations',['period','type']);
  $dueC   = pick_col($pdo,'evaluations',['due_date','review_date','next_review_at','created_at']);
  $statC  = pick_col($pdo,'evaluations',['status','state','stage']);
  $empKey = pick_col($pdo,'evaluations',['employee_id','emp_id','user_id']);
  $where  = "1=1";
  if ($empKey === 'user_id') {
    $where = "ev.`user_id` = ".(int)($u['id'] ?? 0);
  } elseif ($empKey && tbl_exists($pdo,'employees') && col_exists($pdo,'employees','user_id')) {
    $st=$pdo->prepare("SELECT id FROM employees WHERE user_id=? LIMIT 1");
    $st->execute([(int)($u['id']??0)]);
    if ($eid = (int)$st->fetchColumn()) $where = "ev.`$empKey` = ".$eid;
  }
  $sql = "SELECT ".($perC?"ev.`$perC` AS p":"NULL AS p").",
                 ".($statC?"ev.`$statC` AS s":"NULL AS s").",
                 ".($scoreC?"ev.`$scoreC` AS sc":"NULL AS sc").",
                 ".($dueC?"ev.`$dueC` AS dt":"NULL AS dt")."
          FROM evaluations ev WHERE $where
          ORDER BY COALESCE(ev.`$dueC`, ev.id) DESC LIMIT 10";
  try { $myReviews = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: []; } catch(Throwable $e){}
}

/* Optional: simple setting pulled from users/reminder_subscriptions */
$remEnabled=false;
if (col_exists($pdo,'users','reminders_enabled')) {
  $st=$pdo->prepare("SELECT reminders_enabled FROM users WHERE id=?");
  $st->execute([(int)($u['id']??0)]); $remEnabled=(bool)$st->fetchColumn();
} elseif (tbl_exists($pdo,'reminder_subscriptions')) {
  $st=$pdo->prepare("SELECT enabled FROM reminder_subscriptions WHERE user_id=?");
  $st->execute([(int)($u['id']??0)]); $remEnabled=(bool)$st->fetchColumn();
}

/* ---------- UI util ---------- */
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
  <title>Employee | <?= htmlspecialchars($brandName) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    /* SAME SHELL as your system */
    #sidebar{width:16rem} #sidebar.collapsed{width:4rem}
    #sidebar .nav-item{padding:.6rem .85rem}
    #sidebar.collapsed .nav-item{justify-content:center;padding:.6rem 0}
    #sidebar.collapsed .item-label, #sidebar.collapsed .section-title{display:none}
    #contentWrap{padding-left:16rem;transition:padding .25s ease}
    #contentWrap.collapsed{padding-left:4rem}
    #sidebar{scrollbar-width:none;-ms-overflow-style:none} #sidebar::-webkit-scrollbar{display:none}
    .chip { display:flex; align-items:center; gap:.5rem; padding:.35rem .55rem; border-radius:.8rem; box-shadow:0 0 0 1px rgba(226,232,240,1); background:#fff; }
    .chip .avatar{width:32px;height:32px;border-radius:.6rem;display:grid;place-items:center;background:#e11d48;color:#fff;font-weight:700}
    .menu{position:absolute; right:0; top:110%; min-width:180px; background:#fff; border-radius:.8rem; box-shadow:0 10px 30px rgba(15,23,42,.12); border:1px solid rgba(226,232,240,1); display:none}
    .menu a{display:block; padding:.6rem .9rem; font-size:.9rem; color:#0f172a}
    .menu a:hover{background:#f8fafc}
  </style>
</head>
<body class="bg-slate-50">

<!-- TOP BAR -->
<header class="sticky top-0 z-40">
  <div id="topbarPad" class="ml-64 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="h-14 px-3 md:px-4 flex items-center gap-3">
      <button id="btnSidebar" class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-rose-500 text-white hover:bg-rose-600 shrink-0">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="flex-1 min-w-[220px]">
        <div class="relative max-w-2xl">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <input type="text" placeholder="Search…"
                 class="w-full pl-9 pr-3 py-2.5 rounded-xl bg-white border border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-400 placeholder:text-slate-400">
        </div>
      </div>
      <div class="relative">
        <button id="userChip" class="chip">
          <div class="avatar"><?= strtoupper(substr((string)($u['name'] ?? 'U'),0,2)); ?></div>
          <div class="text-left leading-tight hidden sm:block">
            <div class="text-sm font-semibold text-slate-900 truncate max-w-[150px]"><?= htmlspecialchars($u['name'] ?? 'User') ?></div>
            <div class="text-[11px] text-slate-500">Employee</div>
          </div>
          <i class="fa-solid fa-chevron-down text-slate-500 text-xs"></i>
        </button>
        <div id="userMenu" class="menu">
          <a href="profile.php"><i class="fa-regular fa-user mr-2"></i> View profile</a>
          <a href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Log out</a>
        </div>
      </div>
    </div>
  </div>
</header>

<div class="relative">
  <!-- SIDEBAR -->
  <aside id="sidebar" class="fixed top-0 bottom-0 left-0 text-slate-100 overflow-y-auto transition-all duration-200"
         style="background:linear-gradient(to bottom,#121214 0%,#121214 70%,#e11d48 100%)">
    <div class="h-14 bg-rose-600 flex items-center justify-center gap-2">
      <div class="w-10 h-10 overflow-hidden rounded-md bg-white grid place-items-center">
        <img src="<?= htmlspecialchars($brandLogo) ?>" class="w-full h-full object-cover" alt="logo">
      </div>
      <span class="item-label font-semibold text-white"><?= htmlspecialchars($brandName) ?></span>
    </div>
    <nav class="py-4">
      <div class="px-4 text-[11px] tracking-wider text-slate-400/80 section-title">MAIN</div>
      <a href="employee.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('employee.php'); ?>">
        <i class="fa-solid fa-house"></i><span class="item-label font-medium">Dashboard</span>
      </a>
      <a href="profile.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2">
        <i class="fa-regular fa-user"></i><span class="item-label">My Profile</span>
      </a>
      <a href="my_evaluations.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2">
        <i class="fa-solid fa-chart-line"></i><span class="item-label">My Evaluations</span>
      </a>
      <a href="my_recognition.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2">
        <i class="fa-solid fa-award"></i><span class="item-label">Recognitions</span>
      </a>
      <div class="px-4 mt-4 text-[11px] tracking-wider text-slate-400/80 section-title">ACCOUNT</div>
      <a href="logout.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2">
        <i class="fa-solid fa-arrow-right-from-bracket"></i><span class="item-label">Log out</span>
      </a>
    </nav>
  </aside>

  <!-- CONTENT -->
  <main id="contentWrap" class="min-h-[calc(100vh-56px)] transition-all duration-200">
    <div class="px-4 sm:px-6 lg:px-8 py-6">
      <!-- KPI tiles -->
      <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 md:gap-4 mb-6 text-center">
        <?php
          $kpis = [
            ['icon'=>'fa-bullhorn','label'=>'Announcements','value'=>count($ann)],
            ['icon'=>'fa-bell','label'=>'Reminders','value'=>$remEnabled?1:0],
            ['icon'=>'fa-chart-line','label'=>'My Reviews','value'=>count($myReviews)],
          ];
          foreach ($kpis as $k):
        ?>
        <div class="relative rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm p-5 flex flex-col items-center justify-center overflow-hidden min-h-[120px]">
          <div class="absolute inset-0 bg-gradient-to-b from-rose-50/60 to-transparent"></div>
          <div class="w-10 h-10 mb-2 rounded-xl bg-gradient-to-br from-rose-100 to-rose-200 text-rose-600 grid place-items-center shadow-inner">
            <i class="fa-solid <?= $k['icon'] ?> text-base"></i>
          </div>
          <div class="text-[13px] text-slate-700 font-medium"><?= htmlspecialchars($k['label']) ?></div>
          <div class="text-xl font-bold text-slate-900 mt-1 tracking-tight">
            <?= number_format($k['value']) ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- GRID -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Announcements -->
        <div class="xl:col-span-2 rounded-2xl bg-white ring-1 ring-slate-200">
          <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">Company / Recognition Announcements</h2>
          </div>
          <div class="divide-y divide-slate-200">
            <?php if ($ann): foreach ($ann as $a): ?>
              <div class="px-4 py-3 flex items-center justify-between">
                <div class="min-w-0">
                  <div class="font-medium text-slate-800 truncate"><?= htmlspecialchars($a['title'] ?? '—') ?></div>
                  <div class="text-xs text-slate-500 truncate"><?= htmlspecialchars($a['descr'] ?? '') ?></div>
                </div>
                <div class="text-xs text-rose-600 font-medium shrink-0">
                  <?= !empty($a['dt']) ? date('M d, Y', strtotime($a['dt'])) : '' ?>
                </div>
              </div>
            <?php endforeach; else: ?>
              <div class="px-4 py-8 text-center text-slate-500 text-sm">No announcements to show.</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Quick Settings -->
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4">
          <div class="font-semibold text-slate-800 mb-2">My Settings</div>
          <form method="post" class="flex items-center justify-between">
            <input type="hidden" name="act" value="toggle_reminders">
            <div class="text-sm text-slate-600">Monthly reminders</div>
            <label class="inline-flex items-center cursor-pointer">
              <input type="checkbox" class="sr-only peer" name="enabled" value="1" <?= $remEnabled?'checked':''; ?> onchange="this.form.submit()">
              <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:bg-rose-500 transition relative">
                <span class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full peer-checked:translate-x-5 transition"></span>
              </div>
            </label>
          </form>

          <div class="mt-5">
            <a href="profile.php" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white">
              <i class="fa-regular fa-user"></i> View Profile
            </a>
          </div>
        </div>
      </div>

      <!-- My Performance -->
      <div class="rounded-2xl bg-white ring-1 ring-slate-200 mt-6">
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
          <h2 class="font-semibold text-slate-800">My Performance Reviews</h2>
        </div>
        <table class="w-full text-sm table-auto">
          <thead class="bg-slate-100 text-slate-600 uppercase text-[11px]">
            <tr>
              <th class="px-4 py-2 text-left">Period</th>
              <th class="px-4 py-2 text-left">Status</th>
              <th class="px-4 py-2 text-right">Score</th>
              <th class="px-4 py-2 text-left">Date</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($myReviews): foreach ($myReviews as $r): ?>
              <tr class="border-t">
                <td class="px-4 py-3"><?= htmlspecialchars($r['p'] ?? '—') ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars(ucfirst(strtolower($r['s'] ?? ''))) ?></td>
                <td class="px-4 py-3 text-right"><?= isset($r['sc']) ? htmlspecialchars((string)$r['sc']) : '—' ?></td>
                <td class="px-4 py-3"><?= !empty($r['dt']) ? date('M d, Y', strtotime($r['dt'])) : '—' ?></td>
              </tr>
            <?php endforeach; else: ?>
              <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500 text-sm">No performance records.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </main>
</div>

<script>
  // sidebar toggle
  const btn=document.getElementById('btnSidebar');
  const sb=document.getElementById('sidebar');
  const main=document.getElementById('contentWrap');
  const topbarPad=document.getElementById('topbarPad');
  function applyShift(){
    if(sb.classList.contains('collapsed')){
      topbarPad.classList.remove('ml-64'); topbarPad.classList.add('ml-16'); main.classList.add('collapsed');
    } else {
      topbarPad.classList.remove('ml-16'); topbarPad.classList.add('ml-64'); main.classList.remove('collapsed');
    }
  }
  btn?.addEventListener('click',()=>{ sb.classList.toggle('collapsed'); localStorage.setItem('emp-sb-collapsed', sb.classList.contains('collapsed')?'1':'0'); applyShift(); });
  if(localStorage.getItem('emp-sb-collapsed')==='1'){ sb.classList.add('collapsed'); }
  applyShift();

  // profile menu
  const chip = document.getElementById('userChip');
  const menu = document.getElementById('userMenu');
  chip?.addEventListener('click', (e)=>{ e.stopPropagation(); menu.style.display = menu.style.display==='block' ? 'none' : 'block'; });
  document.addEventListener('click', ()=>{ menu.style.display='none'; });
</script>
</body>
</html>
