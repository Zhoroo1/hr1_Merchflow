<?php
/* HR1 Nextgenmms – Dashboard Live API (schema-aware) */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php'; // must set $pdo (PDO MySQL)
date_default_timezone_set('Asia/Manila');
try { $pdo->exec("SET time_zone = '+08:00'"); } catch (Throwable $e) {}

function ok($data=[]){ echo json_encode(['ok'=>true,'data'=>$data]); exit; }
function bad($msg,$code=400){ http_response_code($code); echo json_encode(['ok'=>false,'error'=>$msg]); exit; }

function table_exists(PDO $pdo,string $t):bool{
  try{ return (bool)$pdo->query("SHOW TABLES LIKE ".$pdo->quote($t))->fetch(); }
  catch(Throwable $e){ return false; }
}
function col_exists(PDO $pdo,string $t,string $c):bool{
  if(!table_exists($pdo,$t)) return false;
  try{ return (bool)$pdo->query("SHOW COLUMNS FROM `$t` LIKE ".$pdo->quote($c))->fetch(); }
  catch(Throwable $e){ return false; }
}
function n(PDO $pdo,string $t,string $where='1'):int{
  if(!table_exists($pdo,$t)) return 0;
  try{ return (int)$pdo->query("SELECT COUNT(*) FROM `$t` WHERE $where")->fetchColumn(); }
  catch(Throwable $e){ return 0; }
}
function rows(PDO $pdo,string $sql,array $p=[]):array{
  try{ $st=$pdo->prepare($sql); $st->execute($p); return $st->fetchAll(PDO::FETCH_ASSOC); }
  catch(Throwable $e){ return []; }
}

/* ---- Tables present in your DB (from your screenshot) ---- */
$APPS_TBL = table_exists($pdo,'applicants')        ? 'applicants'        : null;
$EMPL_TBL = table_exists($pdo,'employees')         ? 'employees'         : (table_exists($pdo,'users') ? 'users' : null);
$EVAL_TBL = table_exists($pdo,'evaluations')       ? 'evaluations'       : null;
$RECO_TBL = table_exists($pdo,'recognitions')      ? 'recognitions'      : null;
$ONBD_TBL = table_exists($pdo,'onboarding_tasks')  ? 'onboarding_tasks'  : (table_exists($pdo,'onboarding_plans') ? 'onboarding_plans' : null);
$REQS_TBL = table_exists($pdo,'requisitions')      ? 'requisitions'      : (table_exists($pdo,'recruitments') ? 'recruitments' : null);
$RVWK_TBL = table_exists($pdo,'review_tasks')      ? 'review_tasks'      : null;

/* ================= KPI COUNTS ================= */
$kpi = [
  'applicants'   => $APPS_TBL ? n($pdo,$APPS_TBL) : 0,
  'recruitments' => $REQS_TBL ? n($pdo,$REQS_TBL) : 0,
  'onboarding'   => $ONBD_TBL ? n($pdo,$ONBD_TBL, col_exists($pdo,$ONBD_TBL,'status') ? "LOWER(status) NOT IN ('done','completed','cancelled')" : "1") : 0,
  'employees'    => $EMPL_TBL ? n($pdo,$EMPL_TBL) : 0,
  'recognitions' => 0,
  'reviews_due'  => 0,
];

/* recognitions this month (created_at|date) */
if ($RECO_TBL){
  $dateCol = col_exists($pdo,$RECO_TBL,'created_at') ? 'created_at' : (col_exists($pdo,$RECO_TBL,'date') ? 'date' : null);
  $kpi['recognitions'] = $dateCol
    ? n($pdo,$RECO_TBL,"`$dateCol` IS NOT NULL AND MONTH(`$dateCol`)=MONTH(CURDATE()) AND YEAR(`$dateCol`)=YEAR(CURDATE())")
    : n($pdo,$RECO_TBL);
}

