<?php
require_once __DIR__.'/includes/auth.php';   // uses role_is()

// If the logged-in user is an employee, push them to the real portal.
if (role_is('employee')) {
  header('Location: employee_home.php');
  exit;
}


// Only employees (or store managers if you want) can view the employee portal
require_role(['employee']);  // change to ['employee','store manager'] if needed

/* =======================================================
   Employee Portal (Self-Service) — RBAC-gated widgets
   ======================================================= */

// Prevent duplicate session_start warnings
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$u = $_SESSION['user'];


require_once __DIR__ . '/includes/db.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
date_default_timezone_set('Asia/Manila');
try { $pdo->exec("SET time_zone = '+08:00'"); } catch (Throwable $e) {}

/* ---------- BRAND ---------- */
$brandName = 'Nextgenmms';
$brandLogo = 'assets/logo2.jpg';

/* ---------- LIGHTWEIGHT SCHEMA HELPERS (safe on mixed schemas) ---------- */
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

/* ---------- RBAC (role->permissions) ---------- */
function user_permissions(PDO $pdo): array {
  static $cache = null; if ($cache !== null) return $cache;
  $uid = $_SESSION['user']['id'] ?? 0; if (!$uid) return $cache = [];
  // expects users.role_id -> role_permissions -> permissions
  $sql = "
    SELECT p.code
    FROM users u
    JOIN roles r ON r.id = u.role_id
    JOIN role_permissions rp ON rp.role_id = r.id
    JOIN permissions p ON p.id = rp.permission_id
    WHERE u.id = ?
  ";
  try {
    $st = $pdo->prepare($sql); $st->execute([$uid]);
    $cache = array_column($st->fetchAll(PDO::FETCH_ASSOC),'code');
  } catch (Throwable $e) { $cache = []; }
  return $cache;
}
function can_do(PDO $pdo, string $perm): bool {
  return in_array($perm, user_permissions($pdo), true);
}

/* ---------- SAFE NAME DETECTION (for performance rows) ---------- */
function name_expr(PDO $pdo, string $t): string {
  $single = pick_col($pdo,$t,['fullname','full_name','name','employee_name']);
  if ($single) return "`$single`";
  $first = pick_col($pdo,$t,['firstname','first_name','given_name']);
  $last  = pick_col($pdo,$t,['lastname','last_name','surname']);
  if ($first && $last) return "TRIM(CONCAT(`$first`,' ',`$last`))";
  return "''";
}

/* ---------- Handle POST actions (toggle reminders, submit feedback) ---------- */
$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $act = $_POST['act'] ?? '';

  if ($act==='toggle_reminders' && can_do($pdo,'employee.receive.reminders')) {
    // best-effort: use users.reminders_enabled if exists; else create a tiny table
    $enabled = isset($_POST['enabled']) && $_POST['enabled']==='1' ? 1 : 0;

    if (col_exists($pdo,'users','reminders_enabled')) {
      $st = $pdo->prepare("UPDATE users SET reminders_enabled=? WHERE id=?");
      $st->execute([$enabled, (int)($u['id'] ?? 0)]);
    } else {
      // create minimal table if not present (harmless)
      if (!tbl_exists($pdo,'reminder_subscriptions')) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS reminder_subscriptions (
          user_id INT NOT NULL PRIMARY KEY,
          enabled TINYINT(1) NOT NULL DEFAULT 0,
          updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
      }
      $st = $pdo->prepare("INSERT INTO reminder_subscriptions (user_id,enabled) VALUES (?,?)
                           ON DUPLICATE KEY UPDATE enabled=VALUES(enabled)");
      $st->execute([(int)($u['id'] ?? 0), $enabled]);
    }
    $msg = $enabled ? 'Reminders enabled.' : 'Reminders disabled.';
  }

  if ($act==='submit_feedback' && can_do($pdo,'employee.submit.feedback')) {
    $fb = trim((string)($_POST['feedback'] ?? ''));
    if ($fb !== '') {
      if (!tbl_exists($pdo,'recognition_feedback')) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS recognition_feedback (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          user_id INT NOT NULL,
          feedback TEXT NOT NULL,
          created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
      }
      $st = $pdo->prepare("INSERT INTO recognition_feedback (user_id, feedback) VALUES (?,?)");
      $st->execute([(int)($u['id'] ?? 0), $fb]);
      $msg = 'Thank you! Your feedback was submitted.';
    } else { $msg = 'Please write some feedback.'; }
  }
}

