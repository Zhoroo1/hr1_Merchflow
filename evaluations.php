<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
$u = $_SESSION['user'];

$role = strtolower($u['role'] ?? '');
if (!in_array($role, ['admin','hr manager'], true)) {
    http_response_code(403);
    exit('Forbidden');
}


$brandName = 'Nextgenmms';
$brandLogo = 'assets/logo2.jpg'; // siguraduhin tama ang filename/case

require_once __DIR__ . '/includes/db.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
date_default_timezone_set('Asia/Manila');
try { $pdo->exec("SET time_zone = '+08:00'"); } catch (Throwable $e) {}

/* ---- Always return JSON for API (no HTML notices) ---- */
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

/* -------------------- Helpers -------------------- */
function isActive($page){
  $is = basename($_SERVER['PHP_SELF']) === $page;
  return $is ? 'bg-rose-900/60 text-rose-500'
             : 'text-slate-300 hover:text-rose-500 hover:bg-rose-900/40';
}
function send($d){ header('Content-Type: application/json; charset=utf-8'); echo json_encode($d); exit; }
function bad($m,$c=200){ http_response_code($c); send(['ok'=>false,'error'=>$m]); }
function ok($d=[]){ send(['ok'=>true,'data'=>$d]); }

function colExists(PDO $pdo, $table, $col){
  try{
    $stmt=$pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $stmt->execute([$col]);
    return (bool)$stmt->fetch();
  }catch(Throwable $e){ return false; }
}

function table_exists(PDO $pdo, string $table): bool {
  try {
    $q = $pdo->quote($table);
    return (bool)$pdo->query("SHOW TABLES LIKE $q")->fetch();
  } catch (Throwable $e) {
    return false;
  }
}


function pickName($row){
  return $row['emp_name']
      ?? ($row['full_name']
      ?? ($row['name']
      ?? ($row['employee_name'] ?? '')));
}


/* Humanize period/type labels */
function humanType($period){
  $map = [
    'first-30-days' => 'Initial 30 Day',
    'first-60-days' => 'Initial 60 Day',
    'first-90-days' => 'Initial 90 Day',
    'mid-year'      => 'Mid-Year',
    'annual'        => 'Annual',
  ];
  $k = strtolower(trim((string)$period));
  if (isset($map[$k])) return $map[$k];
  return ucwords(str_replace(['_','-'], [' ',' '], (string)$period));
}

/* Derive display status using due_date when not completed */
function deriveStatus($dbStatus, $dueDate){
  if ($dbStatus === 'completed') return 'Completed';
  $due = $dueDate ? strtotime($dueDate) : null;
  if (!$due) return 'Pending';
  $today = strtotime('today');
  if ($due < $today) return 'Overdue';
  if ($due <= strtotime('+7 days', $today)) return 'Due Soon';
  return 'Pending';
}

