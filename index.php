<?php
// ✅ Auth handles session_start and user loading
require_once __DIR__ . '/includes/auth.php';

// Redirect kapag employee user
if (role_is('employee')) {
  header('Location: employee_home.php');
  exit;
}

/* ---------- BRAND ---------- */
$brandName = 'Nextgenmms';
$brandLogo = 'assets/logo2.jpg';

// From auth.php → exposes $_SESSION['user'] and $ROLE
$u = $_SESSION['user'] ?? [];
$role = strtolower($ROLE ?? ($u['role'] ?? ''));
$isAdminHr = can(['admin','hr manager','superadmin']);

// ✅ No need for another session_start() after this point
require_once __DIR__ . '/includes/db.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
date_default_timezone_set('Asia/Manila');
try { $pdo->exec("SET time_zone = '+08:00'"); } catch (Throwable $e) {}


/* ---------- BRAND ---------- */
$brandName = 'Nextgenmms';
$brandLogo = 'assets/logo2.jpg';

/* ---------- HELPERS ---------- */
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
function name_expr(PDO $pdo, string $t): string {
  $single = pick_col($pdo,$t,[
    'hire_name','fullname','full_name','name',
    'employee_name','candidate_name','applicant_name'
  ]);
  if ($single) return "`$single`";
  $first = pick_col($pdo,$t,['firstname','first_name','given_name']);
  $last  = pick_col($pdo,$t,['lastname','last_name','surname','family_name']);
  if ($first && $last) return "TRIM(CONCAT(`$first`,' ',`$last`))";
  return "''";
}
function alias_name_expr(PDO $pdo, string $realTable, string $alias): string {
  $single = pick_col($pdo, $realTable, [
    'fullname','full_name','name','employee_name','candidate_name','applicant_name'
  ]);
  if ($single) return "`$alias`.`$single`";  // ✅ fixed: closed backtick + quote

  $first = pick_col($pdo, $realTable, ['firstname','first_name','given_name']);
  $last  = pick_col($pdo,  $realTable, ['lastname','last_name','surname','family_name']);
  if ($first && $last) return "TRIM(CONCAT(`$alias`.`$first`,' ',`$alias`.`$last`))";
  return "''";
}

function detect_pk(PDO $pdo, string $table): string {
  $singular = rtrim($table, 's');
  $cands = ['id', $table.'_id', $singular.'_id','applicant_id','candidate_id','employee_id','emp_id','user_id','person_id'];
  $pk = pick_col($pdo, $table, $cands);
  return $pk ?: 'id';
}
function joined_name_expr(PDO $pdo, string $baseTbl, ?string $fkApp, ?string $fkEmp,
                          ?string $T_APPS, ?string $T_EMPL, string &$joins): string {
  $joins = '';
  $localName = name_expr($pdo, $baseTbl);
  $joinName  = "NULL";
  if ($fkApp && $T_APPS) {
    $apk   = detect_pk($pdo, $T_APPS);
    $joins = "LEFT JOIN `$T_APPS` a ON `a`.`$apk` = `$baseTbl`.`$fkApp`";
    $joinName = alias_name_expr($pdo, $T_APPS, 'a');
  } elseif ($fkEmp && $T_EMPL) {
    $epk   = detect_pk($pdo, $T_EMPL);
    $joins = "LEFT JOIN `$T_EMPL` e ON `e`.`$epk` = `$baseTbl`.`$fkEmp`";
    $joinName = alias_name_expr($pdo, $T_EMPL, 'e');
  }
  return "COALESCE(NULLIF($localName,''), $joinName)";
}
function n(PDO $pdo, string $t, string $where='1'): int {
  if (!tbl_exists($pdo,$t)) return 0;
  try { return (int)$pdo->query("SELECT COUNT(*) FROM `$t` WHERE $where")->fetchColumn(); }
  catch(Throwable $e){ return 0; }
}
function rows(PDO $pdo, string $sql): array {
  try { return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC); }
  catch(Throwable $e){ return []; }
}

