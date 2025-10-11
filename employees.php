<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
$u = $_SESSION['user'];

$role = strtolower($u['role'] ?? '');
if (!in_array($role, ['admin', 'hr manager'], true)) {
    http_response_code(403);
    exit('Forbidden');
}


$brandName = 'Nextgenmms';
$brandLogo = 'assets/logo2.jpg';

/* --- Helper: active state --- */
function isActive($page) {
  $is = basename($_SERVER['PHP_SELF']) === $page;
  return $is ? 'bg-rose-900/60 text-rose-500'
             : 'text-slate-300 hover:text-rose-500 hover:bg-rose-900/40';
}

/* ================= DB & helpers ================= */
require_once __DIR__ . '/includes/db.php';
date_default_timezone_set('Asia/Manila');
try { $pdo->exec("SET time_zone = '+08:00'"); } catch (Throwable $e) {}

function table_exists(PDO $pdo, string $table): bool {
  try { return (bool)$pdo->query("SHOW TABLES LIKE ".$pdo->quote($table))->fetch(); }
  catch(Throwable $e){ return false; }
}
function col_exists(PDO $pdo, string $table, string $col): bool {
  try { $st = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?"); $st->execute([$col]); return (bool)$st->fetch(); }
  catch(Throwable $e){ return false; }
}
function pickcol(PDO $pdo, string $table, array $options): ?string {
  foreach ($options as $c) if (col_exists($pdo,$table,$c)) return $c;
  return null;
}

/* ============= AJAX: return new-hire files for an employee ============= */
if (isset($_GET['ajax']) && $_GET['ajax'] === 'files') {
  header('Content-Type: application/json; charset=utf-8');

  $ROW_ID = (int)($_GET['id'] ?? 0);
  $result = ['ok' => false, 'files' => [], 'error' => null];

  try {
    // Map employee row -> applicant_id
    $applicantId = 0;

    if ($ROW_ID > 0) {
      if (table_exists($pdo, 'employees')) {
        // Prefer direct FK if meron
        if (col_exists($pdo, 'employees', 'applicant_id')) {
          $st = $pdo->prepare("SELECT applicant_id FROM employees WHERE id=?");
          $st->execute([$ROW_ID]);
          $applicantId = (int)($st->fetchColumn() ?: 0);
        }

        // Fallback: match by name if wala ang applicant_id
        if (!$applicantId && table_exists($pdo,'applicants')) {
          $nameCol = pickcol($pdo,'employees',['name','full_name','employee_name']) ?: 'name';
          $st = $pdo->prepare("SELECT `$nameCol` FROM employees WHERE id=?");
          $st->execute([$ROW_ID]);
          $empName = trim((string)$st->fetchColumn());

          if ($empName !== '') {
            $st = $pdo->prepare("SELECT id FROM applicants WHERE name=? LIMIT 1");
            $st->execute([$empName]);
            $applicantId = (int)($st->fetchColumn() ?: 0);
          }
        }
      }

      // Kung employees table mismo ay “fallback from applicants”, id = applicant_id
      if (!$applicantId) $applicantId = $ROW_ID;
    }

    if (!$applicantId) {
      $result['error'] = 'No applicant mapping found.'; echo json_encode($result); exit;
    }

    // Basahin ang files
    if (!table_exists($pdo,'newhire_uploads')) {
      $result['error'] = 'newhire_uploads table not found.'; echo json_encode($result); exit;
    }

    $labelMap = [
      'gov_id1'   => 'Gov ID #1 (Front/Back)',
      'gov_id2'   => 'Gov ID #2 (optional)',
      'sss'       => 'SSS',
      'pagibig'   => 'Pag-IBIG',
      'philhealth'=> 'PhilHealth',
      'tin'       => 'TIN',
      'nbi'       => 'NBI / Police Clearance',
      'photo2x2'  => '2x2 Photo',
      'diploma'   => 'Diploma / COE / TOR (optional)',
      // alternatibong keys na pwedeng lumabas
      'clearance' => 'NBI / Police Clearance',
      'photo_2x2' => '2x2 Photo',
    ];

    $st = $pdo->prepare("SELECT file_key, file_path, uploaded_at
                         FROM newhire_uploads
                         WHERE applicant_id=?
                         ORDER BY uploaded_at DESC");
    $st->execute([$applicantId]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    $files = [];
    foreach ($rows as $r) {
      $key = (string)($r['file_key'] ?? '');
      $files[] = [
        'key'   => $key,
        'label' => $labelMap[$key] ?? strtoupper($key),
        'path'  => (string)$r['file_path'],
        'date'  => (string)$r['uploaded_at'],
      ];
    }

    $result['ok'] = true;
    $result['files'] = $files;
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;

  } catch (Throwable $e) {
    $result['error'] = $e->getMessage();
    echo json_encode($result);
    exit;
  }
}


/* ================= READ employees for display ================= */
$employees = [];

if (table_exists($pdo,'employees')) {
  $idCol    = pickcol($pdo,'employees',['id','emp_id']);
  $nameCol  = pickcol($pdo,'employees',['name','full_name','employee_name']);
  $roleCol  = pickcol($pdo,'employees',['role','position','job_title']);
  $deptCol  = pickcol($pdo,'employees',['department','dept']);
  $statCol  = pickcol($pdo,'employees',['status','employment_status']);
  $dateCol  = pickcol($pdo,'employees',['date_hired','hired_at','start_date','created_at']);
  $archCol  = pickcol($pdo,'employees',['archived','is_archived','deleted','is_deleted','is_active']);

  if ($idCol && $nameCol) {
    $archSel = $archCol
      ? ($archCol==='is_active' ? "IF(`$archCol`=0,1,0) AS archived" : "`$archCol` AS archived")
      : "0 AS archived";
    $select = "`$idCol` AS id, `$nameCol` AS name"
            . ($roleCol ? ", `$roleCol` AS role" : ", NULL AS role")
            . ($deptCol ? ", `$deptCol` AS dept" : ", NULL AS dept")
            . ($statCol ? ", `$statCol` AS status" : ", 'Active' AS status")
            . ($dateCol ? ", `$dateCol` AS hire" : ", NULL AS hire")
            . ", $archSel";

    $order = $dateCol ? "ORDER BY `$dateCol` ASC, `$idCol` ASC" : "ORDER BY `$idCol` ASC";
    $stmt = $pdo->query("SELECT $select FROM employees $order");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}

/* Fallback: hired applicants */
if (!$employees && table_exists($pdo,'applicants')) {
  $idCol   = pickcol($pdo,'applicants',['id']);
  $nameCol = pickcol($pdo,'applicants',['name','full_name','applicant_name']);
  $roleCol = pickcol($pdo,'applicants',['role','position','position_applied','apply_for']);
  $deptCol = pickcol($pdo,'applicants',['department','dept']);
  $dateCol = pickcol($pdo,'applicants',['date_hired','hire_date','start_date','created_at']);
  if ($idCol && $nameCol) {
    $select = "`$idCol` AS id, `$nameCol` AS name"
            . ($roleCol ? ", `$roleCol` AS role" : ", NULL AS role")
            . ($deptCol ? ", `$deptCol` AS dept" : ", NULL AS dept")
            . ", 'Active' AS status"
            . ($dateCol ? ", `$dateCol` AS hire" : ", NOW() AS hire")
            . ", 0 AS archived";
    $order = $dateCol ? "ORDER BY `$dateCol` ASC, `$idCol` ASC" : "ORDER BY `$idCol` ASC";
    $stmt = $pdo->query("SELECT $select FROM applicants WHERE LOWER(TRIM(status))='hired' $order");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}

/* map status to UI */
function display_status_label($raw) {
  return match (strtolower((string)$raw)) {
    'regular'   => 'Active',
    'probation' => 'Probation',
    'inactive'  => 'Inactive',
    default     => ucfirst((string)$raw),
  };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Employees | HR1 <?= htmlspecialchars($brandName) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
  #sidebar{width:16rem}
  #sidebar.collapsed{width:4rem}
  #sidebar .nav-item{padding:.6rem .85rem}
  #sidebar.collapsed .nav-item{justify-content:center;padding:.6rem 0}
  #sidebar.collapsed .item-label, #sidebar.collapsed .section-title{display:none}
  #contentWrap{padding-left:16rem;transition:padding .25s ease}
  #contentWrap.collapsed{padding-left:4rem}
  .emp-tbl{table-layout:fixed;border-collapse:separate;border-spacing:0 6px;}
  .emp-tbl tr{background:white;border-radius:8px;}
  .emp-tbl th{background:#f9fafb;}
  .emp-tbl td{vertical-align:middle;}
  .emp-tbl col.idx{width:60px;}
  .emp-tbl col.name{width:260px;}
  .emp-tbl col.role{width:220px;}
  .emp-tbl col.dept{width:160px;}
  .emp-tbl col.stat{width:140px;}
  .emp-tbl col.hire{width:150px;}
  .emp-tbl col.act{width:180px;}
  .iconbtn{width:36px;height:36px;display:grid;place-items:center;border-radius:.7rem;border:1px solid #e5e7eb;color:#475569;transition:all .15s;margin-right:6px}
  .iconbtn:last-child{margin-right:0}
  .iconbtn:hover{background:#f1f5f9;transform:scale(1.05)}
  .emp-tbl td, .emp-tbl th {padding-top:14px;padding-bottom:14px;}
  </style>
</head>
<body class="bg-slate-50">

<header class="sticky top-0 z-40">
  <div id="topbarPad" class="ml-64 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="h-14 px-3 md:px-4 flex items-center gap-3">
      <button id="btnSidebar" class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-rose-500 text-white hover:bg-rose-600 shrink-0">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="flex-1"></div>
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

  <main id="contentWrap" class="min-h-[calc(100vh-56px)] transition-all duration-200">
    <div class="px-8 py-8">

      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-semibold text-rose-600">Employees</h1>
        <div class="flex items-center gap-3">
          <button id="btnShowArchived" type="button"
                  class="inline-flex items-center gap-2 h-10 px-3 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">
            <i class="fa-solid fa-box-archive"></i>
            Show Archived
          </button>
          <button id="btnExportPdf" type="button"
                  class="bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-lg shadow inline-flex items-center gap-2"
                  title="Export to PDF">
            <i class="fa-regular fa-file-pdf"></i>
            Export PDF
          </button>
        </div>
      </div>

      <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative w-full sm:w-80">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
          <input id="q" type="text" placeholder="Search name / role / dept…"
                 class="w-full pl-8 pr-3 py-2 rounded-lg bg-white border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-300">
        </div>
        <?php
          $roles = array_unique(array_filter(array_map(fn($e)=>$e['role'] ?? '', $employees)));
          sort($roles);
        ?>
        <select id="fRole" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300">
          <option value="">All Roles</option>
          <?php foreach ($roles as $r): 
                $v = strtolower(trim($r)); ?>
            <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($r) ?></option>
          <?php endforeach; ?>
        </select>

<select id="fStatus" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300">
  <option value="">All Status</option>
  <option value="active">Active</option>
  <option value="on leave">On Leave</option>
  <option value="probation">Probation</option>
  <option value="inactive">Inactive</option>
</select>

      </div>

      <div class="bg-white shadow rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left emp-tbl">
            <colgroup>
              <col class="idx"><col class="name"><col class="role"><col class="dept"><col class="stat"><col class="hire"><col class="act">
            </colgroup>
            <thead class="bg-slate-100 text-slate-600 uppercase text-xs">
              <tr>
                <th class="px-6 py-3 text-center">#</th>
                <th class="px-6 py-3">Name</th>
                <th class="px-6 py-3 hidden xl:table-cell">Role</th>
                <th class="px-6 py-3 hidden xl:table-cell">Department</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Date Hired</th>
                <th class="px-6 py-3 text-center">Action</th>
              </tr>
            </thead>
            <tbody id="empRows">
              <?php if (!$employees): ?>
                <tr class="border-t"><td colspan="7" class="px-6 py-6 text-slate-500">No employees yet.</td></tr>
              <?php else: foreach ($employees as $e): ?>
                <?php
                  $empName = trim((string)($e['name'] ?? '')); if ($empName === '') continue;
                  $ini = '';
                  foreach (preg_split('/\s+/', $empName) as $p) { if ($p !== '') { $ini .= mb_strtoupper(mb_substr($p,0,1)); if (mb_strlen($ini) >= 2) break; } }
                  $roleTxt = $e['role'] ?? '—';
                  $deptTxt = $e['dept'] ?? 'Operations';
                  $dispStatus = display_status_label($e['status'] ?? 'Active');
                  $st = strtolower($dispStatus);
                  $cls = 'text-slate-700';
                  if ($st==='active') $cls='text-green-600';
                  elseif ($st==='on leave') $cls='text-orange-500';
                  elseif ($st==='probation') $cls='text-rose-600';
                  $arch = (int)($e['archived'] ?? 0);
                ?>
                <tr class="border-t emp-row"
                  data-id="<?= (int)($e['id'] ?? 0) ?>"
                  data-name="<?= htmlspecialchars($e['name'] ?? '',ENT_QUOTES) ?>"
                  data-role="<?= htmlspecialchars(strtolower($roleTxt),ENT_QUOTES) ?>"
                  data-dept="<?= htmlspecialchars($deptTxt,ENT_QUOTES) ?>"
                  data-status="<?= htmlspecialchars(strtolower($dispStatus),ENT_QUOTES) ?>"
                  data-hire="<?= htmlspecialchars($e['hire'] ?? '',ENT_QUOTES) ?>"
                  data-archived="<?= $arch ?>">


                  <td class="px-6 py-3 text-center tabular-nums"><span class="idx"></span></td>

                  <td class="px-6 py-3">
                    <div class="flex items-center gap-3">
                      <div class="w-9 h-9 rounded-full bg-rose-100 text-rose-700 grid place-items-center text-xs font-semibold"><?= htmlspecialchars($ini) ?></div>
                      <div class="leading-tight">
                        <div class="font-medium text-slate-800"><?= htmlspecialchars($empName) ?></div>
                        <div class="text-[12px] text-slate-500 xl:hidden">
                          <?= htmlspecialchars($roleTxt) ?> • <?= htmlspecialchars($deptTxt) ?>
                        </div>
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-3 hidden xl:table-cell"><?= htmlspecialchars($roleTxt) ?></td>
                  <td class="px-6 py-3 hidden xl:table-cell"><?= htmlspecialchars($deptTxt) ?></td>

                  <td class="px-6 py-3 <?= $cls ?> font-medium"><?= htmlspecialchars($dispStatus) ?></td>
                  <td class="px-6 py-3"><?= !empty($e['hire']) ? date('M d, Y', strtotime($e['hire'])) : '—'; ?></td>
                  <td class="px-6 py-3 text-center">
                    <div class="inline-flex justify-center">
                      <button class="iconbtn act-view" title="View"><i class="fa-regular fa-eye"></i></button>
                      <button class="iconbtn act-edit" title="Edit status"><i class="fa-regular fa-pen-to-square"></i></button>
                      <button class="iconbtn act-archive"   title="Archive"><i class="fa-solid fa-box-archive"></i></button>
                      <button class="iconbtn act-unarchive" title="Unarchive"><i class="fa-solid fa-box-open"></i></button>
                      <button class="iconbtn act-delete text-rose-700" title="Delete permanently"><i class="fa-regular fa-trash-can"></i></button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- Generic Modal -->
<div id="modal" class="fixed inset-0 hidden items-center justify-center bg-black/40 z-50 p-4">
  <div class="w-[560px] max-w-[95vw] bg-white rounded-2xl ring-1 ring-slate-200 shadow-xl p-5">
    <div class="flex items-center justify-between mb-3">
      <h3 id="mTitle" class="text-lg font-semibold text-slate-800">Employee</h3>
      <button id="mClose" class="text-slate-500 hover:text-slate-700"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div id="mBody" class="space-y-3"></div>
    <div class="mt-5 text-right">
      <button id="mOk" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg">Close</button>
    </div>
  </div>
</div>

<script>
/* ===== Sidebar toggle ===== */
const btn=document.getElementById('btnSidebar');
const sb=document.getElementById('sidebar');
const main=document.getElementById('contentWrap');
const topbarPad=document.getElementById('topbarPad');
function applyShift(){ if(sb.classList.contains('collapsed')){ topbarPad.classList.remove('ml-64'); topbarPad.classList.add('ml-16'); main.classList.add('collapsed'); } else { topbarPad.classList.remove('ml-16'); topbarPad.classList.add('ml-64'); main.classList.remove('collapsed'); } }
btn?.addEventListener('click',()=>{ sb.classList.toggle('collapsed'); localStorage.setItem('sb-collapsed', sb.classList.contains('collapsed')?'1':'0'); applyShift(); });
if(localStorage.getItem('sb-collapsed')==='1'){ sb.classList.add('collapsed'); } applyShift();

/* ===== Export PDF ===== */
const btnExportPdf = document.getElementById('btnExportPdf');
let SHOW_ARCHIVED = false;
btnExportPdf?.addEventListener('click', (e) => {
  e.preventDefault();
  const qVal    = (document.getElementById('q')?.value || '').trim();
  const roleVal = document.getElementById('fRole')?.value || '';
  const statVal = document.getElementById('fStatus')?.value || '';
  const url = `employees_export.php?q=${encodeURIComponent(qVal)}&role=${encodeURIComponent(roleVal)}&status=${encodeURIComponent(statVal)}&archived=${SHOW_ARCHIVED?1:0}`;
  window.open(url, '_blank', 'noopener,noreferrer');
});

/* ===== Filters & Renumber ===== */
const q = document.getElementById('q');
const fRole = document.getElementById('fRole');
const fStatus = document.getElementById('fStatus');

function currentRows(){ return Array.from(document.querySelectorAll('#empRows .emp-row')); }
function renumber(){
  let n = 1;
  currentRows().forEach(tr=>{
    if (getComputedStyle(tr).display !== 'none') {
      const idx = tr.querySelector('.idx'); if (idx) idx.textContent = n++;
    }
  });
}
function norm(s){ return (s||'').toLowerCase(); }
function applyFilters(){
  const text = norm(q.value);
  const role = norm(fRole.value);
  const stat = norm(fStatus.value);

  currentRows().forEach(tr=>{
    const name = norm(tr.dataset.name);
    const r    = norm(tr.dataset.role);
    const d    = norm(tr.dataset.dept);
    const s    = norm(tr.dataset.status);
    const isArchived = (tr.dataset.archived === '1');

    // archive gate
    if (SHOW_ARCHIVED ? !isArchived : isArchived) { tr.style.display = 'none'; return; }

    const okQ  = !text || name.includes(text) || r.includes(text) || d.includes(text);
    const okR  = !role || r===role;
    const okS  = !stat || s===stat;
    tr.style.display = (okQ && okR && okS) ? '' : 'none';

    // toggle action visibility
    const btnArchive   = tr.querySelector('.act-archive');
    const btnUnarchive = tr.querySelector('.act-unarchive');
    const btnDelete    = tr.querySelector('.act-delete');
    if (isArchived) { btnArchive.style.display='none'; btnUnarchive.style.display=''; btnDelete.style.display=''; }
    else            { btnArchive.style.display='';    btnUnarchive.style.display='none'; btnDelete.style.display='none'; }
  });
  renumber();
}

/* ===== Archived toggle button ===== */
const btnShowArchived = document.getElementById('btnShowArchived');
function refreshArchivedBtn(){
  btnShowArchived.innerHTML = (SHOW_ARCHIVED
    ? '<i class="fa-solid fa-box-open"></i> Hide Archived'
    : '<i class="fa-solid fa-box-archive"></i> Show Archived');
  btnShowArchived.classList.toggle('bg-rose-50', SHOW_ARCHIVED);
  btnShowArchived.classList.toggle('text-rose-700', SHOW_ARCHIVED);
  btnShowArchived.classList.toggle('border-rose-300', SHOW_ARCHIVED);
}
btnShowArchived?.addEventListener('click', ()=>{ SHOW_ARCHIVED = !SHOW_ARCHIVED; refreshArchivedBtn(); applyFilters(); });
refreshArchivedBtn();

/* ===== Generic modal helpers ===== */
const modal = document.getElementById('modal');
const mTitle = document.getElementById('mTitle');
const mBody  = document.getElementById('mBody');
const mOk    = document.getElementById('mOk');
const mClose = document.getElementById('mClose');
function openModal(title, body, okText='Close', onOk=null){
  mTitle.textContent = title; mBody.innerHTML = body; mOk.textContent = okText;
  modal.classList.remove('hidden'); modal.classList.add('flex');
  mOk.onclick = ()=>{ if(onOk) onOk(); closeModal(); };
}
function closeModal(){ modal.classList.add('hidden'); modal.classList.remove('flex'); }
mClose.onclick = closeModal; modal.onclick = (e)=>{ if(e.target===modal) closeModal(); };

function confirmBox(message, yesLabel, onYes){
  const id='confirmBox';
  const html = `
    <div id="${id}" class="fixed inset-0 flex items-center justify-center bg-black/40 z-[9999]">
      <div class="bg-white rounded-2xl shadow-xl p-6 w-[380px] text-center ring-1 ring-slate-200">
        <div class="text-slate-800 text-lg font-semibold mb-3">${message}</div>
        <div class="flex justify-center gap-3 mt-4">
          <button id="${id}-ok" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg">${yesLabel||'OK'}</button>
          <button id="${id}-no" class="bg-slate-200 hover:bg-slate-300 text-slate-800 px-4 py-2 rounded-lg">Cancel</button>
        </div>
      </div>
    </div>`;
  document.body.insertAdjacentHTML('beforeend', html);
  document.getElementById(`${id}-ok`).onclick = ()=>{ document.getElementById(id).remove(); onYes&&onYes(); };
  document.getElementById(`${id}-no`).onclick = ()=>{ document.getElementById(id).remove(); };
}

function escapeHtml(s){return (s||'').replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m]));}
function fmtDate(d){ if(!d) return '—'; const t = Date.parse(d); return isNaN(t)?d:new Date(t).toLocaleDateString(undefined,{month:'short',day:'2-digit',year:'numeric'}); }

/* ===== API helper ===== */
async function apiEmployees(payload){
  try{
    const r = await fetch('api/employees_api.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    });
    const raw = await r.text();
    try { return JSON.parse(raw); }
    catch (e) { alert(raw.slice(0,500)); return { ok:false, error:'Invalid JSON from API' }; }
  }catch(e){ return { ok:false, error:String(e) }; }
}

/* ===== Row actions ===== */
document.getElementById('empRows').addEventListener('click', async (e)=>{
  const btn = e.target.closest('button'); if(!btn) return;
  const tr  = btn.closest('tr');
  const id  = Number(tr.dataset.id||0);
  const name= tr.dataset.name||'—';
  const role= tr.dataset.role||'—';
  const dept= tr.dataset.dept||'—';
  const status = tr.dataset.status||'Active';
  const hire   = fmtDate(tr.dataset.hire||'');

  if (btn.classList.contains('act-view')) {
  // Kuhanin din ang files via AJAX
  let filesHtml = '<div class="text-xs text-slate-500">No uploaded files yet.</div>';
  try {
    const r = await fetch(`employees.php?ajax=files&id=${id}`, {cache:'no-store'});
    const j = await r.json();
    if (j && j.ok && Array.isArray(j.files) && j.files.length) {
      const items = j.files.map(f => `
        <li class="flex items-center justify-between gap-3 py-1.5">
          <div class="truncate">
            <div class="font-medium text-slate-800 truncate">${escapeHtml(f.label)}</div>
            <div class="text-xs text-slate-500 truncate">${escapeHtml(f.path)}</div>
          </div>
          <a href="${escapeHtml(f.path)}" target="_blank"
             class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50">
            <i class="fa-regular fa-circle-down"></i> <span class="text-sm">Open</span>
          </a>
        </li>
      `).join('');
      filesHtml = `<ul class="divide-y divide-slate-100">${items}</ul>`;
    }
  } catch (e) {
    filesHtml = `<div class="text-xs text-rose-600">Failed to load files.</div>`;
  }

  openModal('Employee Details', `
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm mb-3">
      <div><div class="text-slate-500">Name</div><div class="font-medium">${escapeHtml(name)}</div></div>
      <div><div class="text-slate-500">Role</div><div class="font-medium">${escapeHtml(role)}</div></div>
      <div><div class="text-slate-500">Department</div><div class="font-medium">${escapeHtml(dept)}</div></div>
      <div><div class="text-slate-500">Status</div><div class="font-medium">${escapeHtml(status)}</div></div>
      <div class="sm:col-span-2"><div class="text-slate-500">Date Hired</div><div class="font-medium">${hire}</div></div>
    </div>

    <div class="mt-2">
      <div class="text-slate-600 font-semibold mb-2"><i class="fa-solid fa-paperclip text-rose-600 mr-2"></i>New-hire Files</div>
      <div class="rounded-lg border border-slate-200 p-3 bg-slate-50/50">
        ${filesHtml}
      </div>
    </div>
  `);
  return;
}


  if (btn.classList.contains('act-edit')) {
    openModal('Update Status', `
      <div class="space-y-3">
        <div class="text-sm text-slate-600">Employee</div>
        <div class="px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 text-sm font-medium">${escapeHtml(name)}</div>
        <div>
          <label class="text-sm text-slate-600">Status</label>
          <select id="newStatus" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200">
            ${['Active','On Leave','Probation','Inactive'].map(s=>`<option ${s===status?'selected':''}>${s}</option>`).join('')}
          </select>
        </div>
      </div>
    `,'Save', async ()=>{
      const ns = document.getElementById('newStatus').value;
      const res = await apiEmployees({ action:'update_status', id, status: ns });
      if (res && res.ok){
        tr.dataset.status = ns;
        const cell = tr.querySelector('td:nth-child(5)');
        cell.textContent = ns;
        cell.className = 'px-6 py-3 font-medium ' + (
          ns.toLowerCase()==='active' ? 'text-green-600' :
          ns.toLowerCase()==='on leave' ? 'text-orange-500' :
          ns.toLowerCase()==='probation' ? 'text-rose-600' : 'text-slate-700'
        );
        applyFilters();
      } else {
        alert(res.error || 'Failed to update status');
      }
    });
    return;
  }

  if (btn.classList.contains('act-archive')) {
    confirmBox(`Archive ${escapeHtml(name)}?`, 'Archive', async ()=>{
      const r = await apiEmployees({ action:'archive', id });
      if (r && r.ok){ tr.dataset.archived='1'; applyFilters(); }
      else { alert(r.error||'Archive failed'); }
    });
    return;
  }

  if (btn.classList.contains('act-unarchive')) {
    confirmBox(`Unarchive ${escapeHtml(name)}?`, 'Unarchive', async ()=>{
      const r = await apiEmployees({ action:'unarchive', id });
      if (r && r.ok){ tr.dataset.archived='0'; applyFilters(); }
      else { alert(r.error||'Unarchive failed'); }
    });
    return;
  }

  if (btn.classList.contains('act-delete')) {
    confirmBox('Delete this employee permanently?', 'Yes', async ()=>{
      const r = await apiEmployees({ action:'delete', id });
      if (r && r.ok){ tr.remove(); renumber(); }
      else { alert(r.error||'Delete failed'); }
    });
    return;
  }
});

// --- auto-search with debounce for the text field ---
function debounce(fn, wait=150){
  let t; return (...args)=>{ clearTimeout(t); t=setTimeout(()=>fn(...args), wait); };
}
const onText = debounce(applyFilters, 150);

if (q) {
  q.addEventListener('input', onText);
  q.addEventListener('keyup', onText);
}
// change fires agad sa selects
if (fRole)   fRole.addEventListener('change', applyFilters);
if (fStatus) fStatus.addEventListener('change', applyFilters);


/* ===== initial ===== */
function init(){ applyFilters(); }
init();
</script>
</body>
</html>