/* ==================================================
   Minimal JSON API within same file (self-fetch)
   ================================================== */
  // RBAC for API endpoints
  $__role = strtolower($u['role'] ?? '');
  if (!in_array($__role, ['admin','hr manager'], true)) {
    bad('Forbidden', 403);
  }

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
    && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {

  ob_start();
  try {
    $raw = file_get_contents('php://input');
    $in  = json_decode($raw ?: '[]', true) ?: [];
    $act = strtolower((string)($in['action'] ?? ''));

    /* -------- LIST -------- */
    if ($act === 'list') {
      $term   = trim((string)($in['q'] ?? ''));
      $type   = trim((string)($in['type'] ?? ''));   // human label
      $status = trim((string)($in['status'] ?? ''));

      // Build a robust name expression based on existing columns
$hasFull  = colExists($pdo,'employees','full_name');
$hasName  = colExists($pdo,'employees','name');
$hasFirst = colExists($pdo,'employees','first_name');
$hasLast  = colExists($pdo,'employees','last_name');

if ($hasFull) {
  $nameExpr = "NULLIF(TRIM(e.full_name),'')";
} elseif ($hasName) {
  $nameExpr = "NULLIF(TRIM(e.name),'')";
} elseif ($hasFirst && $hasLast) {
  $nameExpr = "NULLIF(CONCAT(TRIM(e.first_name),' ',
                             TRIM(e.last_name)),'')";
} elseif ($hasFirst) {
  $nameExpr = "NULLIF(TRIM(e.first_name),'')";
} elseif ($hasLast) {
  $nameExpr = "NULLIF(TRIM(e.last_name),'')";
} else {
  $nameExpr = "CAST(e.id AS CHAR)";
}

// check kung may roles table
$hasRoles = table_exists($pdo, 'roles');

// piliin ang role column (mas gusto ang roles table; fallback sa employees.*)
$roleExpr = "''";
if ($hasRoles && colExists($pdo,'roles','name'))        $roleExpr = 'r.name';
elseif ($hasRoles && colExists($pdo,'roles','title'))   $roleExpr = 'r.title';
elseif ($hasRoles && colExists($pdo,'roles','role'))    $roleExpr = 'r.role';
elseif (colExists($pdo,'employees','role'))             $roleExpr = 'e.role';
elseif (colExists($pdo,'employees','position'))         $roleExpr = 'e.position';
elseif (colExists($pdo,'employees','job_title'))        $roleExpr = 'e.job_title';

// conditional LEFT JOIN sa roles
$joinRoles = $hasRoles ? "LEFT JOIN roles r ON r.id = e.role_id" : "";


$sql = "SELECT ev.id, ev.employee_id,
               ev.period AS type, ev.due_date, ev.status,
               ev.overall_score AS score,
               COALESCE(ev.notes, ev.narrative, '') AS notes,
               COALESCE($nameExpr, CAST(e.id AS CHAR)) AS emp_name,
               COALESCE($roleExpr,'') AS role_name
        FROM evaluations ev
        JOIN employees e ON e.id = ev.employee_id
        $joinRoles
        WHERE 1";

$p = [];


     if ($term !== '') {
        $sql .= " AND ( COALESCE($nameExpr, CAST(e.id AS CHAR)) LIKE ?
                      OR COALESCE($roleExpr,'') LIKE ?
                      OR ev.period LIKE ? )";
        $p[] = "%$term%"; $p[] = "%$term%"; $p[] = "%$term%";
      }


      $sql .= " ORDER BY 
                  CASE ev.status WHEN 'overdue' THEN 0 WHEN 'due_soon' THEN 1 WHEN 'pending' THEN 2 ELSE 3 END,
                  ev.due_date ASC";

      $stmt = $pdo->prepare($sql); $stmt->execute($p);

      $rowsOut = [];
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $typeLabel   = humanType($r['type'] ?? $r['period_raw'] ?? '');
          $statusLabel = deriveStatus($r['status'] ?? '', $r['due_date'] ?? null);

          // <<— DITO: diretsong gamitin ang SELECT aliases
          $emp  = trim((string)($r['emp_name'] ?? ''));
          if ($emp === '') { $emp = (string)($r['employee_id'] ?? ''); } // fallback to id

          $role = trim((string)($r['role_name'] ?? ''));
          // kung wala talagang role, UI na ang maglalagay ng '—'

          $rowsOut[] = [
            'id'       => (int)$r['id'],
            'employee' => $emp,
            'role'     => $role,
            'type'     => $typeLabel,
            'due'      => $r['due_date'],
            'status'   => $statusLabel,
            'score'    => isset($r['score']) ? (float)$r['score'] : (isset($r['score_raw']) ? (float)$r['score_raw'] : null),
            'notes'    => (string)($r['notes'] ?? ''),
          ];
        }


      // Apply UI filters after mapping
      if ($type !== '')   $rowsOut = array_values(array_filter($rowsOut, fn($x)=>$x['type']===$type));
      if ($status !== '') $rowsOut = array_values(array_filter($rowsOut, fn($x)=>$x['status']===$status));

      ok(['rows'=>$rowsOut]);
    }

    /* -------- KPI -------- */
    if ($act === 'kpi') {
      $stmt = $pdo->query("SELECT status, due_date, overall_score, created_at, updated_at FROM evaluations");
      $dueWeek = $overdue = $completedMTD = 0;
      $scores = [];

      $today  = strtotime('today');
      $mStart = strtotime(date('Y-m-01'));

      while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $lbl = deriveStatus($r['status'], $r['due_date']);

        if ($lbl !== 'Completed') {
          if (!empty($r['due_date'])) {
            $due = strtotime($r['due_date']);
            if ($due >= $today && $due <= strtotime('+7 days', $today)) $dueWeek++;
            if ($due < $today) $overdue++;
          }
        } else {
          $ts = strtotime($r['updated_at'] ?: $r['created_at']);
          if ($ts >= $mStart) $completedMTD++;
        }

        if ($r['overall_score'] !== null) {
          $v = (float)$r['overall_score'];
          $v = ($v > 5) ? round($v/20, 1) : $v; // convert 100-scale to 5-scale if needed
          $scores[] = $v;
        }
      }
      $avg = count($scores) ? round(array_sum($scores)/count($scores), 1) : 0.0;

      ok([
        'due_this_week'=>(int)$dueWeek,
        'overdue'      =>(int)$overdue,
        'completed_mtd'=>(int)$completedMTD,
        'avg_score'    =>(float)$avg
      ]);
    }

    /* -------- RECENT SCORES -------- */
    if ($act === 'recent_scores') {
      $nameCols = colExists($pdo,'employees','full_name') ? 'e.full_name' :
                  (colExists($pdo,'employees','name') ? 'e.name' : 'e.id');

      $stmt = $pdo->query("
        SELECT $nameCols AS emp_name,
               ev.period AS period_raw,
               ev.overall_score AS score_raw,
               DATE(COALESCE(ev.updated_at, ev.created_at)) AS d,
               COALESCE(ev.notes, ev.narrative, '') AS notes
        FROM evaluations ev
        JOIN employees e ON e.id=ev.employee_id
        WHERE ev.overall_score IS NOT NULL
        ORDER BY COALESCE(ev.updated_at, ev.created_at) DESC
        LIMIT 10
      ");

      $out=[]; 
      while($r=$stmt->fetch(PDO::FETCH_ASSOC)){
        $s = (float)$r['score_raw'];
        $s = ($s > 5) ? round($s/20, 1) : $s; // 100 -> 5
        $out[]=[
          'employee'=>pickName($r),
          'type'=>humanType($r['period_raw']),
          'score'=>$s,
          'date'=>$r['d'],
          'notes'=>$r['notes']
        ];
      }
      ok($out);
    }

    /* -------- CREATE -------- */
    if ($act === 'create') {
      $emp  = (int)($in['employee_id'] ?? 0);
      $type = trim((string)($in['type'] ?? ''));      // UI type => period
      $due  = trim((string)($in['due_date'] ?? ''));  // yyyy-mm-dd
      if (!$emp || $type==='' || $due==='') bad('Missing required fields');

      if (!colExists($pdo,'evaluations','period'))   bad("Missing column 'period' in evaluations");
      if (!colExists($pdo,'evaluations','due_date')) bad("Missing column 'due_date' in evaluations");
      if (!colExists($pdo,'evaluations','status'))   bad("Missing column 'status' in evaluations");

      $stmt=$pdo->prepare("INSERT INTO evaluations (employee_id, period, due_date, status, created_at, updated_at)
                           VALUES (?,?,?,?,NOW(),NOW())");
      $stmt->execute([$emp,$type,$due,'pending']);
      ok(['id'=>$pdo->lastInsertId()]);
    }

    /* -------- COMPLETE -------- */
    if ($act === 'complete') {
      $id = (int)($in['id'] ?? 0);
      $score = isset($in['score']) && $in['score'] !== '' ? (float)$in['score'] : null;
      $notes = trim((string)($in['notes'] ?? ''));
      if (!$id) bad('Invalid id');
      $stmt=$pdo->prepare("
        UPDATE evaluations
        SET status='completed',
            overall_score = ?,
            notes = ?,
            updated_at = NOW()
        WHERE id=?
      ");
      $stmt->execute([$score,$notes,$id]);
      ok();
    }

    /* -------- SNOOZE -------- */
    if ($act === 'snooze') {
      $id = (int)($in['id'] ?? 0);
      $days = max(1, (int)($in['days'] ?? 7));
      if (!$id) bad('Invalid id');
      $stmt=$pdo->prepare("UPDATE evaluations SET due_date = DATE_ADD(due_date, INTERVAL ? DAY), updated_at=NOW() WHERE id=?");
      $stmt->execute([$days,$id]);
      ok();
    }

    /* -------- DELETE -------- */
    if ($act === 'delete') {
      $id=(int)($in['id'] ?? 0);
      if(!$id) bad('Invalid id');
      $pdo->prepare("DELETE FROM evaluations WHERE id=?")->execute([$id]);
      ok();
    }

    /* -------- EMPLOYEES DROPDOWN -------- */
   if ($act === 'list_employees') {
  // Detect existing name-related columns
  $hasFull = colExists($pdo, 'employees', 'full_name');
  $hasName = colExists($pdo, 'employees', 'name');
  $hasFirst = colExists($pdo, 'employees', 'first_name');
  $hasLast = colExists($pdo, 'employees', 'last_name');

  // Build safe name expression
  if ($hasFull) {
    $nameExpr = "NULLIF(TRIM(e.full_name),'')";
  } elseif ($hasName) {
    $nameExpr = "NULLIF(TRIM(e.name),'')";
  } elseif ($hasFirst && $hasLast) {
    $nameExpr = "NULLIF(CONCAT(TRIM(e.first_name),' ',
                               TRIM(e.last_name)),'')";
  } elseif ($hasFirst) {
    $nameExpr = "NULLIF(TRIM(e.first_name),'')";
  } elseif ($hasLast) {
    $nameExpr = "NULLIF(TRIM(e.last_name),'')";
  } else {
    $nameExpr = "CAST(e.id AS CHAR)";
  }

  // Query using only existing columns
  $sql = "SELECT e.id, COALESCE($nameExpr, CAST(e.id AS CHAR)) AS emp_name
          FROM employees e
          WHERE TRIM(COALESCE($nameExpr,'')) <> ''
          ORDER BY emp_name ASC";

  $rows = $pdo->query($sql);
  $out = [];
  while ($r = $rows->fetch(PDO::FETCH_ASSOC)) {
    $out[] = ['id' => (int)$r['id'], 'name' => $r['emp_name']];
  }
  ok($out);
}



    bad('Unknown action',400);

  } catch (Throwable $e) {
    @ob_end_clean();
    bad('Server error: '.$e->getMessage(), 500);
  }
}