/* ---------- TABLES PRESENT ---------- */
$T_APPS   = tbl_exists($pdo,'applicants')         ? 'applicants'         : null;
$T_REQS   = tbl_exists($pdo,'requisitions')       ? 'requisitions'       : (tbl_exists($pdo,'recruitments') ? 'recruitments' : null);
$T_ON_T   = tbl_exists($pdo,'onboarding_tasks')   ? 'onboarding_tasks'   : null;
$T_ON_P   = tbl_exists($pdo,'onboarding_plans')   ? 'onboarding_plans'   : null;
$T_EMPL   = tbl_exists($pdo,'employees')          ? 'employees'          : (tbl_exists($pdo,'users') ? 'users' : null);
$T_EVAL   = tbl_exists($pdo,'evaluations')        ? 'evaluations'        : null;
$T_RVTK   = tbl_exists($pdo,'review_tasks')       ? 'review_tasks'       : null;
$T_RECO   = tbl_exists($pdo,'recognitions')       ? 'recognitions'       : null;
$T_INTRV  = tbl_exists($pdo,'interviews')         ? 'interviews'         : null;
$T_INTSC  = tbl_exists($pdo,'interview_schedules')? 'interview_schedules': null;
$T_OFFR   = tbl_exists($pdo,'offers')             ? 'offers'             : null;

/* ====================== Realtime pipeline (from applicants) ===================== */
function pipeline_from_applicants(PDO $pdo): array {
  if (!tbl_exists($pdo,'applicants')) {
    return ['Sourcing'=>0,'Screening'=>0,'Interview'=>0,'Offer'=>0,'Hired'=>0];
  }
  $stCol   = pick_col($pdo,'applicants',['status','stage']);
  if (!$stCol) {
    return ['Sourcing'=>0,'Screening'=>0,'Interview'=>0,'Offer'=>0,'Hired'=>0];
  }
  $archCol = pick_col($pdo,'applicants',['archived','is_archived']);
  $where = '1';
  if ($archCol) $where .= " AND COALESCE(`$archCol`,0)=0";
  $where .= " AND LOWER(COALESCE(`$stCol`,'')) <> 'archived'";
  $sql = "
    SELECT
      SUM(CASE WHEN LOWER(`$stCol`) IN ('new','pool','sourcing')          THEN 1 ELSE 0 END) AS sourcing,
      SUM(CASE WHEN LOWER(`$stCol`) IN ('screen','screening','shortlist') THEN 1 ELSE 0 END) AS screening,
      SUM(CASE WHEN LOWER(`$stCol`) IN ('interview','interviewing')       THEN 1 ELSE 0 END) AS interview,
      SUM(CASE WHEN LOWER(`$stCol`) IN ('offer','offered')                THEN 1 ELSE 0 END) AS offer_,
      SUM(CASE WHEN LOWER(`$stCol`) = 'hired'                              THEN 1 ELSE 0 END) AS hired
    FROM `applicants`
    WHERE $where
  ";
  try {
    $r = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC) ?: [];
    return [
      'Sourcing'  => (int)($r['sourcing']  ?? 0),
      'Screening' => (int)($r['screening'] ?? 0),
      'Interview' => (int)($r['interview'] ?? 0),
      'Offer'     => (int)($r['offer_']    ?? 0),
      'Hired'     => (int)($r['hired']     ?? 0),
    ];
  } catch (Throwable $e) {
    $rows = rows($pdo, "SELECT `$stCol` AS st".($archCol? ", `$archCol` AS ar" : ", 0 AS ar")." FROM `applicants`");
    $out = ['Sourcing'=>0,'Screening'=>0,'Interview'=>0,'Offer'=>0,'Hired'=>0];
    foreach ($rows as $x){
      if ((int)($x['ar']??0)) continue;
      $s = strtolower(trim((string)($x['st']??'')));
      if ($s==='archived') continue;
      if (in_array($s,['new','pool','sourcing']))              $out['Sourcing']++;
      elseif (in_array($s,['screen','screening','shortlist'])) $out['Screening']++;
      elseif (in_array($s,['interview','interviewing']))       $out['Interview']++;
      elseif (in_array($s,['offer','offered']))                $out['Offer']++;
      elseif ($s==='hired')                                    $out['Hired']++;
    }
    return $out;
  }
}

/* ---------------- Consistent Active Employees helpers ---------------- */
function emp_status_col(PDO $pdo): ?string {
  if (col_exists($pdo,'employees','status')) return 'status';
  if (col_exists($pdo,'employees','employment_status')) return 'employment_status';
  return null;
}
function where_not_archived(PDO $pdo): string {
  foreach (['archived','is_archived','deleted','is_deleted'] as $c) {
    if (col_exists($pdo,'employees',$c)) return "(`$c`=0 OR `$c` IS NULL)";
  }
  if (col_exists($pdo,'employees','is_active')) return "`is_active`=1";
  return "1=1";
}
function activeEmployeesCount(PDO $pdo): int {
  if (!tbl_exists($pdo,'employees')) return 0;
  $archWhere = where_not_archived($pdo);
  $sc = emp_status_col($pdo);
  if ($sc) {
    $sql = "SELECT COUNT(*) FROM employees WHERE $archWhere AND LOWER(`$sc`)='regular'";
    return (int)$pdo->query($sql)->fetchColumn();
  }
  $sql = "SELECT COUNT(*) FROM employees WHERE $archWhere";
  return (int)$pdo->query($sql)->fetchColumn();
}