/* ---------- Data queries ---------- */
/* 1) Announcements / recognition schedule */
$ann = [];
if (can_do($pdo,'employee.view.announcements')) {
  if (tbl_exists($pdo,'recognitions')) {
    $dateCol = pick_col($pdo,'recognitions',['date','created_at','event_date']);
    $title   = pick_col($pdo,'recognitions',['title','name','activity']);
    $desc    = pick_col($pdo,'recognitions',['description','details','note']);
    $ann = [];
    if ($dateCol && $title) {
      $sql = "SELECT `$dateCol` AS dt, `$title` AS title, ".($desc?"`$desc`":"NULL")." AS descr
              FROM recognitions ORDER BY `$dateCol` DESC LIMIT 8";
      try { $ann = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: []; } catch(Throwable $e){}
    }
  }
}

/* 2) Current reminders state */
$remEnabled = false;
if (can_do($pdo,'employee.receive.reminders')) {
  if (col_exists($pdo,'users','reminders_enabled')) {
    try {
      $st = $pdo->prepare("SELECT reminders_enabled FROM users WHERE id=?");
      $st->execute([(int)($u['id'] ?? 0)]);
      $remEnabled = (bool)$st->fetchColumn();
    } catch(Throwable $e){ $remEnabled=false; }
  } elseif (tbl_exists($pdo,'reminder_subscriptions')) {
    $st = $pdo->prepare("SELECT enabled FROM reminder_subscriptions WHERE user_id=?");
    $st->execute([(int)($u['id'] ?? 0)]);
    $remEnabled = (bool)$st->fetchColumn();
  }
}

/* 3) My Performance history (light, flexible) */
$perf = [];
if (can_do($pdo,'employee.view.performance')) {
  if (tbl_exists($pdo,'evaluations')) {
    // try to map by users.id -> evaluations.employee_id OR by email
    $empKey = pick_col($pdo,'evaluations',['employee_id','emp_id','user_id']);
    $scoreC = pick_col($pdo,'evaluations',['score','rating']);
    $perC   = pick_col($pdo,'evaluations',['period','type']);
    $dueC   = pick_col($pdo,'evaluations',['due_date','review_date','next_review_at']);
    $statC  = pick_col($pdo,'evaluations',['status','state','stage']);
    $name   = '';
    // try to join employees for a name
    if (tbl_exists($pdo,'employees')) {
      $nm = name_expr($pdo,'employees');
      $name = ", $nm AS emp_name";
      // resolve employee id by user link if present
      $empId = null;
      if (col_exists($pdo,'employees','user_id')) {
        $q=$pdo->prepare("SELECT id FROM employees WHERE user_id=? LIMIT 1");
        $q->execute([(int)($u['id'] ?? 0)]); $empId = (int)$q->fetchColumn();
      }
      $where = "1=1";
      if ($empId && $empKey) $where = "ev.`$empKey` = ".(int)$empId;
      $sql = "SELECT ".($perC?"ev.`$perC` AS period":"NULL AS period").",
                     ".($scoreC?"ev.`$scoreC` AS score":"NULL AS score").",
                     ".($statC?"ev.`$statC` AS status":"NULL AS status").",
                     ".($dueC?"ev.`$dueC` AS dt":"NULL AS dt")."
                     $name
              FROM evaluations ev
              ".($empKey?"":"")."
              WHERE $where
              ORDER BY COALESCE(ev.`$dueC`, ev.id) DESC
              LIMIT 10";
    } else {
      $sql = "SELECT ".($perC?"`$perC` AS period":"NULL AS period").",
                     ".($scoreC?"`$scoreC` AS score":"NULL AS score").",
                     ".($statC?"`$statC` AS status":"NULL AS status").",
                     ".($dueC?"`$dueC` AS dt":"NULL AS dt")."
              FROM evaluations
              ORDER BY COALESCE(`$dueC`, id) DESC
              LIMIT 10";
    }
    try { $perf = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: []; } catch(Throwable $e){}
  }
}

