<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

session_start();
if (empty($_SESSION['user'])) { echo json_encode(['ok'=>false,'error'=>'unauthorized']); exit; }

require_once __DIR__ . '/../includes/db.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
date_default_timezone_set('Asia/Manila');
try { $pdo->exec("SET time_zone = '+08:00'"); } catch (Throwable $e) {}

/* =======================================================
   GENERIC HELPERS
   ======================================================= */
function ok($d = []) { echo json_encode(['ok'=>true,'data'=>$d]); exit; }
function bad($m, $c=200){ http_response_code($c); echo json_encode(['ok'=>false,'error'=>$m]); exit; }

$in  = json_decode(file_get_contents('php://input') ?: '[]', true) ?: [];
$act = strtolower((string)($in['action'] ?? ''));

function table_exists(PDO $pdo, string $t): bool {
  try { return (bool)$pdo->query("SHOW TABLES LIKE ".$pdo->quote($t))->fetch(); } catch(Throwable $e){ return false; }
}
function col_exists(PDO $pdo, string $t, string $c): bool {
  try { $st=$pdo->prepare("SHOW COLUMNS FROM `$t` LIKE ?"); $st->execute([$c]); return (bool)$st->fetch(); } catch(Throwable $e){ return false; }
}
function first_existing_col(PDO $pdo, string $table, array $candidates): string {
  foreach ($candidates as $c) if (col_exists($pdo,$table,$c)) return $c;
  return '';
}
function pick(array $r, array $keys, $def=''){
  foreach ($keys as $k) if (array_key_exists($k,$r) && $r[$k]!==null && !(is_string($r[$k]) && trim((string)$r[$k])==='')) return $r[$k];
  return $def;
}
function enum_allowed(PDO $pdo, string $table, string $col): array {
  $st = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
  $st->execute([$col]);
  $meta = $st->fetch(PDO::FETCH_ASSOC) ?: [];
  if (!empty($meta['Type']) && stripos($meta['Type'],'enum(')===0) {
    if (preg_match_all("/'([^']*)'/", $meta['Type'], $m)) return $m[1];
  }
  return [];
}

/* ---------- status helpers ---------- */
function get_status_col(PDO $pdo): ?string {
  if (col_exists($pdo,'employees','status')) return 'status';
  if (col_exists($pdo,'employees','employment_status')) return 'employment_status';
  return null;
}
function normalize_status_for_employees(PDO $pdo, string $ui): string {
  $map = [
    'active'    => 'regular',
    'on leave'  => 'inactive',
    'probation' => 'probation',
    'inactive'  => 'inactive',
    'regular'   => 'regular'
  ];
  $statusCol = get_status_col($pdo);
  $target = $map[strtolower($ui)] ?? strtolower($ui);
  if (!$statusCol) return $target;
  $allowed = enum_allowed($pdo,'employees',$statusCol);
  if (!$allowed) return $target;
  foreach ($allowed as $opt) if (strcasecmp($opt, $target)===0) return $opt;
  return $allowed[0];
}

/* ---------- archive helpers ---------- */
function get_archive_col(PDO $pdo): ?string {
  foreach (['archived','is_archived','deleted','is_deleted','is_active'] as $c) {
    if (col_exists($pdo,'employees',$c)) return $c;
  }
  return null;
}
function set_archived(PDO $pdo, int $id, bool $archived): void {
  $pk = 'id';
  $col = get_archive_col($pdo);
  if ($col) {
    if ($col === 'is_active') {
      $st = $pdo->prepare("UPDATE employees SET `is_active`=?".(col_exists($pdo,'employees','updated_at')?", updated_at=NOW()":"")." WHERE `$pk`=?");
      $st->execute([$archived?0:1, $id]);
    } else {
      $st = $pdo->prepare("UPDATE employees SET `$col`=?".(col_exists($pdo,'employees','updated_at')?", updated_at=NOW()":"")." WHERE `$pk`=?");
      $st->execute([$archived?1:0, $id]);
    }
    return;
  }
  $statusCol = get_status_col($pdo);
  if ($statusCol) {
    $val = $archived ? normalize_status_for_employees($pdo,'inactive')
                     : normalize_status_for_employees($pdo,'active');
    $st = $pdo->prepare("UPDATE employees SET `$statusCol`=?".(col_exists($pdo,'employees','updated_at')?", updated_at=NOW()":"")." WHERE `$pk`=?");
    $st->execute([$val,$id]);
    return;
  }
  throw new RuntimeException('No archive/status column available');
}
function where_archived(PDO $pdo, bool $archived): string {
  $col = get_archive_col($pdo);
  if ($col === 'is_active') return $archived ? "`is_active`=0" : "`is_active`=1";
  if ($col) return $archived ? "`$col`=1" : "(`$col`=0 OR `$col` IS NULL)";
  $statusCol = get_status_col($pdo);
  if ($statusCol) {
    return $archived
      ? "LOWER(`$statusCol`) IN ('inactive')"
      : "LOWER(`$statusCol`) NOT IN ('inactive')";
  }
  return "1";
}