/* ============================ KPI COUNTS ============================ */
$applicants_total = 0;
if ($T_APPS) {
  $arch = pick_col($pdo,$T_APPS,['archived','is_archived']);
  $st   = pick_col($pdo,$T_APPS,['status','stage']);
  if ($arch) {
    $applicants_total = n($pdo,$T_APPS,"COALESCE(`$arch`,0)=0");
  } elseif ($st) {
    $applicants_total = n($pdo,$T_APPS,"LOWER(COALESCE(`$st`,''))<>'archived'");
  } else {
    $applicants_total = n($pdo,$T_APPS);
  }
}

/* Open Recruitments */
$recruit_total = 0;
if ($T_REQS) {
  $stage = pick_col($pdo,$T_REQS,['stage','status','state']);
  if ($stage) {
    $recruit_total = n($pdo,$T_REQS,"LOWER(`$stage`) NOT IN ('closed','cancelled','canceled','filled','rejected','complete','completed')");
  } else {
    $recruit_total = n($pdo,$T_REQS);
  }
}

/* Onboarding Progress */
$onboarding_total = 0;
if ($T_ON_P) {
  $st = pick_col($pdo,$T_ON_P,['status','stage','state']);
  $onboarding_total = $st
    ? n($pdo,$T_ON_P,"LOWER(`$st`) NOT IN ('completed','closed','cancelled','canceled')")
    : n($pdo,$T_ON_P);
} elseif ($T_ON_T) {
  $st = pick_col($pdo,$T_ON_T,['status','stage','state']);
  $onboarding_total = $st
    ? n($pdo,$T_ON_T,"LOWER(`$st`) NOT IN ('completed','done','closed','cancelled','canceled')")
    : n($pdo,$T_ON_T);
}

/* Performance Reviews due in next 7 days */
$reviews_due = 0;
if ($T_EVAL) {
  $due = pick_col($pdo,$T_EVAL,['due_date','next_review_at','review_date']);
  $st  = pick_col($pdo,$T_EVAL,['status','state']);
  if ($due) {
    $cond = "`$due` IS NOT NULL AND `$due`>=CURDATE() AND `$due`<=DATE_ADD(CURDATE(),INTERVAL 7 DAY)";
    if ($st) $cond .= " AND LOWER(`$st`)<>'completed'";
    $reviews_due = n($pdo,$T_EVAL,$cond);
  }
} elseif ($T_RVTK) {
  $due = pick_col($pdo,$T_RVTK,['due_date','due_at','schedule_date']);
  $st  = pick_col($pdo,$T_RVTK,['status','state']);
  if ($due) {
    $cond = "`$due` IS NOT NULL AND `$due`>=CURDATE() AND `$due`<=DATE_ADD(CURDATE(),INTERVAL 7 DAY)";
    if ($st) $cond .= " AND LOWER(`$st`) NOT IN ('completed','done')";
    $reviews_due = n($pdo,$T_RVTK,$cond);
  }
}

/* Recognitions (MTD) */
$recognitions_total = 0;
if ($T_RECO){
  $dc = pick_col($pdo,$T_RECO,['created_at','date']);
  $recognitions_total = $dc
    ? n($pdo,$T_RECO,"`$dc` IS NOT NULL AND MONTH(`$dc`)=MONTH(CURDATE()) AND YEAR(`$dc`)=YEAR(CURDATE())")
    : n($pdo,$T_RECO);
}

/* Active Employees */
$employees_total = $T_EMPL ? activeEmployeesCount($pdo) : 0;

/* ============================ PIPELINE & LISTS ============================= */
$pipeline = pipeline_from_applicants($pdo);
unset($pipeline['Sourcing'], $pipeline['Offer']); // hide rows in widget only

