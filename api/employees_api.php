<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

session_start();
if (empty($_SESSION['user'])) { echo json_encode(['ok'=>false,'error'=>'unauthorized']); exit; }

require_once __DIR__ . '/../includes/db.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try { $pdo->exec("SET time_zone = '+08:00'"); } catch (Throwable $e) {}

function ok($d=[]) { echo json_encode(['ok'=>true,'data'=>$d]); exit; }
function bad($m,$c=200){ http_response_code($c); echo json_encode(['ok'=>false,'error'=>$m]); exit; }

$in  = json_decode(file_get_contents('php://input') ?: '[]', true) ?: [];
$act = strtolower((string)($in['action'] ?? ''));

/* ---------- helpers ---------- */
function table_exists(PDO $pdo, string $t): bool {
  try { return (bool)$pdo->query("SHOW TABLES LIKE ".$pdo->quote($t))->fetch(); } catch(Throwable $e){ return false; }
}
function col_exists(PDO $pdo, string $t, string $c): bool {
  try { $st=$pdo->prepare("SHOW COLUMNS FROM `$t` LIKE ?"); $st->execute([$c]); return (bool)$st->fetch(); } catch(Throwable $e){ return false; }
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

/* status helpers */
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

/* archive helpers */
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
  // fallback via status
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

/* ---------- ensure minimal employees table (and add is_archived if missing) ---------- */
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
        `is_archived` TINYINT(1) NOT NULL DEFAULT 0,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_applicant` (`applicant_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
  }
  if (!col_exists($pdo,'employees','is_archived')) {
    $pdo->exec("ALTER TABLE `employees` ADD COLUMN `is_archived` TINYINT(1) NOT NULL DEFAULT 0");
  }
}

/* ---------- sync from applicant (insert/update) ---------- */
function insert_or_update_employee_from_applicant(PDO $pdo, int $appId): array {
  if (!table_exists($pdo,'applicants')) return ['done'=>false,'reason'=>'applicants table missing'];
  ensure_employees_table($pdo);

  $st = $pdo->prepare("SELECT * FROM applicants WHERE id=? LIMIT 1");
  $st->execute([$appId]);
  $a = $st->fetch(PDO::FETCH_ASSOC);
  if (!$a) return ['done'=>false,'reason'=>'applicant not found'];

  $statusApp = strtolower(trim((string)pick($a, ['status'], '')));
  if ($statusApp !== 'hired') return ['done'=>false,'reason'=>'not hired'];

  $name  = trim((string)pick($a, ['full_name','name','applicant_name'], ''));
  if ($name==='') return ['done'=>false,'reason'=>'no name'];
  $role  = (string)pick($a, ['role','position','position_applied'], 'Staff');
  $dept  = 'Operations';

  /* >>> FIX: use real hired timestamp; never preferred start date. <<< */
  $dhRaw = (string)pick($a, [
    'date_hired',        // explicit hired timestamp
    'hire_date',         // alternate naming
    'hired_at',          // event timestamp
    'status_changed_at', // if you store when status became "hired"
    'updated_at'         // last update as fallback
  ], '');
  $dh    = $dhRaw ? $dhRaw : date('Y-m-d H:i:s');

  $statusCol = get_status_col($pdo);
  $statusVal = $statusCol ? normalize_status_for_employees($pdo, 'regular') : null;

  $has = [
    'applicant_id' => col_exists($pdo,'employees','applicant_id'),
    'name'         => col_exists($pdo,'employees','name'),
    'role'         => col_exists($pdo,'employees','role'),
    'department'   => col_exists($pdo,'employees','department'),
    'status'       => $statusCol !== null,
    'date_hired'   => col_exists($pdo,'employees','date_hired'),
  ];
  if (!$has['name']) return ['done'=>false,'reason'=>'employees.name missing'];

  // find existing by applicant_id (preferred) or name
  $emp = null;
  if ($has['applicant_id']) {
    $q = $pdo->prepare("SELECT id FROM employees WHERE applicant_id=? LIMIT 1");
    $q->execute([$appId]); $emp = $q->fetch(PDO::FETCH_ASSOC);
  } else {
    $q = $pdo->prepare("SELECT id FROM employees WHERE name=? LIMIT 1");
    $q->execute([$name]); $emp = $q->fetch(PDO::FETCH_ASSOC);
  }

  try {
    if ($emp) {
      $sets=[]; $args=[];
      if ($has['name'])       { $sets[]="name=?";       $args[]=$name; }
      if ($has['role'])       { $sets[]="role=?";       $args[]=$role; }
      if ($has['department']) { $sets[]="department=?"; $args[]=$dept; }
      if ($has['status'] && $statusCol){ $sets[]="`$statusCol`=?"; $args[]=$statusVal; }
      if ($has['date_hired']) { $sets[]="date_hired=?"; $args[]=$dh; } // overwrite with true hire time
      if (col_exists($pdo,'employees','updated_at')) { $sets[]="updated_at=NOW()"; }
      $args[]=(int)$emp['id'];
      $pdo->prepare("UPDATE employees SET ".implode(',', $sets)." WHERE id=?")->execute($args);
      return ['done'=>true,'mode'=>'updated','employee_id'=>(int)$emp['id']];
    }

    $cols=[]; $qv=[]; $args=[];
    if ($has['applicant_id']) { $cols[]='applicant_id'; $qv[]='?'; $args[]=$appId; }
    if ($has['name'])         { $cols[]='name';         $qv[]='?'; $args[]=$name; }
    if ($has['role'])         { $cols[]='role';         $qv[]='?'; $args[]=$role; }
    if ($has['department'])   { $cols[]='department';   $qv[]='?'; $args[]=$dept; }
    if ($has['status'] && $statusCol){ $cols[]=$statusCol; $qv[]='?'; $args[]=$statusVal; }
    if ($has['date_hired'])   { $cols[]='date_hired';   $qv[]='?'; $args[]=$dh; }

    $sql = "INSERT INTO employees (".implode(',', $cols).") VALUES (".implode(',', $qv).")";
    $pdo->prepare($sql)->execute($args);
    return ['done'=>true,'mode'=>'inserted','employee_id'=>(int)$pdo->lastInsertId()];
  } catch (Throwable $e) {
    return ['done'=>false,'reason'=>'db-fail','error'=>$e->getMessage()];
  }
}

/* ================== ACTIONS ================== */

if ($act === 'sync_from_applicant') {
  $appId = (int)($in['applicant_id'] ?? 0);
  if ($appId <= 0) bad('missing applicant_id');
  $res = insert_or_update_employee_from_applicant($pdo, $appId);
  if (empty($res['done'])) ok(['skipped'=>true] + $res);
  ok($res);
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

/* ------- ARCHIVE / UNARCHIVE / DELETE ------- */

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

/* ------- LIST (supports archived filter) ------- */
if ($act === 'list') {
  ensure_employees_table($pdo);
  $arch = isset($in['archived']) ? (int)!!$in['archived'] : 0;
  $st = $pdo->prepare("SELECT * FROM employees WHERE ".where_archived($pdo,(bool)$arch)." ORDER BY date_hired DESC, id DESC");
  $st->execute();
  ok(['employees'=>$st->fetchAll(PDO::FETCH_ASSOC)]);
}

bad('unknown action');