/* -------------------- Page (HTML) -------------------- */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Evaluations | HR1 <?= htmlspecialchars($brandName) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <style>
    #sidebar{width:16rem} #sidebar.collapsed{width:4rem}
    #sidebar .nav-item{padding:.6rem .85rem}
    #sidebar.collapsed .nav-item{justify-content:center;padding:.6rem 0}
    #sidebar.collapsed .item-label, #sidebar.collapsed .section-title{display:none}
    #contentWrap{padding-left:16rem;transition:padding .25s ease}
    #contentWrap.collapsed{padding-left:4rem}
    .badge{padding:.15rem .5rem;border-radius:.5rem;font-size:.72rem;font-weight:600}
    .b-due{color:#92400e;background:#fef3c7}
    .b-ovd{color:#991b1b;background:#fee2e2}
    .b-done{color:#065f46;background:#d1fae5}
  </style>
</head>
<body class="bg-slate-50">
<header class="sticky top-0 z-40">
  <div id="topbarPad" class="ml-64 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="h-14 px-3 md:px-4 flex items-center gap-3">
      <button id="btnSidebar" class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-rose-500 text-white hover:bg-rose-600 shrink-0">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="flex-1 min-w-[220px]">
        <div class="relative max-w-2xl">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <input id="q" type="text" placeholder="Search employee / type…"
            class="w-full pl-9 pr-3 py-2.5 rounded-xl bg-white border border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-400 placeholder:text-slate-400">
        </div>
      </div>
      <div class="ml-1 flex items-center gap-2 px-2.5 py-1.5 rounded-xl bg-white ring-1 ring-slate-200 shadow">
        <div class="w-8 h-8 rounded-md bg-rose-500 text-white grid place-items-center text-xs font-semibold">
          <?php echo strtoupper(substr($u['name'],0,2)); ?>
        </div>
        <div class="leading-tight pr-1">
          <div class="text-sm font-medium text-slate-800 truncate max-w-[120px]"><?php echo htmlspecialchars($u['name']); ?></div>
          <div class="text-[11px] text-slate-500 capitalize"><?php echo htmlspecialchars($u['role']); ?></div>
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
        <img src="<?= $brandLogo ?>" alt="Logo" class="w-full h-full object-cover">
      </div>
      <span class="item-label font-semibold text-white"><?= htmlspecialchars($brandName) ?></span>
    </div>
    <nav class="py-4">
      <div class="px-4 text-[11px] tracking-wider text-slate-400/80 section-title">MAIN</div>
      <a href="index.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?php echo isActive('index.php'); ?>">
        <i class="fa-solid fa-house"></i><span class="item-label font-medium">Dashboard</span>
      </a>
      <a href="applicants.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?php echo isActive('applicants.php'); ?>">
        <i class="fa-solid fa-user"></i><span class="item-label">Applicants</span>
      </a>
      <a href="recruitment.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?php echo isActive('recruitment.php'); ?>">
        <i class="fa-solid fa-briefcase"></i><span class="item-label">Recruitment</span>
      </a>
      <a href="onboarding.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?php echo isActive('onboarding.php'); ?>">
        <i class="fa-solid fa-square-check"></i><span class="item-label">Onboarding</span>
      </a>
      <div class="px-4 mt-4 text-[11px] tracking-wider text-slate-400/80 section-title">MANAGEMENT</div>
      <a href="employees.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?php echo isActive('employees.php'); ?>">
        <i class="fa-solid fa-users"></i><span class="item-label">Employees</span>
      </a>
      <a href="evaluations.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?php echo isActive('evaluations.php'); ?>">
        <i class="fa-solid fa-chart-line"></i><span class="item-label">Evaluations</span>
      </a>
      <a href="recognition.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?php echo isActive('recognition.php'); ?>">
        <i class="fa-solid fa-award"></i><span class="item-label">Recognition</span>
      </a>
      <?php if (!empty($u['role']) && strtolower($u['role']) === 'admin'): ?>
        <a href="users.php"
          class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('users.php'); ?>">
          <i class="fa-solid fa-user-gear"></i>
          <span class="item-label">Users</span>
        </a>
      <?php endif; ?>

    </nav>
  </aside>

  <!-- CONTENT -->
  <main id="contentWrap" class="min-h-[calc(100vh-56px)] transition-all duration-200">
    <div class="px-8 py-8">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-rose-600">Evaluations</h1>
        <button id="btnNew" class="bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-lg shadow">+ New Review</button>
      </div>

      <!-- KPI -->
      <div id="kpiWrap" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8"></div>

      <!-- Filters -->
      <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative w-full sm:w-80">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
          <input id="fQuery" type="text" placeholder="Search employee / type…"
            class="w-full pl-8 pr-3 py-2 rounded-lg bg-white border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-300">
        </div>
        <select id="fType" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300">
          <option value="">All types</option>
          <option>Initial 30 Day</option><option>Initial 60 Day</option><option>Initial 90 Day</option>
          <option>Mid-Year</option><option>Annual</option>
        </select>
        <select id="fStatus" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300">
          <option value="">All status</option>
          <option>Due Soon</option><option>Overdue</option><option>Completed</option>
        </select>
      </div>

      <!-- GRID -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Reviews Table -->
        <div class="xl:col-span-2 rounded-xl bg-white ring-1 ring-slate-200">
          <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">Reviews Queue</h2>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
              <thead class="bg-slate-100 text-slate-600 uppercase text-xs">
                <tr>
                  <th class="px-6 py-3">Employee</th>
                  <th class="px-6 py-3">Type</th>
                  <th class="px-6 py-3">Due</th>
                  <th class="px-6 py-3">Status</th>
                  <th class="px-6 py-3">Action</th>
                </tr>
              </thead>
              <tbody id="revRows"></tbody>
            </table>
          </div>
        </div>

        <!-- Recent Scores -->
        <div class="rounded-xl bg-white ring-1 ring-slate-200">
          <div class="px-4 py-3 border-b border-slate-200">
            <h2 class="font-semibold text-slate-800">Recent Scores</h2>
          </div>
          <div id="recentWrap" class="divide-y divide-slate-200"></div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- New Review Modal -->
<div id="modalNew" class="fixed inset-0 hidden items-center justify-center bg-black/40 z-50">
  <div class="bg-white w-[95%] max-w-md rounded-xl shadow-lg p-5">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg font-semibold text-slate-800">Create Review</h3>
      <button class="text-slate-500 hover:text-slate-700" onclick="closeNew()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="space-y-3">
      <label class="block text-sm">
        <span class="text-slate-600">Employee</span>
        <select id="newEmp" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></select>
      </label>
      <label class="block text-sm">
        <span class="text-slate-600">Type</span>
        <select id="newType" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
          <option>Initial 30 Day</option><option>Initial 60 Day</option><option>Initial 90 Day</option>
          <option>Mid-Year</option><option>Annual</option>
        </select>
      </label>
      <label class="block text-sm">
        <span class="text-slate-600">Due date</span>
        <input id="newDue" type="date" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
      </label>
    </div>
    <div class="mt-4 flex justify-end gap-2">
      <button type="button" class="px-3 py-2 rounded-lg border" onclick="closeNew()">Cancel</button>
      <button type="button" class="px-3 py-2 rounded-lg bg-rose-600 text-white" onclick="createReview()">Create</button>
    </div>
  </div>
</div>

<!-- Open Review Modal -->
<div id="modalOpen" class="fixed inset-0 hidden items-center justify-center bg-black/40 z-50">
  <div class="bg-white w-[95%] max-w-md rounded-xl shadow-lg p-5">
    <div class="flex items-center justify-between mb-3">
      <h3 id="openTitle" class="text-lg font-semibold text-slate-800">Review</h3>
      <button class="text-slate-500 hover:text-slate-700" onclick="closeOpen()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <input type="hidden" id="openId">
    <div class="grid grid-cols-2 gap-3">
      <label class="block text-sm col-span-2">
        <span class="text-slate-600">Score (1–5)</span>
        <input id="openScore" type="number" step="0.1" min="1" max="5" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="e.g., 4.5">
      </label>
      <label class="block text-sm col-span-2">
        <span class="text-slate-600">Notes</span>
        <textarea id="openNotes" rows="4" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Observations, coaching focus…"></textarea>
      </label>
    </div>
    <div class="mt-4 flex flex-wrap gap-2 justify-between">
      <div class="flex gap-2">
        <button class="px-3 py-2 rounded-lg border" onclick="snooze(7)"><i class="fa-regular fa-clock mr-1"></i>Snooze 7d</button>
        <button class="px-3 py-2 rounded-lg border" onclick="snooze(14)">Snooze 14d</button>
      </div>
      <div class="flex gap-2">
        <button class="px-3 py-2 rounded-lg border text-rose-700" onclick="delReview()"><i class="fa-regular fa-trash-can mr-1"></i>Delete</button>
        <button class="px-3 py-2 rounded-lg bg-emerald-600 text-white" onclick="completeReview()"><i class="fa-solid fa-check mr-1"></i>Mark Completed</button>
      </div>
    </div>
  </div>
</div>

<script>
/* ------------ Sidebar toggle ------------ */
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

/* ------------ Fetch helpers (tolerant to non-JSON) ------------ */
async function api(action, payload={}){
  const res = await fetch('evaluations.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({action, ...payload})
  });
  const txt = await res.text();
  try { return JSON.parse(txt); }
  catch(e){
    console.error('Non-JSON response from server:', txt);
    const plain = txt.replace(/<[^>]*>/g,' ').replace(/\s+/g,' ').trim();
    return { ok:false, error: plain || ('HTTP '+res.status) };
  }
}

/* ------------ KPI render ------------ */
async function loadKPI(){
  const r = await api('kpi'); 
  if(!r.ok){ alert(r.error); return; }
  const k = r.data;

  function card(icon, label, val){
    return '<div class="rounded-xl bg-white ring-1 ring-slate-200 p-4 flex items-center gap-3">' +
             '<div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-600 grid place-items-center">' +
               '<i class="'+icon+'"></i>' +
             '</div>' +
             '<div><div class="text-xs text-slate-500">'+label+'</div>' +
             '<div class="text-lg font-semibold">'+val+'</div></div>' +
           '</div>';
  }

  document.getElementById('kpiWrap').innerHTML =
    card('fa-solid fa-calendar-week','Due This Week', k.due_this_week) +
    card('fa-solid fa-triangle-exclamation','Overdue', k.overdue) +
    card('fa-solid fa-circle-check',      'Completed (MTD)', k.completed_mtd) + // free icon
    card('fa-solid fa-star-half-stroke','Avg Score', (k.avg_score||0).toFixed(1)+'/5');
}

/* ------------ Reviews list ------------ */
const fQuery=document.getElementById('fQuery');
const fType=document.getElementById('fType');
const fStatus=document.getElementById('fStatus');
async function loadList(){
  const r = await api('list', { q:fQuery.value, type:fType.value, status:fStatus.value });
  if(!r.ok){ alert(r.error); return; }
  const rows = r.data.rows || [];
  const tbody = document.getElementById('revRows');

  function rowHtml(row){
    let badge = row.status || 'Pending', cls = 'badge';
    if(badge === 'Due Soon'){ cls += ' b-due'; }
    else if(badge === 'Overdue'){ cls += ' b-ovd'; }
    else if(badge === 'Completed'){ cls += ' b-done'; }
    return '<tr class="border-t">' +
         '<td class="px-6 py-3 font-medium text-slate-800">' + escapeHtml(row.employee) + '</td>' +
         '<td class="px-6 py-3">' + escapeHtml(row.type) + '</td>' +
         '<td class="px-6 py-3">' + fmtDate(row.due) + '</td>' +
         '<td class="px-6 py-3"><span class="' + cls + '">' + badge + '</span></td>' +
         '<td class="px-6 py-3">' +
           '<button class="text-rose-600 hover:underline" onclick="openReview(' + row.id + ', \'' + escapeAttr(row.employee) + '\', \'' + escapeAttr(row.type) + '\')">Open</button>' +
         '</td>' +
       '</tr>';

  }

  tbody.innerHTML = rows.map(rowHtml).join('');
}
[fQuery,fType,fStatus].forEach(el=>el.addEventListener('input',()=>{ loadList(); }));

/* ------------ Recent scores ------------ */
async function loadRecent(){
  const r = await api('recent_scores'); 
  if(!r.ok){ alert(r.error); return; }
  const list = r.data || [];
  const wrap = document.getElementById('recentWrap');

  function itemHtml(s){
    return '<div class="px-4 py-3">' +
             '<div class="flex items-center justify-between">' +
               '<div class="font-medium text-slate-800">' + escapeHtml(s.employee) + '</div>' +
               '<div class="text-sm font-semibold text-rose-600">' + ((s.score||0).toFixed(1)) + '/5</div>' +
             '</div>' +
             '<div class="text-xs text-slate-500">' + escapeHtml(s.type) + ' • ' + fmtDate(s.date) + '</div>' +
             '<p class="text-sm text-slate-700 mt-1">' + escapeHtml(s.notes||'') + '</p>' +
           '</div>';
  }
  wrap.innerHTML = list.map(itemHtml).join('');
}

/* ------------ New Review modal ------------ */
const mNew=document.getElementById('modalNew');
document.getElementById('btnNew')?.addEventListener('click', async ()=>{
  await loadEmpOptions(); openNew();
});
function openNew(){ mNew.classList.remove('hidden'); mNew.classList.add('flex'); }
function closeNew(){ mNew.classList.add('hidden'); mNew.classList.remove('flex'); }
async function loadEmpOptions(){
  const r = await api('list_employees'); 
  if(!r.ok){ alert(r.error); return; }
  const sel = document.getElementById('newEmp');
  const list = r.data || [];

  let html = '<option value="" disabled selected>Select employee…</option>';
for (let i=0; i<list.length; i++){
  const e = list[i];
  const nm = (e.name || '').trim();
  if (!nm) continue; // skip blanks defensively
  html += '<option value="' + e.id + '">' + escapeHtml(nm) + '</option>';
}
sel.innerHTML = html;

}
async function createReview(){
  try{
    const emp  = document.getElementById('newEmp').value;
    const type = document.getElementById('newType').value;
    let   due  = document.getElementById('newDue').value;
    if(!emp||!type||!due){ alert('Complete all fields.'); return; }
    const d = new Date(due); if(!isNaN(d)) due = d.toISOString().slice(0,10);
    const r = await api('create',{employee_id: +emp, type, due_date: due});
    if(!r.ok) throw new Error(r.error||'Create failed');
    closeNew(); await loadKPI(); await loadList(); await loadRecent();
  }catch(e){ alert('Create error: '+(e.message||e)); console.error(e); }
}

/* ------------ Open Review modal (complete/snooze/delete) ------------ */
const mOpen=document.getElementById('modalOpen');
function openReview(id, emp, type){
  document.getElementById('openId').value = id;
  document.getElementById('openTitle').textContent = emp + ' • ' + type;
  document.getElementById('openScore').value = '';
  document.getElementById('openNotes').value = '';
  mOpen.classList.remove('hidden');
  mOpen.classList.add('flex');
}
function closeOpen(){ mOpen.classList.add('hidden'); mOpen.classList.remove('flex'); }

async function completeReview(){
  const id=+document.getElementById('openId').value;
  const score=document.getElementById('openScore').value;
  const notes=document.getElementById('openNotes').value;
  const r=await api('complete',{id,score,notes});
  if(r.ok){ closeOpen(); await loadKPI(); await loadList(); await loadRecent(); } else { alert(r.error||'Error'); }
}
async function snooze(days){
  const id=+document.getElementById('openId').value;
  const r=await api('snooze',{id,days});
  if(r.ok){ closeOpen(); await loadList(); await loadKPI(); } else { alert(r.error||'Error'); }
}
async function delReview(){
  if(!confirm('Delete this review?')) return;
  const id=+document.getElementById('openId').value;
  const r=await api('delete',{id});
  if(r.ok){ closeOpen(); await loadList(); await loadKPI(); } else { alert(r.error||'Error'); }
}

/* ------------ utils ------------ */
function fmtDate(d){ if(!d) return '—'; const x=new Date(d); if(isNaN(x)) return d; return x.toLocaleDateString(undefined,{month:'short',day:'2-digit',year:'numeric'}); }
function escapeHtml(s){ return String(s??'').replace(/[&<>"']/g, m=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[m])); }
function escapeAttr(s){ return String(s??'').replace(/["']/g, m=> m==="\""?"&quot;":"&#039;"); }

/* ------------ initial load ------------ */
(async function init(){
  await loadKPI();
  await loadList();
  await loadRecent();
})();
</script>
</body>
</html>