/* reviews due:
   priority 1: evaluations.next_review_at or evaluations.review_date
   plus status IN (due, pending, overdue)
   fallback: review_tasks due_date/status if exists
*/
if ($EVAL_TBL){
  $dueDateCol = col_exists($pdo,$EVAL_TBL,'next_review_at') ? 'next_review_at'
              : (col_exists($pdo,$EVAL_TBL,'review_date') ? 'review_date' : null);
  $dueByDate  = $dueDateCol ? n($pdo,$EVAL_TBL,"`$dueDateCol` IS NOT NULL AND `$dueDateCol` <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)") : 0;
  $dueByStat  = col_exists($pdo,$EVAL_TBL,'status') ? n($pdo,$EVAL_TBL,"LOWER(status) IN ('due','pending','overdue')") : 0;
  $kpi['reviews_due'] = $dueByDate + $dueByStat;
} elseif ($RVWK_TBL){
  $dueCol = col_exists($pdo,$RVWK_TBL,'due_date') ? 'due_date' : (col_exists($pdo,$RVWK_TBL,'due_at') ? 'due_at' : null);
  $cnt = 0;
  if ($dueCol) $cnt += n($pdo,$RVWK_TBL,"`$dueCol` IS NOT NULL AND `$dueCol` <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
  if (col_exists($pdo,$RVWK_TBL,'status')) $cnt += n($pdo,$RVWK_TBL,"LOWER(status) IN ('due','pending','overdue')");
  $kpi['reviews_due'] = $cnt;
}

/* ================= PIPELINE =================
   Prefer requisitions.stage; fallback to applicants.status/stage
   Map common values → Sourcing/Screening/Interview/Offer/Hired
*/
$pipeline = ['Sourcing'=>0,'Screening'=>0,'Interview'=>0,'Offer'=>0,'Hired'=>0];

if ($REQS_TBL && col_exists($pdo,$REQS_TBL,'stage')){
  foreach (rows($pdo,"SELECT LOWER(stage) s, COUNT(*) c FROM `$REQS_TBL` GROUP BY s") as $r){
    $map = ['sourcing'=>'Sourcing','screening'=>'Screening','interview'=>'Interview','offer'=>'Offer','offered'=>'Offer','hired'=>'Hired'];
    $k = $map[$r['s']] ?? null; if($k) $pipeline[$k] += (int)$r['c'];
  }
} elseif ($APPS_TBL){
  $col = col_exists($pdo,$APPS_TBL,'stage') ? 'stage' : (col_exists($pdo,$APPS_TBL,'status') ? 'status' : null);
  if ($col){
    foreach (rows($pdo,"SELECT LOWER($col) s, COUNT(*) c FROM `$APPS_TBL` GROUP BY s") as $r){
      $map = [
        'new'=>'Sourcing','pool'=>'Sourcing','sourcing'=>'Sourcing',
        'screening'=>'Screening','shortlist'=>'Screening',
        'interview'=>'Interview','interviewing'=>'Interview',
        'offer'=>'Offer','offered'=>'Offer',
        'hired'=>'Hired'
      ];
      $k = $map[$r['s']] ?? null; if($k) $pipeline[$k] += (int)$r['c'];
    }
  }
}

/* ================= RECENT LISTS ================= */
$recentApplicants=[];
if ($APPS_TBL){
  $name  = "COALESCE(fullname,name,CONCAT(firstname,' ',lastname))";
  $role  = col_exists($pdo,$APPS_TBL,'position') ? 'position' : (col_exists($pdo,$APPS_TBL,'job_title')?'job_title':"NULL");
  $stage = col_exists($pdo,$APPS_TBL,'stage') ? 'stage' : (col_exists($pdo,$APPS_TBL,'status')?'status':"NULL");
  $date  = col_exists($pdo,$APPS_TBL,'created_at') ? 'created_at' : (col_exists($pdo,$APPS_TBL,'applied_at') ? 'applied_at' : (col_exists($pdo,$APPS_TBL,'added_at')?'added_at':null));
  if($date){
    $recentApplicants = rows($pdo,"
      SELECT $name AS name, $role AS role, $stage AS stage, `$date` AS dt
      FROM `$APPS_TBL` ORDER BY `$date` DESC LIMIT 6");
  }
}

/* Onboarding list from onboarding_tasks|onboarding_plans */
$onboarding=[];
if ($ONBD_TBL){
  $name = "COALESCE(employee_name,candidate_name,assignee,owner)";
  $role = "COALESCE(role,position,job_title)";
  $due  = col_exists($pdo,$ONBD_TBL,'due_date') ? 'due_date' : (col_exists($pdo,$ONBD_TBL,'due_at')?'due_at':(col_exists($pdo,$ONBD_TBL,'target_date')?'target_date':null));
  $status = "COALESCE(status,stage)";
  $order = $due ? "`$due`" : (col_exists($pdo,$ONBD_TBL,'created_at')?'`created_at`':'1');
  $onboarding = rows($pdo,"SELECT $name AS name, $role AS role, $status AS status".($due?", `$due` AS due_dt":", NULL AS due_dt")." FROM `$ONBD_TBL` ORDER BY $order DESC LIMIT 5");
}

/* Reviews upcoming list */
$reviews=[];
if ($EVAL_TBL){
  $date = col_exists($pdo,$EVAL_TBL,'next_review_at') ? 'next_review_at' : (col_exists($pdo,$EVAL_TBL,'review_date') ? 'review_date' : null);
  $name = "COALESCE(employee_name,emp_name,employee)";
  $role = "COALESCE(role,position,job_title)";
  if ($date){
    $reviews = rows($pdo,"SELECT $name AS emp, $role AS role, COALESCE(period,type,'Initial') AS type, `$date` AS dt FROM `$EVAL_TBL` WHERE `$date` IS NOT NULL ORDER BY `$date` ASC LIMIT 6");
  }
} elseif ($RVWK_TBL){
  $date = col_exists($pdo,$RVWK_TBL,'due_date') ? 'due_date' : (col_exists($pdo,$RVWK_TBL,'due_at') ? 'due_at' : null);
  if ($date){
    $reviews = rows($pdo,"SELECT COALESCE(employee_name,assignee,'—') AS emp, COALESCE(role,position,'') AS role, COALESCE(type,'Initial') AS type, `$date` AS dt FROM `$RVWK_TBL` WHERE `$date` IS NOT NULL ORDER BY `$date` ASC LIMIT 6");
  }
}

/* Recognition feed */
$recog=[];
if ($RECO_TBL){
  $from = col_exists($pdo,$RECO_TBL,'from_name') ? 'from_name' : (col_exists($pdo,$RECO_TBL,'from_user')?'from_user':(col_exists($pdo,$RECO_TBL,'from')?'from':"NULL"));
  $to   = col_exists($pdo,$RECO_TBL,'to_name')   ? 'to_name'   : (col_exists($pdo,$RECO_TBL,'to_user')  ?'to_user'  :(col_exists($pdo,$RECO_TBL,'to')?'to':"NULL"));
  $why  = col_exists($pdo,$RECO_TBL,'reason')    ? 'reason'    : (col_exists($pdo,$RECO_TBL,'message')  ?'message'  :"NULL");
  $date = col_exists($pdo,$RECO_TBL,'created_at')? 'created_at': (col_exists($pdo,$RECO_TBL,'date')     ?'date'     : null);
  if($date){
    $recog = rows($pdo,"SELECT $from AS from_name, $to AS to_name, $why AS reason, `$date` AS dt FROM `$RECO_TBL` ORDER BY `$date` DESC LIMIT 6");
  }
}

ok([
  'kpi'      => $kpi,
  'pipeline' => $pipeline,
  'lists'    => [
    'recentApplicants' => $recentApplicants,
    'onboarding'       => $onboarding,
    'reviews'          => $reviews,
    'recognitions'     => $recog,
  ],
  'timestamp'=> date('Y-m-d H:i:s')
]);