/* ---------- UI helper ---------- */
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
  #sidebar{width:16rem} #sidebar.collapsed{width:4rem}
  #sidebar .nav-item{padding:.6rem .85rem}
  #sidebar.collapsed .nav-item{justify-content:center;padding:.6rem 0}
  #sidebar.collapsed .item-label,#sidebar.collapsed .section-title{display:none}
  #contentWrap{padding-left:16rem;transition:padding .25s ease}
  #contentWrap.collapsed{padding-left:4rem}
  #sidebar{scrollbar-width:none;-ms-overflow-style:none} #sidebar::-webkit-scrollbar{display:none}
  .iconbtn{width:30px;height:30px;display:grid;place-items:center;border-radius:.5rem;border:1px solid #e5e7eb;color:#475569}
  .iconbtn:hover{background:#f8fafc}
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
      <div class="ml-1 flex items-center gap-2 px-2.5 py-1.5 rounded-xl bg-white ring-1 ring-slate-200 shadow">
        <div class="w-8 h-8 rounded-md bg-rose-500 text-white grid place-items-center text-xs font-semibold"><?= strtoupper(substr($u['name'] ?? 'U',0,2)); ?></div>
        <div class="leading-tight pr-1">
          <div class="text-sm font-medium text-slate-800 truncate max-w-[120px]"><?= htmlspecialchars($u['name'] ?? 'User'); ?></div>
          <div class="text-[11px] text-slate-500 capitalize"><?= htmlspecialchars($u['role'] ?? ''); ?></div>
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
      <div class="w-10 h-10 overflow-hidden rounded-md">
        <img src="<?= $brandLogo ?>" class="w-full h-full object-cover" alt="Logo">
      </div>
      <span class="item-label font-semibold text-white"><?= htmlspecialchars($brandName) ?></span>
    </div>
    <nav class="py-4">
  <div class="px-4 text-[11px] tracking-wider text-slate-400/80 section-title">MAIN</div>

  <!-- Dashboard: employee → employee_home.php, else → index.php -->
  <a href="<?= role_is('employee') ? 'employee_home.php' : 'index.php' ?>"
     class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive(role_is('employee') ? 'employee_home.php' : 'index.php'); ?>">
    <i class="fa-solid fa-house"></i><span class="item-label font-medium">Dashboard</span>
  </a>

  <?php if (role_is('employee')): ?>
    <!-- EMPLOYEE SELF-SERVICE ONLY -->
    <a href="profile.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('profile.php'); ?>">
      <i class="fa-regular fa-user"></i><span class="item-label">My Profile</span>
    </a>
    <a href="my_evaluations.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('my_evaluations.php'); ?>">
      <i class="fa-solid fa-chart-line"></i><span class="item-label">My Evaluations</span>
    </a>
    <a href="my_recognition.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('my_recognition.php'); ?>">
      <i class="fa-solid fa-award"></i><span class="item-label">Recognitions</span>
    </a>

    <div class="px-4 mt-4 text-[11px] tracking-wider text-slate-400/80 section-title">ACCOUNT</div>
    <a href="logout.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2">
      <i class="fa-solid fa-arrow-right-from-bracket"></i><span class="item-label">Log out</span>
    </a>

  <?php else: ?>
    <!-- MANAGEMENT MODULES FOR ADMIN/HR ONLY -->
    <?php if (role_is(['admin','hr manager','recruiter','superadmin'])): ?>
      <a href="applicants.php"  class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('applicants.php'); ?>">
        <i class="fa-solid fa-user"></i><span class="item-label">Applicants</span>
      </a>
      <a href="recruitment.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('recruitment.php'); ?>">
        <i class="fa-solid fa-briefcase"></i><span class="item-label">Recruitment</span>
      </a>
      <a href="onboarding.php"  class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('onboarding.php'); ?>">
        <i class="fa-solid fa-square-check"></i><span class="item-label">Onboarding</span>
      </a>
    <?php endif; ?>

    <div class="px-4 mt-4 text-[11px] tracking-wider text-slate-400/80 section-title">MANAGEMENT</div>
    <?php if (role_is(['admin','hr manager','superadmin'])): ?>
      <a href="employees.php"   class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('employees.php'); ?>">
        <i class="fa-solid fa-users"></i><span class="item-label">Employees</span>
      </a>
      <a href="evaluations.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('evaluations.php'); ?>">
        <i class="fa-solid fa-chart-line"></i><span class="item-label">Evaluations</span>
      </a>
      <a href="recognition.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('recognition.php'); ?>">
        <i class="fa-solid fa-award"></i><span class="item-label">Recognition</span>
      </a>
    <?php endif; ?>

    <?php if (role_is(['admin','superadmin'])): ?>
      <a href="users.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('users.php'); ?>">
        <i class="fa-solid fa-user-gear"></i><span class="item-label">Users</span>
      </a>
    <?php endif; ?>
  <?php endif; ?>
</nav>

  </aside>

  <!-- CONTENT -->
  <main id="contentWrap" class="min-h-[calc(100vh-56px)] transition-all duration-200">
    <div class="px-8 py-8">
      <?php if ($msg): ?>
        <div class="mb-4 rounded-xl bg-emerald-50 text-emerald-700 px-4 py-3 ring-1 ring-emerald-200"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Announcements / Schedule -->
        <div class="lg:col-span-2 rounded-2xl bg-white ring-1 ring-slate-200">
          <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">Recognition Announcements</h2>
          </div>
          <div class="divide-y divide-slate-200">
            <?php if (can_do($pdo,'employee.view.announcements') && $ann): foreach ($ann as $a): ?>
              <div class="px-4 py-3">
                <div class="flex items-center justify-between">
                  <div class="min-w-0">
                    <div class="font-medium text-slate-800 truncate"><?= htmlspecialchars($a['title'] ?? '—') ?></div>
                    <div class="text-xs text-slate-500 truncate"><?= htmlspecialchars($a['descr'] ?? '') ?></div>
                  </div>
                  <div class="text-xs text-rose-600 font-medium shrink-0">
                    <?= !empty($a['dt']) ? date('M d, Y', strtotime($a['dt'])) : '' ?>
                  </div>
                </div>
              </div>
            <?php endforeach; else: ?>
              <div class="px-4 py-8 text-center text-slate-500 text-sm">No announcements to show.</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Reminders toggle + Feedback -->
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4">
          <?php if (can_do($pdo,'employee.receive.reminders')): ?>
            <form method="post" class="mb-5">
              <input type="hidden" name="act" value="toggle_reminders">
              <div class="flex items-center justify-between">
                <div>
                  <div class="font-semibold text-slate-800">Reminders</div>
                  <div class="text-xs text-slate-500">Monthly recognition activities</div>
                </div>
                <label class="inline-flex items-center cursor-pointer">
                  <input type="checkbox" class="sr-only peer" name="enabled" value="1" <?= $remEnabled?'checked':''; ?>
                         onchange="this.form.submit()">
                  <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:bg-rose-500 transition relative">
                    <span class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full peer-checked:translate-x-5 transition"></span>
                  </div>
                </label>
              </div>
            </form>
          <?php endif; ?>

          <?php if (can_do($pdo,'employee.submit.feedback')): ?>
            <div class="mt-2">
              <div class="font-semibold text-slate-800 mb-2">Send Feedback</div>
              <form method="post" class="space-y-2">
                <input type="hidden" name="act" value="submit_feedback">
                <textarea name="feedback" rows="4" class="w-full px-3 py-2 rounded-lg border border-slate-200" placeholder="Your feedback…"></textarea>
                <div class="text-right">
                  <button class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg">Submit</button>
                </div>
              </form>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- My Performance History -->
      <div class="rounded-2xl bg-white ring-1 ring-slate-200 mt-6">
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
          <h2 class="font-semibold text-slate-800">My Performance History</h2>
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
            <?php if (can_do($pdo,'employee.view.performance') && $perf): foreach ($perf as $p): ?>
              <tr class="border-t">
                <td class="px-4 py-3"><?= htmlspecialchars($p['period'] ?? '—') ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars(ucfirst(strtolower($p['status'] ?? ''))) ?></td>
                <td class="px-4 py-3 text-right"><?= isset($p['score']) ? htmlspecialchars((string)$p['score']) : '—' ?></td>
                <td class="px-4 py-3"><?= !empty($p['dt']) ? date('M d, Y', strtotime($p['dt'])) : '—' ?></td>
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
  // Sidebar toggle persist (same behavior as other pages)
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
  btn?.addEventListener('click',()=>{ sb.classList.toggle('collapsed'); localStorage.setItem('sb-collapsed', sb.classList.contains('collapsed')?'1':'0'); applyShift(); });
  if(localStorage.getItem('sb-collapsed')==='1'){ sb.classList.add('collapsed'); }
  applyShift();
</script>
</body>
</html>