/* Recent Applicants (6) */
$recentApplicants = [];
if ($T_APPS){
  $name = name_expr($pdo,$T_APPS);
  $role = pick_col($pdo,$T_APPS,['position','role','job_title','applied_position']);
  $st   = pick_col($pdo,$T_APPS,['stage','status']);
  $d    = pick_col($pdo,$T_APPS,['created_at','applied_at','added_at','date_created','submitted_at']);
  $roleSql = $role ? "`$role`" : "NULL";
  $stSql   = $st   ? "`$st`"   : "NULL";
  $dtSql   = $d    ? "`$d`"    : "NULL";
  $ord     = $d    ? "`$d` DESC" : "1";
  $recentApplicants = rows($pdo, "
    SELECT $name AS name, $roleSql AS role, $stSql AS stage, $dtSql AS dt
    FROM `$T_APPS`
    ORDER BY $ord
    LIMIT 6
  ");
}

/* Onboarding lists */
$newOnboarding = [];
if ($T_ON_P){
  $fkApp = pick_col($pdo,$T_ON_P,['applicant_id','candidate_id','applicant','app_id']);
  $fkEmp = pick_col($pdo,$T_ON_P,['employee_id','emp_id','user_id']);
  $rl    = pick_col($pdo,$T_ON_P,['position','role','job_title']);
  $st    = pick_col($pdo,$T_ON_P,['status','stage','state']);
  $sd    = pick_col($pdo,$T_ON_P,['start_date','start_at','date_start','created_at']);
  $loc   = pick_col($pdo,$T_ON_P,['site','location','branch','store']);
  $joins = '';
  $nameSelect = joined_name_expr($pdo, $T_ON_P, $fkApp, $fkEmp, $T_APPS, $T_EMPL, $joins);
  $rlSql = $rl  ? "`$T_ON_P`.`$rl`"  : "NULL";
  $stSql = $st  ? "`$T_ON_P`.`$st`"  : "NULL";
  $sdSql = $sd  ? "`$T_ON_P`.`$sd`"  : "NULL";
  $lcSql = $loc ? "`$T_ON_P`.`$loc`" : "NULL";
  $where = $st ? "WHERE LOWER($stSql) NOT IN ('completed','cancelled','closed')" : "";
  $order = $sd ? "ORDER BY $sdSql DESC" : "ORDER BY 1";
  $newOnboarding = rows($pdo, "
    SELECT $nameSelect AS name, $rlSql AS role, $lcSql AS site, $stSql AS status,
           $sdSql AS start_dt, NULL AS due_dt
    FROM `$T_ON_P`
    $joins
    $where
    $order
    LIMIT 5
  ");
}
elseif ($T_ON_T){
  $fkApp = pick_col($pdo,$T_ON_T,['applicant_id','candidate_id','applicant','app_id']);
  $fkEmp = pick_col($pdo,$T_ON_T,['employee_id','emp_id','user_id','assignee_id']);
  $rl    = pick_col($pdo,$T_ON_T,['position','role','job_title']);
  $st    = pick_col($pdo,$T_ON_T,['status','stage','state']);
  $dd    = pick_col($pdo,$T_ON_T,['due_date','due_at','target_date','deadline','schedule_date']);
  $loc   = pick_col($pdo,$T_ON_T,['site','location','branch','store']);
  $joins = '';
  $nameSelect = joined_name_expr($pdo, $T_ON_T, $fkApp, $fkEmp, $T_APPS, $T_EMPL, $joins);
  $rlSql = $rl  ? "`$T_ON_T`.`$rl`"  : "NULL";
  $stSql = $st  ? "`$T_ON_T`.`$st`"  : "NULL";
  $ddSql = $dd  ? "`$T_ON_T`.`$dd`"  : "NULL";
  $lcSql = $loc ? "`$T_ON_T`.`$loc`" : "NULL";
  $where = $st ? "WHERE LOWER($stSql) NOT IN ('completed','done','cancelled','closed')" : "";
  $order = $dd ? "ORDER BY $ddSql DESC"
               : (pick_col($pdo,$T_ON_T,['created_at']) ? "ORDER BY `$T_ON_T`.`created_at` DESC" : "ORDER BY 1");
  $newOnboarding = rows($pdo, "
    SELECT $nameSelect AS name, $rlSql AS role, $lcSql AS site, $stSql AS status,
           NULL AS start_dt, $ddSql AS due_dt
    FROM `$T_ON_T`
    $joins
    $where
    $order
    LIMIT 5
  ");
}

/* Initial Reviews list */
$initialReviews = [];
if ($T_EVAL && $T_EMPL) {
  $empPk   = detect_pk($pdo, $T_EMPL);
  $empName = name_expr($pdo, $T_EMPL);
  $hasRolesTbl = tbl_exists($pdo, 'roles');
  $joinRoles   = ($hasRolesTbl && col_exists($pdo,'employees','role_id'))
               ? "LEFT JOIN roles r ON r.id = e.role_id" : "";
  $roleCand = [];
  if ($hasRolesTbl) foreach (['name','title','role'] as $c) if (col_exists($pdo,'roles',$c)) $roleCand[] = "r.`$c`";
  foreach (['role','position','job_title','department','dept'] as $c) if (col_exists($pdo,$T_EMPL,$c)) $roleCand[] = "e.`$c`";
  foreach (['role','position'] as $c) if (col_exists($pdo,$T_EVAL,$c)) $roleCand[] = "ev.`$c`";
  $roleExpr = $roleCand ? ('COALESCE('.implode(',', $roleCand).")") : "NULL";
  $dueCol  = pick_col($pdo,$T_EVAL,['due_date']) ?: 'due_date';
  $statCol = pick_col($pdo,$T_EVAL,['status','state','stage']) ?: 'status';
  $updCol  = pick_col($pdo,$T_EVAL,['updated_at','modified_at']) ?: 'updated_at';
  $perCol  = pick_col($pdo,$T_EVAL,['period','type']) ?: 'period';
  $periodCond = "
    LOWER(ev.`$perCol`) IN ('first-30-days','first-60-days','first-90-days',
                             'initial 30 day','initial 60 day','initial 90 day')
  ";
  $pending = rows($pdo, "
    SELECT $empName AS name, $roleExpr AS role, ev.`$dueCol` AS dt
    FROM `$T_EVAL` ev
    JOIN `$T_EMPL` e ON e.`$empPk` = ev.employee_id
    $joinRoles
    WHERE $periodCond AND ev.`$dueCol` IS NOT NULL AND LOWER(ev.`$statCol`) <> 'completed'
    ORDER BY ev.`$dueCol` ASC
    LIMIT 6
  ");
  $initialReviews = $pending;
  if (count($initialReviews) < 6) {
    $need = 6 - count($initialReviews);
    $recentDone = rows($pdo, "
      SELECT $empName AS name, $roleExpr AS role, ev.`$dueCol` AS dt
      FROM `$T_EVAL` ev
      JOIN `$T_EMPL` e ON e.`$empPk` = ev.employee_id
      $joinRoles
      WHERE $periodCond AND LOWER(ev.`$statCol`) = 'completed'
        AND COALESCE(ev.`$updCol`, ev.`$dueCol`) >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
      ORDER BY COALESCE(ev.`$updCol`, ev.`$dueCol`) DESC
      LIMIT $need
    ");
    $initialReviews = array_merge($initialReviews, $recentDone);
  }
  $initialReviews = array_map(function($r){
    $nm = trim((string)($r['name'] ?? ''));
    $rl = trim((string)($r['role'] ?? ''));
    return ['name' => $nm !== '' ? $nm : '—','role' => $rl !== '' ? $rl : '—','dt' => $r['dt'] ?? null];
  }, $initialReviews);
} elseif ($T_RVTK) {
  $nm = pick_col($pdo,$T_RVTK,['employee_name','assignee','name']);
  $rl = pick_col($pdo,$T_RVTK,['role','position','job_title','department','dept']);
  $dt = pick_col($pdo,$T_RVTK,['due_date','due_at','schedule_date']);
  $nmSql = $nm ? "`$nm`" : "''";
  $rlSql = $rl ? "`$rl`" : "NULL";
  $dtSql = $dt ? "`$dt`" : "NULL";
  $initialReviews = rows($pdo, "
    SELECT $nmSql AS name, $rlSql AS role, $dtSql AS dt
    FROM `$T_RVTK`
    WHERE $dtSql IS NOT NULL
    ORDER BY $dtSql ASC
    LIMIT 6
  ");
}

/* ---------- UI helper ---------- */
function isActive($page) {
  $is = basename($_SERVER['PHP_SELF']) === $page;
  return $is ? 'bg-rose-900/60 text-rose-500'
             : 'text-slate-300 hover:text-rose-500 hover:bg-rose-900/40';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard | <?= htmlspecialchars($brandName) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    /* --- Layout that really collapses --- */
    #sidebar{width:16rem} #sidebar.collapsed{width:4rem}
    #sidebar .nav-item{padding:.6rem .85rem}
    #sidebar.collapsed .nav-item{justify-content:center;padding:.6rem 0}
    #sidebar.collapsed .item-label, #sidebar.collapsed .section-title{display:none}

    #contentWrap{padding-left:16rem;transition:padding .25s ease}
    #contentWrap.collapsed{padding-left:4rem}

    /* fix: topbar shifts with sidebar */
    #topbarPad{margin-left:16rem;transition:margin .25s ease}
    #topbarPad.collapsed{margin-left:4rem}

    #sidebar{scrollbar-width:none;-ms-overflow-style:none} #sidebar::-webkit-scrollbar{display:none}
    .group:hover { box-shadow: 0 8px 20px -4px rgba(225,29,72,0.2); }
    @keyframes fadeIn { from{opacity:0;transform:translateY(-6px);} to{opacity:1;transform:translateY(0);} }
    .animate-fadeIn { animation: fadeIn .15s ease-out forwards; }
  </style>
</head>
<body class="bg-slate-50">

<header class="sticky top-0 z-40">
  <div id="topbarPad" class="bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="h-14 px-3 md:px-4 flex items-center gap-3">
      <!-- Hamburger -->
     <button id="btnSidebar"
        class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-rose-500 text-white hover:bg-rose-600 shrink-0 -mt-px">
        <i class="fa-solid fa-bars"></i>
      </button>


      <!-- Brand / Title spacer (no search) -->
      <div class="flex items-center gap-2 text-slate-700 font-medium -mt-px">
        <span class="hidden sm:inline">Dashboard</span>
      </div>

      <!-- push user menu to the right -->
      <div class="flex-1"></div>

      <!-- USER DROPDOWN -->
      <div class="relative ml-1 select-none">
        <button id="userBtn"
          class="flex items-center gap-2 px-2.5 py-1.5 rounded-xl bg-white ring-1 ring-slate-200 shadow hover:bg-rose-50 transition duration-150">
          <div class="w-8 h-8 rounded-md bg-gradient-to-br from-rose-500 to-rose-600 text-white grid place-items-center text-xs font-semibold shadow-inner">
            <?= strtoupper(substr($u['name'] ?? 'U',0,2)); ?>
          </div>
          <div class="leading-tight pr-1 text-left hidden sm:block">
            <div class="text-sm font-medium text-slate-800 truncate max-w-[120px]">
              <?= htmlspecialchars($u['name'] ?? 'User'); ?>
            </div>
            <div class="text-[11px] text-slate-500 capitalize">
              <?= htmlspecialchars($u['role'] ?? ''); ?>
            </div>
          </div>
          <i class="fa-solid fa-chevron-down text-slate-400 text-xs"></i>
        </button>

        <div id="userMenu"
          class="hidden absolute right-0 mt-2 w-44 bg-white rounded-2xl shadow-xl border border-slate-200 ring-1 ring-rose-100 overflow-hidden z-50">
          <div class="py-1">
            <a href="profile.php"
              class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-gradient-to-r hover:from-rose-50 hover:to-rose-100 hover:text-rose-600 transition duration-150">
              <i class="fa-solid fa-user text-rose-500 w-4"></i>
              View Profile
            </a>
            <form method="post" action="logout.php">
              <button type="submit"
                class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-gradient-to-r hover:from-rose-50 hover:to-rose-100 hover:text-rose-600 transition duration-150">
                <i class="fa-solid fa-right-from-bracket text-rose-500 w-4"></i>
                Log Out
              </button>
            </form>
          </div>
        </div>
      </div>
      <!-- /USER DROPDOWN -->
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

    <a href="index.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('index.php'); ?>">
      <i class="fa-solid fa-house"></i><span class="item-label font-medium">Dashboard</span>
    </a>

    <?php if (role_is(['employee'])): ?>
      <a href="employee_home.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('employee_home.php'); ?>">
        <i class="fa-solid fa-id-badge"></i><span class="item-label">Employee</span>
      </a>
    <?php endif; ?>

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
  </nav>

  </aside>

  <!-- CONTENT -->
  <main id="contentWrap" class="min-h-[calc(100vh-56px)] transition-all duration-200">
    <div class="px-4 sm:px-6 lg:px-8 py-6">

      <!-- KPIs -->
      <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 md:gap-4 mb-6 text-center">
        <?php
        $kpis = [
          ['icon'=>'fa-id-badge','label'=>'Applicant Overview','value'=>$applicants_total],
          ['icon'=>'fa-briefcase','label'=>'Open Recruitment','value'=>$recruit_total],
          ['icon'=>'fa-user-plus','label'=>'Onboarding Progress','value'=>$onboarding_total],
          ['icon'=>'fa-chart-line','label'=>'Performance Reviews','value'=>$reviews_due],
          ['icon'=>'fa-award','label'=>'Employee Recognitions','value'=>$recognitions_total],
          ['icon'=>'fa-users','label'=>'Active Employees','value'=>$employees_total],
        ];
        foreach ($kpis as $k):
        ?>
        <div class="group relative rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm hover:shadow-lg p-5 flex flex-col items-center justify-center transition-all duration-300 hover:-translate-y-1 overflow-hidden min-h-[140px]">
          <div class="absolute inset-0 bg-gradient-to-b from-rose-50/60 to-transparent opacity-0 group-hover:opacity-100 transition duration-300"></div>
          <div class="w-10 h-10 mb-3 rounded-xl bg-gradient-to-br from-rose-100 to-rose-200 text-rose-600 grid place-items-center shadow-inner">
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
        <!-- Recent Applicants -->
        <div class="xl:col-span-2 rounded-2xl bg-white ring-1 ring-slate-200">
          <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">Recent Applicants</h2>
            <a href="applicants.php" class="text-xs text-rose-600 hover:underline">View all</a>
          </div>
          <div class="divide-y divide-slate-200">
            <?php if ($recentApplicants): foreach ($recentApplicants as $a): ?>
              <?php
                $role  = trim((string)($a['role'] ?? ''));
                $stage = trim((string)($a['stage'] ?? ''));
                $stage = $stage !== '' ? ucfirst(strtolower($stage)) : '';
                $meta  = ($role !== '' || $stage !== '')
                        ? ($role . ($role && $stage ? ' • ' : '') . $stage)
                        : '—';
              ?>
              <div class="px-4 py-3 flex items-center justify-between">
                <div class="min-w-0">
                  <div class="font-medium text-slate-800 truncate"><?= htmlspecialchars($a['name'] ?? '—') ?></div>
                  <div class="text-xs text-slate-500 truncate"><?= htmlspecialchars($meta) ?></div>
                </div>
                <div class="text-xs text-slate-500 shrink-0">
                  <?= !empty($a['dt']) ? date('M d, Y', strtotime($a['dt'])) : '' ?>
                </div>
              </div>
            <?php endforeach; else: ?>
              <div class="px-4 py-8 text-center text-slate-500 text-sm">No applicants yet.</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Recruitment Pipeline -->
        <div class="rounded-2xl bg-white ring-1 ring-slate-200">
          <div class="px-4 py-3 border-b border-slate-200"><h2 class="font-semibold text-slate-800">Recruitment Pipeline</h2></div>
          <div class="p-4 space-y-3">
            <?php foreach ($pipeline as $label=>$val): $w = min(100, ($val>0 ? 10*$val : 2)); ?>
              <div>
                <div class="flex items-center justify-between text-sm">
                  <span class="text-slate-700"><?= htmlspecialchars($label) ?></span>
                  <span class="font-medium text-slate-800"><?= (int)$val ?></span>
                </div>
                <div class="h-2 rounded-full bg-slate-200 overflow-hidden mt-1">
                  <div class="h-full bg-rose-500" style="width: <?= $w ?>%"></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- GRID 2 -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-6">
        <!-- Onboarding -->
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 xl:col-span-2">
          <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">New Hire Onboarding Progress</h2>
            <a href="onboarding.php" class="text-xs text-rose-600 hover:underline">Open module</a>
          </div>
          <div class="divide-y divide-slate-200">
            <?php if ($newOnboarding): foreach ($newOnboarding as $t): ?>
              <?php
                $nm     = trim((string)($t['name'] ?? ''));
                $title  = $nm !== '' ? $nm : '—';
                $role   = trim((string)($t['role'] ?? ''));
                $status = trim((string)($t['status'] ?? ''));
                $status = $status !== '' ? ucfirst(strtolower($status)) : '';
                $metaParts = array_filter([$role, $status], fn($v)=>$v!=='');
                $meta  = $metaParts ? implode(' • ', $metaParts) : '—';
                $dateVal = !empty($t['start_dt']) ? $t['start_dt'] : (!empty($t['due_dt']) ? $t['due_dt'] : '');
              ?>
              <div class="px-4 py-3">
                <div class="flex items-center justify-between">
                  <div class="min-w-0">
                    <div class="font-medium text-slate-800 truncate"><?= htmlspecialchars($title) ?></div>
                    <div class="text-xs text-slate-500 truncate"><?= htmlspecialchars($meta) ?></div>
                  </div>
                  <div class="text-xs text-rose-600 font-medium shrink-0">
                    <?= $dateVal ? date('M d', strtotime($dateVal)) : '' ?>
                  </div>
                </div>
              </div>
            <?php endforeach; else: ?>
              <div class="px-4 py-8 text-center text-slate-500 text-sm">No onboarding items yet.</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Performance Reviews -->
        <div class="rounded-2xl bg-white ring-1 ring-slate-200">
          <div class="px-4 py-3 border-b border-slate-200">
            <h2 class="font-semibold text-slate-800">Initial Performance Reviews</h2>
          </div>
          <div class="divide-y divide-slate-200">
            <?php if ($initialReviews): foreach ($initialReviews as $r): ?>
              <div class="px-4 py-3">
                <div class="flex items-center justify-between">
                  <div class="min-w-0">
                    <div class="font-medium text-slate-800 truncate"><?= htmlspecialchars($r['name'] ?? '—') ?></div>
                    <div class="text-xs text-slate-500 truncate"><?= htmlspecialchars($r['role'] ?? '—') ?></div>
                  </div>
                  <div class="text-xs text-slate-500 shrink-0"><?= !empty($r['dt']) ? date('M d', strtotime($r['dt'])) : '' ?></div>
                </div>
              </div>
            <?php endforeach; else: ?>
              <div class="px-4 py-8 text-center text-slate-500 text-sm">No scheduled initial reviews.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- ==== SCRIPTS (hamburger + search fixed) ==== -->
<script>
const userBtn  = document.getElementById('userBtn');
const userMenu = document.getElementById('userMenu');
const sb       = document.getElementById('sidebar');
const btn      = document.getElementById('btnSidebar');
const content  = document.getElementById('contentWrap');
const topbar   = document.getElementById('topbarPad');

// User menu
userBtn?.addEventListener('click', e => {
  e.stopPropagation();
  userMenu.classList.toggle('hidden');
  userMenu.classList.toggle('animate-fadeIn');
});
window.addEventListener('click', e => {
  if (userMenu && !userMenu.contains(e.target) && !userBtn.contains(e.target)) {
    userMenu.classList.add('hidden');
  }
});

// Sidebar toggle
function applyShift(){
  const collapsed = sb.classList.contains('collapsed');
  content.classList.toggle('collapsed', collapsed);
  topbar.classList.toggle('collapsed', collapsed);
}
btn?.addEventListener('click', () => {
  sb.classList.toggle('collapsed');
  localStorage.setItem('sb-collapsed', sb.classList.contains('collapsed') ? '1' : '0');
  applyShift();
});
// restore state
if (localStorage.getItem('sb-collapsed') === '1') { sb.classList.add('collapsed'); }
applyShift();

/* -------- Global search -------- */
function routeSearch(qRaw){
  const q = (qRaw || '').trim(); if (!q) return;
  const lower = q.toLowerCase();
  const go = p => window.location.href = `${p}?search=${encodeURIComponent(q)}`;
  if (lower.includes('applicant')) return go('applicants.php');
  if (lower.includes('recruit') || lower.includes('requisition')) return go('recruitment.php');
  if (lower.includes('onboard')) return go('onboarding.php');
  if (lower.includes('employee') || lower.includes('staff')) return go('employees.php');
  if (lower.includes('review') || lower.includes('evaluation')) return go('evaluations.php');
  if (lower.includes('recognition') || lower.includes('award')) return go('recognition.php');
  return go('applicants.php'); // default
}

// Enter to search
searchBox?.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') routeSearch(searchBox.value);
  if (e.key === 'Escape') { searchBox.value=''; searchClr?.classList.add('hidden'); }
});

// Click magnifier to search
searchIcon?.addEventListener('click', () => routeSearch(searchBox.value));

// Show/clear button
searchBox?.addEventListener('input', () => {
  if (searchBox.value.trim()) searchClr.classList.remove('hidden');
  else searchClr.classList.add('hidden');
});
searchClr?.addEventListener('click', () => {
  searchBox.value=''; searchBox.focus(); searchClr.classList.add('hidden');
});

// Optional: auto-route after a short pause (300ms)
let t=null;
searchBox?.addEventListener('keyup', () => {
  clearTimeout(t);
  const v = searchBox.value.trim();
  if (!v) return;
  t = setTimeout(()=>routeSearch(v), 300);
});
</script>
</body>
</html>
