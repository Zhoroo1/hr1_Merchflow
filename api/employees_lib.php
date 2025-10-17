<?php
declare(strict_types=1);

/* ---- tiny helpers copied from employees_api.php ---- */
function emp_table_exists(PDO $pdo, string $t): bool {
  try { return (bool)$pdo->query("SHOW TABLES LIKE ".$pdo->quote($t))->fetch(); }
  catch(Throwable $e){ return false; }
}
function emp_col_exists(PDO $pdo, string $t, string $c): bool {
  try { $st=$pdo->prepare("SHOW COLUMNS FROM `$t` LIKE ?"); $st->execute([$c]); return (bool)$st->fetch(); }
  catch(Throwable $e){ return false; }
}
function emp_first_existing_col(PDO $pdo, string $table, array $candidates): string {
  foreach ($candidates as $c) if (emp_col_exists($pdo,$table,$c)) return $c;
  return '';
}
function emp_get_status_col(PDO $pdo): ?string {
  if (emp_col_exists($pdo,'employees','status')) return 'status';
  if (emp_col_exists($pdo,'employees','employment_status')) return 'employment_status';
  return null;
}
function emp_ensure_employees_table(PDO $pdo): void {
  if (!emp_table_exists($pdo,'employees')) {
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

/* ---- this is the function you need ---- */
function insert_or_update_employee_from_applicant(PDO $pdo, int $applicantId): void {
  try {
    $stmt = $pdo->prepare("SELECT * FROM applicants WHERE id=? LIMIT 1");
    $stmt->execute([$applicantId]);
    $a = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$a) return;

    $name   = $a['full_name'] ?? ($a['name'] ?? '');
    $role   = $a['role'] ?? '';
    $dept   = $a['department'] ?? ($a['dept'] ?? 'Operations');
    $emailV = $a['email'] ?? ($a['company_email'] ?? ($a['work_email'] ?? ''));
    $status = 'Active';
    $dateHired = date('Y-m-d H:i:s');

    emp_ensure_employees_table($pdo);

    $emailCol = emp_first_existing_col($pdo,'employees',['email','company_email','work_email','email_address']);
    $deptCol  = emp_first_existing_col($pdo,'employees',['department','dept','site','department_name']);
    $dhCol    = emp_first_existing_col($pdo,'employees',['date_hired','hired_at','hired_date','employment_date','date_started','start_date']);

    // UPDATE if exists
    $chk = $pdo->prepare("SELECT id FROM employees WHERE applicant_id=?");
    $chk->execute([$applicantId]);
    $exists = $chk->fetchColumn();

    if ($exists) {
      $sets = ["name = :name", "role = :role"];
      $bind = [':name'=>$name, ':role'=>$role, ':aid'=>$applicantId];
      if ($deptCol) { $sets[]="$deptCol = :dept"; $bind[':dept']=$dept; }
      if ($emailCol && $emailV!=='') { $sets[]="$emailCol = :email"; $bind[':email']=$emailV; }
      if ($stCol = emp_get_status_col($pdo)) { $sets[]="$stCol = :status"; $bind[':status']=$status; }
      if ($dhCol) { $sets[]="$dhCol = :dh"; $bind[':dh']=$dateHired; }
      if (emp_col_exists($pdo,'employees','updated_at')) $sets[]="updated_at = NOW()";
      $sql = "UPDATE employees SET ".implode(',',$sets)." WHERE applicant_id = :aid";
      $pdo->prepare($sql)->execute($bind);
    } else {
      $cols = ['applicant_id','name','role'];  $vals = [':aid',':name',':role'];
      $bind = [':aid'=>$applicantId, ':name'=>$name, ':role'=>$role];
      if ($deptCol){ $cols[]=$deptCol; $vals[]=':dept';   $bind[':dept']=$dept; }
      if ($stCol = emp_get_status_col($pdo)){ $cols[]=$stCol; $vals[]=':status'; $bind[':status']=$status; }
      if ($dhCol){ $cols[]=$dhCol; $vals[]=':dh'; $bind[':dh']=$dateHired; }
      if ($emailCol && $emailV!==''){ $cols[]=$emailCol; $vals[]=':email'; $bind[':email']=$emailV; }
      if (emp_col_exists($pdo,'employees','created_at')) { $cols[]='created_at'; $vals[]='NOW()'; }
      if (emp_col_exists($pdo,'employees','updated_at')) { $cols[]='updated_at'; $vals[]='NOW()'; }
      $sql = "INSERT INTO employees (".implode(',',$cols).") VALUES (".implode(',',$vals).")";
      $pdo->prepare($sql)->execute($bind);
    }
  } catch (Throwable $e) { /* silent */ }
}
