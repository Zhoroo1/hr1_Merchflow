<?php
// api/onboarding_api.php
declare(strict_types=1);
header('Content-Type: application/json');

try {
  require_once __DIR__ . '/../includes/db.php';   // must set $pdo = new PDO(...)
  if (!isset($pdo) || !$pdo instanceof PDO) throw new RuntimeException('PDO $pdo not found');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'DB connect error: '.$e->getMessage()]);
  exit;
}

/* ---------- helpers ---------- */
function ok($data){ echo json_encode(['ok'=>true,'data'=>$data]); exit; }
function fail($msg,$code=400){ http_response_code($code); echo json_encode(['ok'=>false,'error'=>$msg]); exit; }
function in(): array {
  $raw = file_get_contents('php://input') ?: '';
  if ($raw==='') return [];
  $j = json_decode($raw,true);
  if (!is_array($j)) fail('Invalid JSON body');
  return $j;
}
function column_exists(PDO $pdo, string $table, string $col): bool {
  try { $st=$pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?"); $st->execute([$col]); return (bool)$st->fetch(); }
  catch(Throwable $e){ return false; }
}
/* Safe audit: only insert if columns exist */
function audit(PDO $pdo, string $etype, int $eid, string $action, array $details=[]): void {
  if (!column_exists($pdo,'audit_logs','id')) return; // table absent
  $hasEType = column_exists($pdo,'audit_logs','entity_type');
  $hasEId   = column_exists($pdo,'audit_logs','entity_id');
  $hasAct   = column_exists($pdo,'audit_logs','action');
  $hasDet   = column_exists($pdo,'audit_logs','details');
  $cols=[]; $vals=[]; $args=[];
  if ($hasEType){ $cols[]='entity_type'; $vals[]='?'; $args[]=$etype; }
  if ($hasEId){   $cols[]='entity_id';   $vals[]='?'; $args[]=$eid; }
  if ($hasAct){   $cols[]='action';      $vals[]='?'; $args[]=$action; }
  if ($hasDet){   $cols[]='details';     $vals[]='?'; $args[]=json_encode($details,JSON_UNESCAPED_UNICODE); }
  if (!$cols) return;
  $cols[]='created_at'; $vals[]='NOW()';
  $sql="INSERT INTO audit_logs (".implode(',',$cols).") VALUES (".implode(',',$vals).")";
  $pdo->prepare($sql)->execute($args);
}

/* ---------- input ---------- */
$in = in();
$action = $in['action'] ?? '';

/* ---------- utility ---------- */
function plan_progress_from_tasks(PDO $pdo, int $plan_id): int {
  $tot =(int)$pdo->query("SELECT COUNT(*) FROM onboarding_tasks WHERE plan_id=".(int)$plan_id)->fetchColumn();
  if ($tot===0) return 0;
  $done=(int)$pdo->query("SELECT COUNT(*) FROM onboarding_tasks WHERE plan_id=".(int)$plan_id." AND status='Completed'")->fetchColumn();
  return (int)round(($done/$tot)*100);
}
function recompute_plan_status(PDO $pdo, int $plan_id): void {
  $prog = plan_progress_from_tasks($pdo,$plan_id);
  $tot  =(int)$pdo->query("SELECT COUNT(*) FROM onboarding_tasks WHERE plan_id=".(int)$plan_id)->fetchColumn();
  $done =(int)$pdo->query("SELECT COUNT(*) FROM onboarding_tasks WHERE plan_id=".(int)$plan_id." AND status='Completed'")->fetchColumn();
  $st = ($tot>0 && $done===$tot) ? 'Completed' : (($done>0)?'In Progress':'Pending');
  if (column_exists($pdo,'onboarding_plans','progress')) {
    $pdo->prepare("UPDATE onboarding_plans SET progress=?, status=? WHERE id=?")->execute([$prog,$st,$plan_id]);
  } else {
    $pdo->prepare("UPDATE onboarding_plans SET status=? WHERE id=?")->execute([$st,$plan_id]);
  }
}

/* =========================== routes =========================== */
try {

/* -------- LIST with filters (q,status) + KPI, upcoming, overdue -------- */
if ($action==='list') {
  $q = trim((string)($in['q'] ?? ''));
  $status = trim((string)($in['status'] ?? '')); // '', 'Pending', 'In Progress', 'Completed'
  $w=[]; $args=[];
  if ($q!==''){ $w[]="(p.hire_name LIKE ? OR p.role LIKE ?)"; $args[]="%$q%"; $args[]="%$q%"; }
  if ($status!==''){ $w[]="p.status=?"; $args[]=$status; }
  $where = $w ? ('WHERE '.implode(' AND ',$w)) : '';

  $hasProgress = column_exists($pdo,'onboarding_plans','progress');
  $sql = $hasProgress
    ? "SELECT p.id,p.hire_name,p.role,p.site,p.start_date,p.status,COALESCE(p.progress,0) as progress
       FROM onboarding_plans p $where ORDER BY p.start_date DESC, p.id DESC"
    : "SELECT p.id,p.hire_name,p.role,p.site,p.start_date,p.status,0 as progress
       FROM onboarding_plans p $where ORDER BY p.start_date DESC, p.id DESC";
  $st=$pdo->prepare($sql); $st->execute($args);
  $plans=$st->fetchAll(PDO::FETCH_ASSOC);

  // ⛔️ Hide legacy blank-name plans from the UI
  $plans = array_values(array_filter($plans, fn($p)=> trim((string)($p['hire_name'] ?? '')) !== ''));

  if (!$hasProgress) {
    foreach ($plans as &$p) { $p['progress']=plan_progress_from_tasks($pdo,(int)$p['id']); }
    unset($p);
  }

  // Upcoming (next starts)
  $up = $pdo->query("SELECT id,hire_name,role,start_date FROM onboarding_plans WHERE start_date>=CURDATE() ORDER BY start_date ASC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);

  // Overdue tasks
  $ov = $pdo->query("
    SELECT t.id, t.title as task, t.owner, t.due_date as due,
           DATEDIFF(CURDATE(), t.due_date) AS age_days,
           p.hire_name as hire
    FROM onboarding_tasks t
    JOIN onboarding_plans p ON p.id=t.plan_id
    WHERE t.due_date IS NOT NULL AND t.due_date<CURDATE() AND t.status<>'Completed'
    ORDER BY t.due_date ASC
  ")->fetchAll(PDO::FETCH_ASSOC);

  // KPIs
$in_onb = (int)$pdo->query(
  "SELECT COUNT(*) FROM onboarding_plans WHERE status IN ('Pending','In Progress')"
)->fetchColumn();

[$due_today, $overdue] = $pdo->query("
  SELECT
    SUM(t.status<>'Completed' AND t.due_date=CURDATE())          AS due_today,
    SUM(t.status<>'Completed' AND t.due_date IS NOT NULL
                               AND t.due_date<CURDATE())         AS overdue
  FROM onboarding_tasks t
")->fetch(PDO::FETCH_NUM);

$avg = 0;
if (count($plans)) {
  $sum=0; foreach ($plans as $p) { $sum += (int)$p['progress']; }
  $avg = (int)round($sum / max(1,count($plans)));
}


  ok(['plans'=>$plans,'upcoming'=>$up,'overdue'=>$ov,'kpi'=>[
    'in_onboarding'=>$in_onb,'due_today'=>$due_today,'overdue'=>$overdue,'avg_completion'=>$avg
  ]]);
}

/* -------- (NEW) AUTO-ADD from Applicants when status = Hired -------- */
if ($action==='onboarding.auto_add') {
  $name = trim((string)($in['hire_name'] ?? ''));
  $role = trim((string)($in['role'] ?? ''));
  $site = trim((string)($in['site'] ?? ''));
  $start= trim((string)($in['start_date'] ?? ''));

  if ($name==='' || $role==='') fail('Missing name/role');
  if ($start==='') $start = date('Y-m-d');

  // Avoid duplicates (same person + role on same date)
  $st = $pdo->prepare("SELECT id FROM onboarding_plans WHERE hire_name=? AND role=? AND DATE(start_date)=?");
  $st->execute([$name,$role,$start]);
  $exists = (int)($st->fetchColumn() ?: 0);
  if ($exists) ok(['id'=>$exists,'duplicate'=>true]);

  $hasProgress = column_exists($pdo,'onboarding_plans','progress');
  $hasSite     = column_exists($pdo,'onboarding_plans','site');

  if ($hasProgress && $hasSite) {
    $ins=$pdo->prepare("INSERT INTO onboarding_plans (hire_name,role,site,start_date,status,progress) VALUES (?,?,?,?,?,0)");
    $ins->execute([$name,$role,$site,$start,'Pending']);
  } elseif ($hasProgress && !$hasSite) {
    $ins=$pdo->prepare("INSERT INTO onboarding_plans (hire_name,role,start_date,status,progress) VALUES (?,?,?,?,0)");
    $ins->execute([$name,$role,$start,'Pending']);
  } elseif (!$hasProgress && $hasSite) {
    $ins=$pdo->prepare("INSERT INTO onboarding_plans (hire_name,role,site,start_date,status) VALUES (?,?,?,?,?)");
    $ins->execute([$name,$role,$site,$start,'Pending']);
  } else {
    $ins=$pdo->prepare("INSERT INTO onboarding_plans (hire_name,role,start_date,status) VALUES (?,?,?,?)");
    $ins->execute([$name,$role,$start,'Pending']);
  }

  $id = (int)$pdo->lastInsertId();
  audit($pdo,'onboarding_plan',$id,'add_from_applicant',['name'=>$name,'role'=>$role]);
  ok(['id'=>$id]);
}

/* -------- ADD PLAN -------- */
if ($action==='add_plan') {
  $name = trim((string)($in['hire_name'] ?? ''));
  $role = trim((string)($in['role'] ?? ''));
  $start = trim((string)($in['start_date'] ?? ''));
  if ($name==='' || $role==='' || $start==='') fail('Missing fields');
  $site = (string)($_ENV['STORE_SITE'] ?? '');

  if (column_exists($pdo,'onboarding_plans','progress')) {
    $st=$pdo->prepare("INSERT INTO onboarding_plans (hire_name,role,site,start_date,status,progress) VALUES (?,?,?,?,?,0)");
    $st->execute([$name,$role,$site,$start,'Pending']);
  } else {
    $st=$pdo->prepare("INSERT INTO onboarding_plans (hire_name,role,site,start_date,status) VALUES (?,?,?,?,?)");
    $st->execute([$name,$role,$site,$start,'Pending']);
  }
  $id=(int)$pdo->lastInsertId();
  audit($pdo,'onboarding_plan',$id,'add',['name'=>$name]);
  ok(['id'=>$id]);
}

/* -------- UPDATE PLAN -------- */
if ($action==='update_plan') {
  $id = (int)($in['id'] ?? 0);
  $name = trim((string)($in['hire_name'] ?? ''));
  $role = trim((string)($in['role'] ?? ''));
  $start = trim((string)($in['start_date'] ?? ''));
  if ($id<=0) fail('Invalid id');
  $st=$pdo->prepare("UPDATE onboarding_plans SET hire_name=?, role=?, start_date=? WHERE id=?");
  $st->execute([$name,$role,$start,$id]);
  audit($pdo,'onboarding_plan',$id,'update',[]);
  ok(['updated'=>(bool)$st->rowCount()]);
}

/* -------- DELETE PLAN (and tasks) -------- */
if ($action==='delete_plan') {
  $id=(int)($in['id'] ?? 0); if ($id<=0) fail('Invalid id');
  $pdo->prepare("DELETE FROM onboarding_tasks WHERE plan_id=?")->execute([$id]);
  $st=$pdo->prepare("DELETE FROM onboarding_plans WHERE id=?"); $st->execute([$id]);
  audit($pdo,'onboarding_plan',$id,'delete',[]);
  ok(['deleted'=>(bool)$st->rowCount()]);
}

/* -------- GET PLAN + TASKS -------- */
if ($action==='get_plan') {
  $id=(int)($in['id'] ?? 0); if ($id<=0) fail('Invalid id');
  $p=$pdo->prepare("SELECT id,hire_name,role,site,start_date,status,
                       ".(column_exists($pdo,'onboarding_plans','progress')?'COALESCE(progress,0)':'0')." AS progress
                    FROM onboarding_plans WHERE id=?");
  $p->execute([$id]); $plan=$p->fetch(PDO::FETCH_ASSOC);
  if (!$plan) fail('Not found',404);
  if (!column_exists($pdo,'onboarding_plans','progress')) {
    $plan['progress']=plan_progress_from_tasks($pdo,$id);
  }
  $t=$pdo->prepare("SELECT id,title,owner,due_date,status FROM onboarding_tasks WHERE plan_id=? ORDER BY id ASC");
  $t->execute([$id]); $tasks=$t->fetchAll(PDO::FETCH_ASSOC);
  ok(['plan'=>$plan,'tasks'=>$tasks]);
}

/* -------- ADD TASK -------- */
if ($action==='add_task') {
  $pid=(int)($in['plan_id'] ?? 0);
  $title=trim((string)($in['title'] ?? ''));
  $owner=trim((string)($in['owner'] ?? 'Employee'));
  $due = trim((string)($in['due_date'] ?? ''));
  if ($pid<=0 || $title==='') fail('Invalid data');
  $st=$pdo->prepare("INSERT INTO onboarding_tasks (plan_id,title,owner,due_date,status) VALUES (?,?,?,?,?)");
  $st->execute([$pid,$title,$owner,($due!==''?$due:null),'Pending']);
  audit($pdo,'onboarding_task',(int)$pdo->lastInsertId(),'add',['plan_id'=>$pid]);
  recompute_plan_status($pdo,$pid);
  ok(['saved'=>true]);
}

/* -------- SET TASK STATUS -------- */
if ($action==='set_task_status') {
  $tid=(int)($in['task_id'] ?? 0);
  $status=trim((string)($in['status'] ?? 'Pending'));
  if ($tid<=0) fail('Invalid task_id');
  $pid=(int)$pdo->query("SELECT plan_id FROM onboarding_tasks WHERE id=$tid")->fetchColumn();
  if ($pid<=0) fail('Task not found',404);
  $pdo->prepare("UPDATE onboarding_tasks SET status=? WHERE id=?")->execute([$status,$tid]);
  audit($pdo,'onboarding_task',$tid,'update_status',['status'=>$status]);
  recompute_plan_status($pdo,$pid);
  ok(['id'=>$tid,'status'=>$status]);
}

/* -------- SET TASK DUE DATE -------- */
if ($action==='set_task_due') {
  $tid = (int)($in['task_id'] ?? 0);
  if ($tid <= 0) fail('Invalid task_id');

  // normalize due_date: '' or null => NULL
  $due = $in['due_date'] ?? null;
  if ($due === '' || $due === null) {
    $due = null;
  } else {
    // validate format YYYY-MM-DD
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$due)) {
      fail('Invalid date format, expected YYYY-MM-DD');
    }
  }

  // get plan_id for audit/recompute
  $st = $pdo->prepare("SELECT plan_id FROM onboarding_tasks WHERE id=?");
  $st->execute([$tid]);
  $pid = (int)($st->fetchColumn() ?: 0);
  if ($pid <= 0) fail('Task not found', 404);

  // update due_date (nullable)
  $sql = "UPDATE onboarding_tasks SET due_date = :due WHERE id = :id";
  $stmt = $pdo->prepare($sql);
  if ($due === null) {
    $stmt->bindValue(':due', null, PDO::PARAM_NULL);
  } else {
    $stmt->bindValue(':due', $due, PDO::PARAM_STR);
  }
  $stmt->bindValue(':id', $tid, PDO::PARAM_INT);
  $stmt->execute();

  audit($pdo, 'onboarding_task', $tid, 'update_due', ['due_date'=>$due]);
  // due date changes don't affect completion, but safe to recompute
  recompute_plan_status($pdo, $pid);

  ok(['id'=>$tid,'due_date'=>$due]);
}

/* -------- ADD TASKS (BULK) -------- */
if ($action === 'add_tasks_bulk') {
  $pid   = (int)($in['plan_id'] ?? 0);
  $tasks = $in['tasks'] ?? [];
  if ($pid <= 0) fail('Invalid plan_id');
  if (!is_array($tasks) || !count($tasks)) fail('No tasks to insert');

  $ins = $pdo->prepare(
    "INSERT INTO onboarding_tasks (plan_id,title,owner,due_date,status)
     VALUES (?,?,?,?,?)"
  );

  $pdo->beginTransaction();
  try {
    $n = 0;
    foreach ($tasks as $t) {
      $title = trim((string)($t['title'] ?? ''));
      if ($title === '') continue;
      $owner = trim((string)($t['owner'] ?? 'Employee'));
      $due   = $t['due_date'] ?? null;
      if ($due === '' || $due === null) { $due = null; }
      else {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$due)) {
          $due = null; // skip bad format to keep bulk robust
        }
      }
      $ins->execute([$pid,$title,$owner,$due,'Pending']);
      $n++;
    }
    $pdo->commit();

    audit($pdo,'onboarding_task',$pid,'bulk_add',['count'=>$n]);
    recompute_plan_status($pdo,$pid);
    ok(['inserted'=>$n]);
  } catch (Throwable $e) {
    $pdo->rollBack();
    fail('Bulk insert failed: '.$e->getMessage(), 500);
  }
}

/* -------- NEW: DELETE TASK -------- */
if ($action==='delete_task') {
  $tid=(int)($in['task_id'] ?? 0);
  if ($tid<=0) fail('Invalid task_id');
  $pid=(int)$pdo->query("SELECT plan_id FROM onboarding_tasks WHERE id=$tid")->fetchColumn();
  if ($pid<=0) fail('Task not found',404);
  $st=$pdo->prepare("DELETE FROM onboarding_tasks WHERE id=?"); $st->execute([$tid]);
  audit($pdo,'onboarding_task',$tid,'delete',[]);
  recompute_plan_status($pdo,$pid);
  ok(['deleted'=>(bool)$st->rowCount()]);
}

/* -------- (ONE-TIME) REPAIR LEGACY BAD NAMES -------- */
if ($action==='repair_bad_names') {
  $sqlFix = "
    UPDATE onboarding_plans p
    JOIN applicants a
      ON LOWER(a.status)='hired'
     AND a.role = p.role
     AND DATE(a.start_date) = DATE(p.start_date)
    SET p.hire_name = a.name
    WHERE (p.hire_name IS NULL OR TRIM(p.hire_name)='' OR p.hire_name LIKE '% Hire' OR p.hire_name = p.role)
  ";
  $fixed = $pdo->exec($sqlFix);

  $deleted = $pdo->exec("DELETE FROM onboarding_plans WHERE hire_name IS NULL OR TRIM(hire_name)=''");

  ok(['fixed_rows'=>$fixed, 'deleted_rows'=>$deleted]);
}

fail('Unknown action: '.$action,404);

} catch (Throwable $e) {
  fail($e->getMessage(),500);
}