/* ---------- ensure minimal employees table (NO schema override) ---------- */
function ensure_employees_table(PDO $pdo): void {
  if (!table_exists($pdo,'employees')) {
    $pdo->exec("
      CREATE TABLE `employees` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `applicant_id` INT NULL,
        `name` VARCHAR(150) NOT NULL,
        `role` VARCHAR(120) NULL,
        `department` VARCHAR(120) DEFAULT 'Operations',
        `status` ENUM('probation','regular','inactive') DEFAULT 'probation',
        `date_hired` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_applicant` (`applicant_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
  }
}

/* =======================================================
   AUTO-ADD EMPLOYEE FROM HIRED APPLICANT (dynamic cols)
   ======================================================= */
function insert_or_update_employee_from_applicant(PDO $pdo, int $applicantId): void {
  try {
    $stmt = $pdo->prepare("SELECT * FROM applicants WHERE id=? LIMIT 1");
    $stmt->execute([$applicantId]);
    $a = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$a) {
      file_put_contents(__DIR__.'/../debug.log', "❌ Applicant not found ($applicantId)\n", FILE_APPEND);
      return;
    }

    $name   = $a['full_name'] ?? ($a['name'] ?? '');
    $role   = $a['role'] ?? '';
    $dept   = $a['department'] ?? ($a['dept'] ?? 'Operations');
    $emailV = $a['email'] ?? ($a['company_email'] ?? ($a['work_email'] ?? ''));
    $status = 'Active';
    $dateHired = date('Y-m-d H:i:s');

    ensure_employees_table($pdo);

    $emailCol = first_existing_col($pdo,'employees',['email','company_email','work_email','email_address']);
    $deptCol  = first_existing_col($pdo,'employees',['department','dept','site','department_name']);
    $dhCol    = first_existing_col($pdo,'employees',['date_hired','hired_at','hired_date','employment_date','date_started','start_date']);

    // UPDATE if exists
    $chk = $pdo->prepare("SELECT id FROM employees WHERE applicant_id=?");
    $chk->execute([$applicantId]);
    $exists = $chk->fetchColumn();

    if ($exists) {
      $sets = ["name = :name", "role = :role"];
      $bind = [':name'=>$name, ':role'=>$role, ':aid'=>$applicantId];

      if ($deptCol) { $sets[] = "$deptCol = :dept"; $bind[':dept'] = $dept; }
      if ($emailCol && $emailV !== '') { $sets[] = "$emailCol = :email"; $bind[':email'] = $emailV; }

      $stCol = get_status_col($pdo);
      if ($stCol) { $sets[] = "$stCol = :status"; $bind[':status'] = $status; }

      if ($dhCol) { $sets[] = "$dhCol = :dh"; $bind[':dh'] = $dateHired; }

      if (col_exists($pdo,'employees','updated_at')) $sets[] = "updated_at = NOW()";

      $sql = "UPDATE employees SET ".implode(',',$sets)." WHERE applicant_id = :aid";
      $pdo->prepare($sql)->execute($bind);

      file_put_contents(__DIR__.'/../debug.log', "✅ Updated employee for applicant #$applicantId\n", FILE_APPEND);
    } else {
      $cols = ['applicant_id','name','role'];
      $vals = [':aid',':name',':role'];
      $bind = [':aid'=>$applicantId, ':name'=>$name, ':role'=>$role];

      if ($deptCol) { $cols[]=$deptCol; $vals[]=':dept'; $bind[':dept']=$dept; }
      $stCol = get_status_col($pdo);
      if ($stCol) { $cols[]=$stCol; $vals[]=':status'; $bind[':status']=$status; }
      if ($dhCol) { $cols[]=$dhCol; $vals[]=':dh'; $bind[':dh']=$dateHired; }
      if ($emailCol && $emailV !== '') { $cols[]=$emailCol; $vals[]=':email'; $bind[':email']=$emailV; }

      if (col_exists($pdo,'employees','created_at')) { $cols[]='created_at'; $vals[]='NOW()'; }
      if (col_exists($pdo,'employees','updated_at')) { $cols[]='updated_at'; $vals[]='NOW()'; }

      $sql = "INSERT INTO employees (".implode(',',$cols).") VALUES (".implode(',',$vals).")";
      $pdo->prepare($sql)->execute($bind);

      file_put_contents(__DIR__.'/../debug.log', "✅ Added new employee for applicant #$applicantId\n", FILE_APPEND);
    }
  } catch (Throwable $e) {
    file_put_contents(__DIR__.'/../debug.log', "❌ Employee insert error: ".$e->getMessage()."\n", FILE_APPEND);
  }
}

/* ================== ACTIONS ================== */

