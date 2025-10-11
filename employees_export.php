<?php
// employees_export.php
session_start();
if (empty($_SESSION['user'])) { http_response_code(403); exit('Forbidden'); }

require_once __DIR__ . '/includes/db.php';
date_default_timezone_set('Asia/Manila');
try { $pdo->exec("SET time_zone = '+08:00'"); } catch (Throwable $e) {}

// ---- Dompdf (Composer) ----
require_once __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;

// helpers
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
function display_status_label($raw) {
  return match (strtolower((string)$raw)) {
    'regular','active' => 'Regular',
    'probation'        => 'Probation',
    'inactive'         => 'Inactive',
    'on leave','leave' => 'On Leave',
    default            => ucfirst(trim((string)$raw)) ?: 'Regular',
  };
}
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// ---- Load employees (same mapping logic) ----
$employees = [];

if (table_exists($pdo,'employees')) {
  $idCol   = pickcol($pdo,'employees',['id','emp_id']);
  $nameCol = pickcol($pdo,'employees',['name','full_name','employee_name']);
  $roleCol = pickcol($pdo,'employees',['role','position','job_title']);
  $deptCol = pickcol($pdo,'employees',['department','dept']);
  $statCol = pickcol($pdo,'employees',['status','employment_status']);
  $dateCol = pickcol($pdo,'employees',['date_hired','hired_at','start_date','created_at']);

  if ($idCol && $nameCol) {
    $select = "`$idCol` AS id, `$nameCol` AS name"
            . ($roleCol ? ", `$roleCol` AS role" : ", NULL AS role")
            . ($deptCol ? ", `$deptCol` AS dept" : ", NULL AS dept")
            . ($statCol ? ", `$statCol` AS status" : ", 'Regular' AS status")
            . ($dateCol ? ", `$dateCol` AS hire" : ", NULL AS hire");
    $order = $dateCol ? "ORDER BY `$dateCol` ASC, `$idCol` ASC" : "ORDER BY `$idCol` ASC";
    $employees = $pdo->query("SELECT $select FROM employees $order")->fetchAll(PDO::FETCH_ASSOC);
  }
}

/* Fallback to hired applicants if employees is empty */
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
            . ", 'Regular' AS status"
            . ($dateCol ? ", `$dateCol` AS hire" : ", NOW() AS hire");
    $order = $dateCol ? "ORDER BY `$dateCol` ASC, `$idCol` ASC" : "ORDER BY `$idCol` ASC";
    $employees = $pdo->query("SELECT $select FROM applicants WHERE LOWER(TRIM(status))='hired' $order")->fetchAll(PDO::FETCH_ASSOC);
  }
}

// ---- Apply realtime filters from querystring ----
$q      = strtolower(trim($_GET['q']     ?? ''));
$fRole  = strtolower(trim($_GET['role']  ?? ''));
$fStat  = strtolower(trim($_GET['status']?? ''));

// filter + drop blanks + normalize
$filtered = [];
foreach ($employees as $e) {
  $name = trim((string)($e['name'] ?? ''));
  if ($name === '') continue; // << remove blank rows (fixes gaps)

  $role = (string)($e['role'] ?? '');
  $dept = (string)($e['dept'] ?? 'Operations');
  $statusLabel = display_status_label($e['status'] ?? 'Regular');

  $hay = strtolower($name.' '.$role.' '.$dept);
  $okQ = ($q === '' || str_contains($hay, $q));
  $okR = ($fRole === '' || strtolower($role) === $fRole);
  $okS = ($fStat === '' || strtolower($statusLabel) === $fStat);

  if ($okQ && $okR && $okS) {
    $filtered[] = [
      'name'   => $name,
      'role'   => $role ?: '—',
      'dept'   => $dept ?: '—',
      'status' => $statusLabel,
      'hire'   => !empty($e['hire']) ? date('M d, Y', strtotime($e['hire'])) : '—',
    ];
  }
}

// ---- Build HTML (renumber without gaps) ----
$now = date('M d, Y h:i A');
ob_start(); ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Employees Report</title>
<style>
  @page { margin: 22mm 18mm; }
  body { font-family: DejaVu Sans, Arial, sans-serif; color:#111827; font-size:12px; }
  h1 { color:#e11d48; margin:0; font-size:20px; }
  .muted { color:#6b7280; font-size:11px; }
  table { width:100%; border-collapse:collapse; margin-top:14px; }
  th, td { border:1px solid #e5e7eb; padding:8px 10px; }
  th { background:#f3f4f6; color:#475569; text-transform:uppercase; font-size:11px; letter-spacing:.03em; }
  td { vertical-align:middle; }
  .num { text-align:center; width:38px; }
  .status { font-weight:600; }
  .s-regular { color:#10b981; }
  .s-probation { color:#ef4444; }
  .s-inactive { color:#6b7280; }
  .s-on\ leave { color:#f59e0b; }
  .footer { margin-top:12px; color:#9ca3af; font-size:10px; }
</style>
</head>
<body>
  <h1>Employees Report</h1>
  <div class="muted">Generated: <?= h($now) ?></div>

  <table>
    <thead>
      <tr>
        <th class="num">#</th>
        <th>Name</th>
        <th>Role</th>
        <th>Department</th>
        <th>Status</th>
        <th>Date Hired</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$filtered): ?>
        <tr><td colspan="6" style="text-align:center; padding:18px; color:#6b7280;">No matching employees.</td></tr>
      <?php else:
        $n = 1;
        foreach ($filtered as $row):
          $st = strtolower($row['status']);
          $stClass = 's-regular';
          if ($st === 'probation') $stClass='s-probation';
          elseif ($st === 'inactive') $stClass='s-inactive';
          elseif ($st === 'on leave') $stClass='s-on leave';
      ?>
        <tr>
          <td class="num"><?= $n++ ?></td>
          <td><?= h($row['name']) ?></td>
          <td><?= h($row['role'] ?: '—') ?></td>
          <td><?= h($row['dept'] ?: '—') ?></td>
          <td class="status <?= $stClass ?>"><?= h($row['status']) ?></td>
          <td><?= h($row['hire']) ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>

  <div class="footer">HR1 MerchFlow</div>
</body>
</html>
<?php
$html = ob_get_clean();

// ---- Render with Dompdf ----
$dompdf = new Dompdf(['isRemoteEnabled'=>true]);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('employees.pdf', ['Attachment' => false]); // open in browser