if ($act === 'sync_from_applicant') {
  $appId = (int)($in['applicant_id'] ?? 0);
  if ($appId <= 0) bad('missing applicant_id');
  insert_or_update_employee_from_applicant($pdo, $appId);
  ok(['synced'=>true,'applicant_id'=>$appId]);
}

if ($act === 'update_status') {
  ensure_employees_table($pdo);
  $id = (int)($in['id'] ?? 0);
  $statusIn = trim((string)($in['status'] ?? ''));
  if ($id <= 0 || $statusIn === '') bad('missing id/status');

  $statusCol = get_status_col($pdo);
  if (!$statusCol) bad('no status column in employees table');

  $candidate = normalize_status_for_employees($pdo, $statusIn);

  $sql = "UPDATE `employees` SET `$statusCol` = ?"
       . (col_exists($pdo,'employees','updated_at') ? ", `updated_at` = NOW()" : "")
       . " WHERE `id` = ?";
  $pdo->prepare($sql)->execute([$candidate, $id]);

  $row = $pdo->prepare("SELECT `id`, `$statusCol` AS status FROM `employees` WHERE `id`=?");
  $row->execute([$id]);
  ok(['updated'=>true,'saved'=>$row->fetch(PDO::FETCH_ASSOC)]);
}

if ($act === 'archive') {
  ensure_employees_table($pdo);
  $id = (int)($in['id'] ?? 0);
  if ($id <= 0) bad('missing id');
  set_archived($pdo,$id,true);
  ok(['archived'=>true,'id'=>$id]);
}

if ($act === 'unarchive') {
  ensure_employees_table($pdo);
  $id = (int)($in['id'] ?? 0);
  if ($id <= 0) bad('missing id');
  set_archived($pdo,$id,false);
  ok(['unarchived'=>true,'id'=>$id]);
}

if ($act === 'delete') {
  ensure_employees_table($pdo);
  $id = (int)($in['id'] ?? 0);
  if ($id <= 0) bad('missing id');
  $pdo->prepare("DELETE FROM employees WHERE id=?")->execute([$id]);
  ok(['deleted'=>true,'id'=>$id]);
}

if ($act === 'list') {
  ensure_employees_table($pdo);
  $arch = isset($in['archived']) ? (int)!!$in['archived'] : 0;
  $st = $pdo->prepare("SELECT * FROM employees WHERE ".where_archived($pdo,(bool)$arch)." ORDER BY ".(col_exists($pdo,'employees','date_hired')?'date_hired':'id')." DESC, id DESC");
  $st->execute();
  ok(['employees'=>$st->fetchAll(PDO::FETCH_ASSOC)]);
}

if ($act === 'add_manual') {
  ensure_employees_table($pdo);

  $name    = trim((string)($in['name'] ?? ''));
  $role    = trim((string)($in['role'] ?? ''));
  // accept BOTH "dept" and "department" (and "site")
  $deptVal = trim((string)( $in['dept'] ?? ($in['department'] ?? ($in['site'] ?? 'Operations')) ));
  // accept BOTH "status" and "employment_status"
  $statusI = trim((string)( $in['status'] ?? ($in['employment_status'] ?? 'Active') ));
  $emailI  = trim((string)($in['email'] ?? ''));
  $dhVal   = trim((string)($in['date_hired'] ?? date('Y-m-d H:i:s')));

  if ($name === '') bad('Name is required');

  // dynamic column names
  $deptCol = first_existing_col($pdo,'employees',['department','dept','site','department_name']);
  $emailCol= first_existing_col($pdo,'employees',['email','company_email','work_email','email_address']);
  $dhCol   = first_existing_col($pdo,'employees',['date_hired','hired_at','hired_date','employment_date','date_started','start_date']);
  $stCol   = get_status_col($pdo);

  $cols = ['name','role'];
  $vals = [':name',':role'];
  $bind = [':name'=>$name, ':role'=>$role];

  if ($deptCol) { $cols[]=$deptCol; $vals[]=':dept';   $bind[':dept']=$deptVal; }
  if ($stCol)   { $cols[]=$stCol;   $vals[]=':status'; $bind[':status']=$statusI; }
  if ($dhCol)   { $cols[]=$dhCol;   $vals[]=':dh';     $bind[':dh']=$dhVal; }
  if ($emailCol && $emailI!=='') { $cols[]=$emailCol; $vals[]=':_email'; $bind[':_email']=$emailI; }
  if (col_exists($pdo,'employees','created_at')) { $cols[]='created_at'; $vals[]='NOW()'; }
  if (col_exists($pdo,'employees','updated_at')) { $cols[]='updated_at'; $vals[]='NOW()'; }

  $sql = "INSERT INTO employees (".implode(',',$cols).") VALUES (".implode(',',$vals).")";
  $pdo->prepare($sql)->execute($bind);

  ok(['added'=>true]);
}


bad('unknown action');
