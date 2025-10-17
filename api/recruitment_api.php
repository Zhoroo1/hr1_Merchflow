<?php
// api/recruitment_api.php
declare(strict_types=1);
header('Content-Type: application/json');

try {
  require_once __DIR__ . '/../includes/db.php';   // must set $pdo = new PDO(...)
  require_once __DIR__ . '/../mail_config.php';   // PHPMailer helpers (sendHRMail / sendOTP)
  if (!isset($pdo) || !$pdo instanceof PDO) {
    throw new RuntimeException('PDO connection $pdo not found.');
  }
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  // Fallback shim kung sakaling walang sendHRMail sa mail_config.php
  if (!function_exists('sendHRMail')) {
    function sendHRMail(string $to, string $subject, string $html): array {
      return [false, 'sendHRMail() not available — define in mail_config.php'];
    }
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'DB connect error: '.$e->getMessage()]);
  exit;
}

/* -------------------- Helpers -------------------- */
function ok($data){ echo json_encode(['ok'=>true,'data'=>$data], JSON_UNESCAPED_UNICODE); exit; }
function fail($msg,$code=400){ http_response_code($code); echo json_encode(['ok'=>false,'error'=>$msg], JSON_UNESCAPED_UNICODE); exit; }
function in(): array {
  $raw = file_get_contents('php://input') ?: '';
  if ($raw==='') return [];
  $j = json_decode($raw,true);
  if (!is_array($j)) fail('Invalid JSON body');
  return $j;
}
function mapStageIn(?string $stage): string {
  $s = strtolower(trim((string)$stage));
  return match($s){
    'new'        => 'new',
    'screening'  => 'screening',
    'interview'  => 'interview',
    'offer'      => 'offer',
   'closed','accepted' => 'accepted',
    'accepted'   => 'closed',
    'rejected'   => 'rejected',
    default      => 'new',
  };
}

function mapStageOut(?string $stage): string {
  $s = strtolower(trim((string)$stage));
  return match($s){
    'new'        => 'new',
    'screen'     => 'screening',
    'screening'  => 'screening',
    'interview'  => 'interview',
    'offer'      => 'offer',
     'accepted'  => 'closed',
    'closed'     => 'closed',
    'rejected'   => 'rejected',
    default      => 'new',
  };
}


/* Applicants status normalizer (para hindi sumablay sa ENUM) */
function mapApplicantStatus(?string $st): string {
  $s = strtolower(trim((string)$st));
  return match($s) {
    'new'                 => 'new',
    'screen'              => 'screening',
    'screening'           => 'screening',
    'interview'           => 'interview',    // ✅ preserve Interview
    'shortlist','shortlisted' => 'shortlisted',
    'hired'               => 'hired',
    'rejected'            => 'rejected',
    default               => 'screening',
  };
}


/* ---- Schema helpers ---- */
function column_nullable(PDO $pdo, string $table, string $col): bool {
  try {
    $st = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $st->execute([$col]);
    $r = $st->fetch(PDO::FETCH_ASSOC);
    return strtoupper($r['Null'] ?? '') === 'YES';
  } catch (Throwable $e) { return false; }
}
function column_exists(PDO $pdo, string $table, string $col): bool {
  try {
    $st = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $st->execute([$col]);
    return (bool)$st->fetch();
  } catch(Throwable $e) { return false; }
}
function pickcol(PDO $pdo, string $table, array $options): ?string {
  foreach ($options as $c) { if (column_exists($pdo, $table, $c)) return $c; }
  return null;
}

/** Flexible audit logger (best-effort). */
function audit_log(PDO $pdo, string $entity, int $entity_id, string $action, array|string $details = []): void {
  try {
    if (!column_exists($pdo,'audit_logs','action') && !column_exists($pdo,'audit_logs','details') && !column_exists($pdo,'audit_logs','data')) {
      return; // table missing or very different schema
    }
    $cols = [];
    $vals = [];
    if (column_exists($pdo,'audit_logs','entity_type')) { $cols[]='entity_type'; $vals[]=$entity; }
    elseif (column_exists($pdo,'audit_logs','entity')) { $cols[]='entity'; $vals[]=$entity; }
    elseif (column_exists($pdo,'audit_logs','module')) { $cols[]='module'; $vals[]=$entity; }

    if (column_exists($pdo,'audit_logs','entity_id')) { $cols[]='entity_id'; $vals[]=$entity_id; }
    elseif (column_exists($pdo,'audit_logs','ref_id')) { $cols[]='ref_id'; $vals[]=$entity_id; }
    elseif (column_exists($pdo,'audit_logs','object_id')) { $cols[]='object_id'; $vals[]=$entity_id; }

    if (column_exists($pdo,'audit_logs','action')) { $cols[]='action'; $vals[]=$action; }

    $payload = is_string($details) ? $details : json_encode($details, JSON_UNESCAPED_UNICODE);
    if (column_exists($pdo,'audit_logs','details')) { $cols[]='details'; $vals[]=$payload; }
    elseif (column_exists($pdo,'audit_logs','data')) { $cols[]='data'; $vals[]=$payload; }
    elseif (column_exists($pdo,'audit_logs','meta')) { $cols[]='meta'; $vals[]=$payload; }

    if (column_exists($pdo,'audit_logs','created_at')) { $cols[]='created_at'; $vals[]=date('Y-m-d H:i:s'); }
    elseif (column_exists($pdo,'audit_logs','logged_at')) { $cols[]='logged_at'; $vals[]=date('Y-m-d H:i:s'); }

    if (!$cols) return;
    $place = implode(',', array_fill(0, count($cols), '?'));
    $sql = "INSERT INTO audit_logs (".implode(',', $cols).") VALUES ($place)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($vals);
  } catch(Throwable $e) { return; }
}

function sendNewHireLink(PDO $pdo, int $applicantId): void {
  // make sure may columns (best-effort only)
  try {
    $pdo->exec("ALTER TABLE applicants
      ADD COLUMN IF NOT EXISTS onboarding_token VARCHAR(64) NULL,
      ADD COLUMN IF NOT EXISTS onboarding_token_expires DATETIME NULL");
  } catch (Throwable $__) {}

  // fetch applicant (email + name)
  $st = $pdo->prepare("SELECT email, COALESCE(full_name,name) AS name FROM applicants WHERE id=?");
  $st->execute([$applicantId]);
  $a = $st->fetch(PDO::FETCH_ASSOC) ?: [];
  $email = trim((string)($a['email'] ?? ''));
  $name  = $a['name'] ?? 'New Hire';
  if ($email === '') return; // no email on file

  // generate token + expiry (7 days)
  try { $token = bin2hex(random_bytes(16)); } catch (Throwable $__) { $token = sha1(uniqid('',true)); }
  $exp = date('Y-m-d H:i:s', time() + 7*24*60*60);
  $pdo->prepare("UPDATE applicants SET onboarding_token=?, onboarding_token_expires=? WHERE id=?")
      ->execute([$token,$exp,$applicantId]);

  // absolute link (ex: http://host/app/newhire.php?t=TOKEN)
  $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') ? 'https' : 'http';
  $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
  $base   = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');         // /api
  $root   = rtrim(preg_replace('~/api$~','',$base),'/');                   // app root
  $link   = "$scheme://$host$root/newhire.php?t=$token";

  $subj = "Welcome to HR1 Nextgenmms – Onboarding";
  $html = "Hi ".htmlspecialchars($name).",<br><br>
           Congratulations! Your application status is now <b>Hired</b>.<br><br>
           Please complete your new-hire requirements using this secure link:<br>
           <a href=\"$link\">$link</a><br><br>
           This link will expire in 7 days.<br><br>
           Thank you,<br>HR1 Nextgenmms – HR Department";

  // send (uses sendHRMail from mail_config.php)
  [$ok,$err] = sendHRMail($email, $subj, $html);

  // best-effort log
  if (column_exists($pdo,'notifications','applicant_id')) {
    if (column_exists($pdo,'notifications','channel')) {
      $pdo->prepare("INSERT INTO notifications
        (applicant_id, channel, subject, message, status_from, status_to, sent_ok, error_text, created_at)
        VALUES (?, 'email', ?, ?, 'hired', 'onboarding', ?, ?, NOW())")
        ->execute([$applicantId,$subj,$html,$ok?1:0, $ok?'':$err]);
    } else {
      $pdo->prepare("INSERT INTO notifications
        (applicant_id, channel_email, channel_sms, subject, message, status_from, status_to, sent_ok, error_text, created_at)
        VALUES (?, 1, 0, ?, ?, 'hired', 'onboarding', ?, ?, NOW())")
        ->execute([$applicantId,$subj,$html,$ok?1:0, $ok?'':$err]);
    }
  }
}


$in = in();
$action = $in['action'] ?? ($_POST['action'] ?? '');

try {

/* ======================= LIST (dashboard data) ======================= */
if ($action === 'list') {
  $q = trim((string)($in['q'] ?? ''));
  $sql = "SELECT req_no, role, site, stage, needed FROM recruitments";
  $args = [];
  if ($q !== '') {
    $sql .= " WHERE req_no LIKE ? OR role LIKE ? OR site LIKE ?";
    $args = ["%$q%","%$q%","%$q%"];
  }
  $sql .= " ORDER BY id DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($args);
  $reqs = [];
  while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $r['stage'] = mapStageOut($r['stage']);
    $reqs[] = $r;
  }

  $apps = []; // (optional on this page)

  $kpi = [
    'open_reqs'      => (int)$pdo->query("SELECT COUNT(*) FROM recruitments")->fetchColumn(),
    'pipeline'       => 0,
    'interviews_wk'  => (int)$pdo->query("
                          SELECT COUNT(*) FROM interview_schedules
                          WHERE YEARWEEK(sched_date, 1) = YEARWEEK(CURDATE(), 1)
                        ")->fetchColumn(),
    'offers'         => (int)$pdo->query("SELECT COUNT(*) FROM recruitments WHERE stage='offer'")->fetchColumn(),
    'stage_counts'   => [
      'New'       => (int)$pdo->query("SELECT COUNT(*) FROM recruitments WHERE stage='screen'")->fetchColumn(),
      'Interview' => (int)$pdo->query("SELECT COUNT(*) FROM recruitments WHERE stage='interview'")->fetchColumn(),
      'Offer'     => (int)$pdo->query("SELECT COUNT(*) FROM recruitments WHERE stage='offer'")->fetchColumn(),
      'Closed'    => (int)$pdo->query("SELECT COUNT(*) FROM recruitments WHERE stage='accepted'")->fetchColumn(),
      'Rejected'  => (int)$pdo->query("SELECT COUNT(*) FROM recruitments WHERE stage='rejected'")->fetchColumn(),
    ],
  ];

  ok(['requisitions'=>$reqs, 'applicants'=>$apps, 'kpi'=>$kpi]);
}

/* =================== ADD REQUISITION =================== */
if ($action === 'add_req') {
  $role   = trim((string)($in['role'] ?? ''));
  $needed = max(1, (int)($in['needed'] ?? 1));
  $stage  = mapStageIn($in['stage'] ?? 'screen');
  if ($role === '') fail('Role is required');

  $yr = date('Y');
  $stmt = $pdo->prepare("
    SELECT MAX(CAST(SUBSTRING_INDEX(req_no,'-',-1) AS UNSIGNED))
    FROM recruitments WHERE req_no LIKE ?
  ");
  $stmt->execute(["REQ-$yr-%"]);
  $next = ((int)$stmt->fetchColumn()) + 1;

  $req_no = '';
  for ($i=0; $i<3; $i++) {
    $req_no = sprintf('REQ-%s-%03d', $yr, $next);
    try {
      $stmt2 = $pdo->prepare("INSERT INTO recruitments (req_no, role, site, stage, needed) VALUES (?,?,?,?,?)");
      $stmt2->execute([$req_no, $role, '', $stage, $needed]);
      break;
    } catch (PDOException $e) {
      if (($e->errorInfo[1] ?? 0) == 1062) { $next++; continue; }
      throw $e;
    }
  }

  ok(['saved'=>true,'req_no'=>$req_no]);
}

/* ================== UPDATE / DELETE REQ ================== */
if ($action === 'update_req') {
  $req_no = trim((string)($in['req_no'] ?? ''));
  if ($req_no === '') fail('req_no is required');
  $role   = trim((string)($in['role'] ?? ''));
  $site   = trim((string)($in['site'] ?? ''));
  $needed = max(1, (int)($in['needed'] ?? 1));
  $stage  = mapStageIn($in['stage'] ?? 'screen');

  $stmt = $pdo->prepare("UPDATE recruitments SET role=?, site=?, needed=?, stage=? WHERE req_no=?");
  $stmt->execute([$role, $site, $needed, $stage, $req_no]);
  ok(['updated'=> (bool)$stmt->rowCount(), 'req_no'=>$req_no]);
}

if ($action === 'delete_req') {
  $req_no = trim((string)($in['req_no'] ?? ''));
  if ($req_no === '') fail('req_no is required');
  $stmt = $pdo->prepare("DELETE FROM recruitments WHERE req_no=?");
  $stmt->execute([$req_no]);
  ok(['deleted'=> (bool)$stmt->rowCount(), 'req_no'=>$req_no]);
}

/* ================== SCHEDULES (REQUISITIONS) ================== */

/* ---- Save schedule (frontend calls: schedule.save) ---- */
if ($action === 'schedule.save' || $action === 'schedule_req') {
  $req_no = trim((string)($in['req_no'] ?? ''));
  $dateIn = trim((string)($in['date']   ?? ''));
  $t1In   = trim((string)($in['start']  ?? ''));
  $t2In   = trim((string)($in['end']    ?? ''));
  $panel  = trim((string)($in['panel']  ?? ''));

  if ($req_no === '') fail('req_no is required');
  if ($dateIn === '') fail('date is required');

  $d = preg_match('~^\d{4}-\d{2}-\d{2}$~',$dateIn) ? $dateIn : date('Y-m-d', strtotime($dateIn));
  $t1 = $t1In !== '' ? date('H:i:s', strtotime($t1In)) : '00:00:00';
  $t2 = $t2In !== '' ? date('H:i:s', strtotime($t2In)) : '00:00:00';

  $stmt = $pdo->prepare("
    INSERT INTO interview_schedules (sched_date, start_time, end_time, req_no, panel)
    VALUES (?, ?, ?, ?, ?)
  ");
  $stmt->execute([$d, $t1, $t2, $req_no, $panel]);

  ok(['scheduled'=>true]);
}

/* ---- List schedules (hide done by default) ---- */
if ($action === 'schedule.list') {
  $show = strtolower((string)($in['show'] ?? ''));
  $where = ($show === 'all') ? "WHERE 1=1" : "WHERE s.is_done = 0";

  $rows = $pdo->query("
    SELECT s.id,
           s.sched_date,
           s.start_time,
           s.end_time,
           s.req_no,
           COALESCE(r.role,'')  AS role,
           COALESCE(s.panel,'') AS panel,
           s.is_done
    FROM interview_schedules s
    LEFT JOIN recruitments r ON r.req_no = s.req_no
    $where
    ORDER BY s.sched_date ASC, s.start_time ASC, s.id ASC
  ")->fetchAll(PDO::FETCH_ASSOC);

  $out = array_map(function($r){
    return [
      'id'     => (int)$r['id'],
      'date'   => $r['sched_date'],
      'start'  => $r['start_time'],
      'end'    => $r['end_time'],
      'req_no' => $r['req_no'],
      'role'   => $r['role'],
      'panel'  => $r['panel'],
      'done'   => (int)$r['is_done'] === 1,
    ];
  }, $rows);

  ok($out);
}


/* ---- Mark schedule as done ---- */
if ($action === 'schedule.done') {
  $id = (int)($in['id'] ?? 0);
  if ($id <= 0) fail('Invalid schedule id');
  $stmt = $pdo->prepare("UPDATE interview_schedules SET is_done = 1 WHERE id = ?");
  $stmt->execute([$id]);
  ok(['done' => (bool)$stmt->rowCount()]);
}

/* ---- (optional) Undo done ---- */
if ($action === 'schedule.undo') {
  $id = (int)($in['id'] ?? 0);
  if ($id <= 0) fail('Invalid schedule id');
  $stmt = $pdo->prepare("UPDATE interview_schedules SET is_done = 0 WHERE id = ?");
  $stmt->execute([$id]);
  ok(['undone' => (bool)$stmt->rowCount()]);
}

/* ---- Delete schedule ---- */
if ($action === 'schedule.delete') {
  $id = (int)($in['id'] ?? 0);
  if ($id <= 0) fail('Invalid schedule id');
  $stmt = $pdo->prepare("DELETE FROM interview_schedules WHERE id=?");
  $stmt->execute([$id]);
  ok(['deleted'=> (bool)$stmt->rowCount()]);
}

/* ================== APPLICANTS (REAL DB WRITES) ================== */

/* ================================================================
   COMPATIBILITY BLOCK for recruitment.php UI
   - adds: submit_score, set_status, schedule, notify, toggle_shortlist, approve
   - also adds helpers for picking shortlist/score columns
   ================================================================= */

/* ---- Column pickers (helpers) ---- */
function pick_applicant_shortlist_col(PDO $pdo): string {
  if (column_exists($pdo,'applicants','shortlisted'))   return 'shortlisted';
  if (column_exists($pdo,'applicants','is_shortlisted')) return 'is_shortlisted';
  // try to add if none exists (best-effort)
  try {
    $pdo->exec("ALTER TABLE applicants ADD COLUMN shortlisted TINYINT(1) NOT NULL DEFAULT 0");
    return 'shortlisted';
  } catch (Throwable $e) {}
  return '';
}
function pick_applicant_score_col(PDO $pdo): string {
  if (column_exists($pdo,'applicants','score'))  return 'score';
  if (column_exists($pdo,'applicants','rating')) return 'rating';
  return '';
}

/* ================== submit_score ================== */
/* Accepts either:
   - score (0–100) alone, OR
   - communication, experience, culture_fit (each 0–100). Average is stored.
   Writes to eval_forms (upsert) and applicants.score/rating if column exists. */
if ($action === 'submit_score') {
  $id   = (int)($in['applicant_id'] ?? ($_POST['applicant_id'] ?? 0));
  if ($id <= 0) fail('Invalid applicant_id');

  $comm = isset($in['communication']) ? (int)$in['communication'] : (isset($_POST['communication']) ? (int)$_POST['communication'] : -1);
  $exp  = isset($in['experience'])    ? (int)$in['experience']    : (isset($_POST['experience'])    ? (int)$_POST['experience']    : -1);
  $fit  = isset($in['culture_fit'])   ? (int)$in['culture_fit']   : (isset($_POST['culture_fit'])   ? (int)$_POST['culture_fit']   : -1);
  $hasParts = ($comm>=0 && $exp>=0 && $fit>=0);

  $score = isset($in['score']) ? (float)$in['score'] : (isset($_POST['score']) ? (float)$_POST['score'] : null);
  if ($hasParts) {
    if ($comm>100 || $exp>100 || $fit>100) fail('Metrics must be 0–100');
    $score = round(($comm + $exp + $fit) / 3, 2);
  }
  if ($score === null || $score < 0 || $score > 100) fail('Score (0–100) is required');

  // Upsert into eval_forms (best-effort, tolerant to schema)
  try {
    if (column_exists($pdo,'eval_forms','id')) {
      if (!column_exists($pdo,'eval_forms','score')) $pdo->exec("ALTER TABLE eval_forms ADD COLUMN score DECIMAL(5,2) DEFAULT NULL");
      $adds = [];
      if ($hasParts) {
        if (!column_exists($pdo,'eval_forms','communication')) $adds[]="ADD COLUMN communication TINYINT(4) DEFAULT NULL";
        if (!column_exists($pdo,'eval_forms','experience'))    $adds[]="ADD COLUMN experience TINYINT(4) DEFAULT NULL";
        if (!column_exists($pdo,'eval_forms','culture_fit'))   $adds[]="ADD COLUMN culture_fit TINYINT(4) DEFAULT NULL";
      }
      if ($adds) { try { $pdo->exec("ALTER TABLE eval_forms ".implode(',', $adds)); } catch(Throwable $__){ } }

      $chk = $pdo->prepare("SELECT id FROM eval_forms WHERE applicant_id=?");
      $chk->execute([$id]); $row = $chk->fetch(PDO::FETCH_ASSOC);

      if ($row) {
        $sql = "UPDATE eval_forms SET score=?, created_at=NOW()".
               ($hasParts ? ", communication=?, experience=?, culture_fit=?" : "").
               " WHERE id=?";
        $vals = $hasParts ? [$score,$comm,$exp,$fit,(int)$row['id']] : [$score,(int)$row['id']];
        $pdo->prepare($sql)->execute($vals);
      } else {
        $cols = "applicant_id, score, created_at";
        $qs   = "?, ?, NOW()";
        $vals = [$id,$score];
        if ($hasParts) { $cols.=", communication, experience, culture_fit"; $qs.=", ?, ?, ?"; array_push($vals,$comm,$exp,$fit); }
        $pdo->prepare("INSERT INTO eval_forms ($cols) VALUES ($qs)")->execute($vals);
      }
    }
  } catch (Throwable $__){ /* ignore */ }

  // Also try to store into applicants.score/rating
  if ($col = pick_applicant_score_col($pdo)) {
    $pdo->prepare("UPDATE applicants SET `$col`=? WHERE id=?")->execute([$score,$id]);
  }

  audit_log($pdo, 'applicant', $id, 'submit_score', ['score'=>$score,'parts'=>$hasParts?['communication'=>$comm,'experience'=>$exp,'culture_fit'=>$fit]:null]);
  ok(['applicant_id'=>$id,'score'=>$score]);
}

/* ================== set_status ================== */
if ($action === 'set_status') {
  $id  = (int)($in['applicant_id'] ?? ($_POST['applicant_id'] ?? 0));
  $st  = (string)($in['status'] ?? ($_POST['status'] ?? ''));
  if ($id <= 0) fail('Invalid applicant_id');
  if ($st === '') fail('Missing status');

  $status = mapApplicantStatus($st);
  $pdo->prepare("UPDATE applicants SET status=? WHERE id=?")->execute([$status,$id]);

  // Auto-add to employees when hired
  if ($status === 'hired') {
    require_once __DIR__ . '/employees_lib.php';
    insert_or_update_employee_from_applicant($pdo, (int)$id);
  }

    /* === AUTO-CREATE ONBOARDING PLAN WHEN HIRED === */
  if (stripos($status, 'hired') !== false) {
    try {
      $a = $pdo->prepare("SELECT full_name, name, role, site FROM applicants WHERE id=?");
      $a->execute([$id]);
      $ar = $a->fetch(PDO::FETCH_ASSOC) ?: [];

      $hireName = $ar['full_name'] ?: ($ar['name'] ?? 'Applicant #'.$id);
      $role     = $ar['role'] ?? 'New Hire';
      $site     = $ar['site'] ?? 'Main Branch';
      $start    = date('Y-m-d');

      // Check if already exists
      $exists = false;
      if (column_exists($pdo, 'onboarding_plans', 'applicant_id')) {
        $s = $pdo->prepare("SELECT id FROM onboarding_plans WHERE applicant_id=?");
        $s->execute([$id]);
        $exists = (bool)$s->fetch();
      } else {
        $s = $pdo->prepare("SELECT id FROM onboarding_plans WHERE hire_name=?");
        $s->execute([$hireName]);
        $exists = (bool)$s->fetch();
      }

      if (!$exists) {
        file_put_contents(__DIR__ . '/../debug.log', "🟡 Creating onboarding plan for #$id ($hireName)\n", FILE_APPEND);

        $cols = "hire_name, role, site, start_date, status, progress, created_at";
        $vals = [$hireName, $role, $site, $start, 'Pending', 0, date('Y-m-d H:i:s')];
        if (column_exists($pdo, 'onboarding_plans', 'applicant_id')) {
          $cols = "applicant_id, " . $cols;
          array_unshift($vals, $id);
        }
        $pdo->prepare("INSERT INTO onboarding_plans ($cols) VALUES (" . str_repeat('?,', count($vals)-1) . "?)")->execute($vals);
        file_put_contents(__DIR__ . '/../debug.log', "✅ Onboarding plan added for applicant #$id\n", FILE_APPEND);
      } else {
        file_put_contents(__DIR__ . '/../debug.log', "ℹ️ Onboarding already exists for applicant #$id\n", FILE_APPEND);
      }
    } catch (Throwable $e) {
      file_put_contents(__DIR__ . '/../debug.log', "❌ Onboarding auto-create failed: ".$e->getMessage()."\n", FILE_APPEND);
    }
  }


  // 👉 ADD THIS: send the New Hire link email when hired
  if ($status === 'hired') {
    sendNewHireLink($pdo, (int)$id);
  }

  audit_log($pdo, 'applicant', $id, 'set_status', ['status'=>$status]);
  ok(['id'=>$id,'status'=>$status]);
}




/* ================== schedule (alias of applicant.schedule) ================== */
if ($action === 'schedule') {
  $id    = (int)($in['applicant_id'] ?? 0);
  $date  = trim((string)($in['date'] ?? ''));
  $start = trim((string)($in['start'] ?? ($in['time'] ?? '')));
  $end   = trim((string)($in['end'] ?? ''));
  $mode  = trim((string)($in['mode'] ?? 'On-site'));
  $notes = trim((string)($in['panel'] ?? ($in['notes'] ?? '')));
  if ($id <= 0) fail('Invalid applicant_id');
  if ($date === '' || $start === '') fail('Date and time are required');

  $d = preg_match('~^\d{4}-\d{2}-\d{2}$~',$date) ? $date : date('Y-m-d', strtotime($date));
  $t = date('H:i:s', strtotime($start));
  $scheduled_at = $d.' '.$t;

  if (column_exists($pdo,'interviews','scheduled_at')) {
    $pdo->prepare("INSERT INTO interviews (applicant_id, scheduled_at, mode, notes, created_at) VALUES (?,?,?,?,NOW())")
        ->execute([$id,$scheduled_at,$mode,$notes]);
  } else {
    $pdo->prepare("INSERT INTO interviews (applicant_id, date, time, mode, notes, created_at) VALUES (?,?,?,?,?,NOW())")
        ->execute([$id,$d,$t,$mode,$notes]);
  }
 $pdo->prepare("UPDATE applicants SET status='interview' WHERE id=?")->execute([$id]);


  audit_log($pdo, 'applicant', $id, 'schedule_interview', ['scheduled_at'=>$scheduled_at,'mode'=>$mode,'notes'=>$notes]);
  ok(['id'=>$id,'scheduled_at'=>$scheduled_at,'mode'=>$mode]);
}

/* ================== notify (alias of applicant.notify) ================== */
if ($action === 'notify') {
  $id        = (int)($in['applicant_id'] ?? 0);
  $msg       = trim((string)($in['message'] ?? ''));
  $viaE      = (int)($in['via_email'] ?? 1);
  $viaS      = (int)($in['via_sms']   ?? 0);
  $statusIn  = trim((string)($in['status'] ?? 'update'));
  $toEmailIn = trim((string)($in['to_email'] ?? ''));

  if ($id <= 0) fail('Invalid applicant_id');
  if ($msg === '') fail('Message is empty');

  $st = $pdo->prepare("SELECT name, full_name, email, status FROM applicants WHERE id=?");
  $st->execute([$id]);
  $app = $st->fetch(PDO::FETCH_ASSOC);
  if (!$app) fail('Applicant not found', 404);

  $dbEmail = trim((string)($app['email'] ?? ''));
  $toEmail = $toEmailIn !== '' ? $toEmailIn : $dbEmail;
  $status  = $statusIn !== '' ? $statusIn : ($app['status'] ?? 'update');
  $subject = "Application Update – " . ucfirst($status);

  $results = [];

  // Email
  if ($viaE) {
    if ($toEmail === '') {
      $results[] = ['channel'=>'email','ok'=>false,'error'=>'No email on file'];
      $sentOk=false; $err='No email on file';
    } else {
      [$sentOk, $err] = sendHRMail($toEmail, $subject, $msg);
      $results[] = ['channel'=>'email','ok'=>$sentOk,'error'=>$err];
    }
    if (column_exists($pdo,'notifications','applicant_id')) {
      if (column_exists($pdo,'notifications','channel')) {
        $pdo->prepare("INSERT INTO notifications
            (applicant_id, channel, subject, message, status_from, status_to, sent_ok, error_text, created_at)
            VALUES (?, 'email', ?, ?, ?, ?, ?, ?, NOW())")
            ->execute([$id,$subject,$msg,(string)($app['status'] ?? ''),$status,$sentOk?1:0,$err]);
      } else {
        $pdo->prepare("INSERT INTO notifications
            (applicant_id, channel_email, channel_sms, subject, message, status_from, status_to, sent_ok, error_text, created_at)
            VALUES (?, 1, 0, ?, ?, ?, ?, ?, ?, NOW())")
            ->execute([$id,$subject,$msg,(string)($app['status'] ?? ''),$status,$sentOk?1:0,$err]);
      }
    }
  }

  // SMS placeholder
  if ($viaS) {
    if (column_exists($pdo,'notifications','applicant_id')) {
      if (column_exists($pdo,'notifications','channel')) {
        $pdo->prepare("INSERT INTO notifications
            (applicant_id, channel, subject, message, status_from, status_to, sent_ok, error_text, created_at)
            VALUES (?, 'sms', ?, ?, ?, ?, 0, 'SMS not configured', NOW())")
            ->execute([$id,$subject,$msg,(string)($app['status'] ?? ''),$status]);
      } else {
        $pdo->prepare("INSERT INTO notifications
            (applicant_id, channel_email, channel_sms, subject, message, status_from, status_to, sent_ok, error_text, created_at)
            VALUES (?, 0, 1, ?, ?, ?, ?, 0, 'SMS not configured', NOW())")
            ->execute([$id,$subject,$msg,(string)($app['status'] ?? ''),$status]);
      }
    }
    $results[] = ['channel'=>'sms','ok'=>false,'error'=>'SMS not configured'];
  }

  audit_log($pdo, 'applicant', $id, 'notify', ['email'=>$viaE,'sms'=>$viaS,'to'=>$toEmail]);
  ok(['id'=>$id,'results'=>$results]);
}

/* ================== toggle_shortlist ================== */
if ($action === 'toggle_shortlist') {
  $id = (int)($in['applicant_id'] ?? 0);
  if ($id <= 0) fail('Invalid applicant_id');

  $col = pick_applicant_shortlist_col($pdo);
  if ($col === '') fail('No shortlist column available on applicants table');

  $st = $pdo->prepare("SELECT `$col` FROM applicants WHERE id=?");
  $st->execute([$id]);
  $cur = (int)($st->fetchColumn() ?? 0);
  $new = $cur ? 0 : 1;

  $pdo->prepare("UPDATE applicants SET `$col`=? WHERE id=?")->execute([$new,$id]);

  audit_log($pdo, 'applicant', $id, 'toggle_shortlist', ['shortlisted'=>$new]);
  ok(['applicant_id'=>$id,'shortlisted'=>($new===1)]);
}

/* ================== approve (quick hire) ================== */
if ($action === 'approve') {
  $id = (int)($in['applicant_id'] ?? 0);
  if ($id <= 0) fail('Invalid applicant_id');

  $pdo->prepare("UPDATE applicants SET status='hired' WHERE id=?")->execute([$id]);

  // Create onboarding plan if not present
  try {
    $a = $pdo->prepare("SELECT name, full_name, role, site FROM applicants WHERE id=?");
    $a->execute([$id]);
    $ar = $a->fetch(PDO::FETCH_ASSOC) ?: [];
    $hire  = $ar['name'] ?? ($ar['full_name'] ?? ('Applicant #'.$id));
    $role  = $ar['role'] ?? '';
    $site  = $ar['site'] ?? '';
    $start = date('Y-m-d');
    if (column_exists($pdo,'onboarding_plans','applicant_id')) {
      $s = $pdo->prepare("SELECT id FROM onboarding_plans WHERE applicant_id=?");
      $s->execute([$id]); $exists = (bool)$s->fetch();
      if (!$exists) {
        $pdo->prepare("INSERT INTO onboarding_plans (applicant_id, hire_name, role, site, start_date, status, progress, created_at)
                       VALUES (?,?,?,?,?,'Pending',0,NOW())")
            ->execute([$id,$hire,$role,$site,$start]);
      }
    }
  } catch (Throwable $__){ }

  audit_log($pdo, 'applicant', $id, 'approve', []);
  ok(['id'=>$id,'status'=>'hired']);
}
/* ================== END COMPAT BLOCK ================== */


/* ---- EVALUATION: save Communication/Experience/Culture Fit + Status ---- */
if ($action === 'evaluate') {
  try {
    // Accept JSON body or form data
    $applicant_id = (int)($in['applicant_id'] ?? ($_POST['applicant_id'] ?? 0));
    $comm = (int)($in['communication'] ?? ($_POST['communication'] ?? -1));
    $exp  = (int)($in['experience']    ?? ($_POST['experience'] ?? -1));
    $fit  = (int)($in['culture_fit']   ?? ($_POST['culture_fit'] ?? -1));
    $statusIn = (string)($in['status'] ?? ($_POST['status'] ?? ''));
    $remarks  = trim((string)($in['remarks'] ?? ($_POST['remarks'] ?? '')));
    $template = trim((string)($in['template'] ?? ($_POST['template'] ?? 'Submitted')));

    if ($applicant_id <= 0)           fail('Missing applicant_id');
    if ($comm < 0 || $comm > 100)     fail('Invalid Communication (0-100)');
    if ($exp  < 0 || $exp  > 100)     fail('Invalid Experience (0-100)');
    if ($fit  < 0 || $fit  > 100)     fail('Invalid Culture Fit (0-100)');
    if ($statusIn === '')             fail('Missing status');

    $score  = round(($comm + $exp + $fit) / 3, 2);
    $status = mapApplicantStatus($statusIn);

    // Ensure columns exist in eval_forms (best-effort, no fatal)
    if (column_exists($pdo,'eval_forms','id')) {
      // silently try to add missing metric columns
      $adds = [];
      if (!column_exists($pdo,'eval_forms','communication')) $adds[] = "ADD COLUMN communication TINYINT(4) DEFAULT NULL";
      if (!column_exists($pdo,'eval_forms','experience'))    $adds[] = "ADD COLUMN experience TINYINT(4) DEFAULT NULL";
      if (!column_exists($pdo,'eval_forms','culture_fit'))   $adds[] = "ADD COLUMN culture_fit TINYINT(4) DEFAULT NULL";
      if ($adds) {
        try { $pdo->exec("ALTER TABLE eval_forms ".implode(',', $adds)); } catch(Throwable $__){ /* ignore */ }
      }
    }

    $pdo->beginTransaction();

    // upsert to eval_forms per applicant
    $chk = $pdo->prepare("SELECT id FROM eval_forms WHERE applicant_id=?");
    $chk->execute([$applicant_id]);
    $existing = $chk->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
      $u = $pdo->prepare("
        UPDATE eval_forms
           SET template=?, status='Submitted',
               communication=?, experience=?, culture_fit=?, score=?, remarks=?,
               created_at = NOW()
         WHERE id=?
      ");
      $u->execute([$template, $comm, $exp, $fit, $score, $remarks, (int)$existing['id']]);
    } else {
      $i = $pdo->prepare("
        INSERT INTO eval_forms
          (applicant_id, template, status, communication, experience, culture_fit, score, remarks, created_at)
        VALUES (?,?,?,?,?,?,?,?, NOW())
      ");
      $i->execute([$applicant_id, $template, 'Submitted', $comm, $exp, $fit, $score, $remarks]);
    }

    // update applicants.status (respect enum)
    if (column_exists($pdo,'applicants','status')) {
      $updA = $pdo->prepare("UPDATE applicants SET status=? WHERE id=?");
      $updA->execute([$status, $applicant_id]);
    }

    $pdo->commit();

    audit_log($pdo, 'applicant', $applicant_id, 'evaluate', [
      'communication'=>$comm,'experience'=>$exp,'culture_fit'=>$fit,'score'=>$score,'status'=>$status
    ]);

    ok([
      'applicant_id'=>$applicant_id,
      'communication'=>$comm,'experience'=>$exp,'culture_fit'=>$fit,
      'score'=>$score,'status'=>$status
    ]);

  } catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fail($e->getMessage());
  }
}

/* ---- Update status ---- */
if ($action === 'applicant.update_status') {
  $id  = (int)($in['applicant_id'] ?? 0);
  $st  = trim((string)($in['status'] ?? 'New'));
  if ($id <= 0) fail('Invalid applicant_id');

  // normalize + save
  $status = mapApplicantStatus($st);
  $pdo->prepare("UPDATE applicants SET status=? WHERE id=?")->execute([$status,$id]);

  /* === AUTO-ADD TO EMPLOYEES WHEN HIRED === */
  if (stripos($status, 'hired') !== false) {
    try {
      require_once __DIR__ . '/employees_api.php';
      if (function_exists('insert_or_update_employee_from_applicant')) {
        insert_or_update_employee_from_applicant($pdo, (int)$id);
      }
    } catch (Throwable $__) { /* ignore best-effort */ }
  }

  /* === SEND NEW-HIRE LINK EMAIL (newhire.php) WHEN HIRED === */
  if (stripos($status, 'hired') !== false) {
    try { sendNewHireLink($pdo, (int)$id); } catch (Throwable $__) { /* ignore */ }
  }

    /* === AUTO-CREATE ONBOARDING PLAN WHEN HIRED === */
  if (stripos($status, 'hired') !== false) {
    try {
      $a = $pdo->prepare("SELECT full_name, name, role, site FROM applicants WHERE id=?");
      $a->execute([$id]);
      $ar = $a->fetch(PDO::FETCH_ASSOC) ?: [];

      $hireName = $ar['full_name'] ?: ($ar['name'] ?? 'Applicant #'.$id);
      $role     = $ar['role'] ?? 'New Hire';
      $site     = $ar['site'] ?? 'Main Branch';
      $start    = date('Y-m-d');

      // Check if already exists
      $exists = false;
      if (column_exists($pdo, 'onboarding_plans', 'applicant_id')) {
        $s = $pdo->prepare("SELECT id FROM onboarding_plans WHERE applicant_id=?");
        $s->execute([$id]);
        $exists = (bool)$s->fetch();
      } else {
        $s = $pdo->prepare("SELECT id FROM onboarding_plans WHERE hire_name=?");
        $s->execute([$hireName]);
        $exists = (bool)$s->fetch();
      }

      if (!$exists) {
        file_put_contents(__DIR__ . '/../debug.log', "🟡 Creating onboarding plan for #$id ($hireName)\n", FILE_APPEND);

        $cols = "hire_name, role, site, start_date, status, progress, created_at";
        $vals = [$hireName, $role, $site, $start, 'Pending', 0, date('Y-m-d H:i:s')];
        if (column_exists($pdo, 'onboarding_plans', 'applicant_id')) {
          $cols = "applicant_id, " . $cols;
          array_unshift($vals, $id);
        }
        $pdo->prepare("INSERT INTO onboarding_plans ($cols) VALUES (" . str_repeat('?,', count($vals)-1) . "?)")->execute($vals);
        file_put_contents(__DIR__ . '/../debug.log', "✅ Onboarding plan added for applicant #$id\n", FILE_APPEND);
      } else {
        file_put_contents(__DIR__ . '/../debug.log', "ℹ️ Onboarding already exists for applicant #$id\n", FILE_APPEND);
      }
    } catch (Throwable $e) {
      file_put_contents(__DIR__ . '/../debug.log', "❌ Onboarding auto-create failed: ".$e->getMessage()."\n", FILE_APPEND);
    }
  }


  audit_log($pdo, 'applicant', $id, 'update_status', ['status'=>$status]);
  ok(['id'=>$id,'status'=>$status]);
}



/* ---- Archive applicant ---- */
if ($action === 'applicant.archive') {
  $id  = (int)($in['applicant_id'] ?? 0);
  if ($id <= 0) fail('Invalid applicant_id');

  if (column_exists($pdo,'applicants','archived')) {
    $pdo->prepare("UPDATE applicants SET archived=1 WHERE id=?")->execute([$id]);
  } else {
    $pdo->prepare("UPDATE applicants SET status='Archived' WHERE id=?")->execute([$id]);
  }
  audit_log($pdo, 'applicant', $id, 'archive', []);
  ok(['id'=>$id]);
}

/* ---------- UNARCHIVE APPLICANT (safe audit) ---------- */
if ($action === 'applicant.unarchive') {
  $id = (int)($in['applicant_id'] ?? 0);
  if ($id <= 0) fail('Invalid applicant_id');

  // helper kung wala pa
  if (!function_exists('column_exists')) {
    function column_exists(PDO $pdo, string $table, string $col): bool {
      try {
        $st = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
        $st->execute([$col]);
        return (bool)$st->fetch();
      } catch (Throwable $e) { return false; }
    }
  }

  // hanapin kung anong archived flag ang meron
  $colArch = column_exists($pdo,'applicants','archived') ? 'archived'
           : (column_exists($pdo,'applicants','is_archived') ? 'is_archived' : '');
  if ($colArch === '') fail('Archived column not found on applicants');

  // optional status/stage column — ibalik sa "Pending"
  $colStatus = column_exists($pdo,'applicants','status') ? 'status'
             : (column_exists($pdo,'applicants','stage') ? 'stage' : '');

  $sql = "UPDATE applicants SET `$colArch`=0" . ($colStatus ? ", `$colStatus`='Pending'" : "") . " WHERE id=?";
  $st  = $pdo->prepare($sql);
  $st->execute([$id]);

  /* --- Safe audit insert (optional): mag-iinsert lang ng mga column na meron --- */
  if (column_exists($pdo,'audit_logs','id')) {
    $cols=[]; $vals=[]; $args=[];

    if (column_exists($pdo,'audit_logs','entity_type')) { $cols[]='entity_type'; $vals[]='?'; $args[]='applicant'; }
    elseif (column_exists($pdo,'audit_logs','entity'))  { $cols[]='entity';      $vals[]='?'; $args[]='applicant'; }

    if (column_exists($pdo,'audit_logs','entity_id'))   { $cols[]='entity_id';   $vals[]='?'; $args[]=$id; }
    if (column_exists($pdo,'audit_logs','action'))      { $cols[]='action';      $vals[]='?'; $args[]='unarchive'; }
    if (column_exists($pdo,'audit_logs','details'))     { $cols[]='details';     $vals[]='?'; $args[]=json_encode(['applicant_id'=>$id], JSON_UNESCAPED_UNICODE); }
    if (column_exists($pdo,'audit_logs','created_at'))  { $cols[]='created_at';  $vals[]='NOW()'; }

    if ($cols) {
      $sql = "INSERT INTO audit_logs (".implode(',',$cols).") VALUES (".implode(',',$vals).")";
      $pdo->prepare($sql)->execute($args);
    }
  }
  ok(['restored'=>(bool)$st->rowCount()]);
}


/* ---- Permanently delete applicant ---- */
if ($action === 'applicant.delete') {
  $id = (int)($in['applicant_id'] ?? 0);
  if ($id <= 0) fail('Invalid applicant_id');

  try {
    $pdo->beginTransaction();

    if (column_exists($pdo,'interviews','applicant_id')) {
      $pdo->prepare("DELETE FROM interviews WHERE applicant_id=?")->execute([$id]);
    }
    if (column_exists($pdo,'notifications','applicant_id')) {
      $pdo->prepare("DELETE FROM notifications WHERE applicant_id=?")->execute([$id]);
    }

    if (column_exists($pdo,'onboarding_plans','applicant_id')) {
      if (column_nullable($pdo,'onboarding_plans','applicant_id')) {
        $pdo->prepare("UPDATE onboarding_plans SET applicant_id=NULL WHERE applicant_id=?")->execute([$id]);
      } else {
        $pdo->prepare("DELETE FROM onboarding_plans WHERE applicant_id=?")->execute([$id]);
      }
    }

    if (column_exists($pdo,'employees','applicant_id')) {
      $q = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE applicant_id=?");
      $q->execute([$id]); $cnt = (int)$q->fetchColumn();
      if ($cnt > 0) {
        if (column_nullable($pdo,'employees','applicant_id')) {
          $pdo->prepare("UPDATE employees SET applicant_id=NULL WHERE applicant_id=?")->execute([$id]);
        } else {
          $pdo->rollBack();
          fail('Cannot delete: this applicant is linked to employee record(s). Unlink or delete those employees first, or archive this applicant instead.');
        }
      }
    }

    $pdo->prepare("DELETE FROM applicants WHERE id=?")->execute([$id]);

    $pdo->commit();
    audit_log($pdo, 'applicant', $id, 'delete', []);
    ok(['deleted'=>true,'id'=>$id]);

  } catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if ((int)($e->errorInfo[1] ?? 0) === 1451) {
      fail('Cannot delete: this applicant is referenced by other records (e.g., employees). Unlink them or archive instead.');
    }
    fail($e->getMessage(), 500);
  }
}

/* ---- Schedule interview for an applicant (flexible columns) ---- */
if ($action === 'applicant.schedule') {
  $id    = (int)($in['applicant_id'] ?? 0);
  $date  = trim((string)($in['date'] ?? ''));
  $time  = trim((string)($in['time'] ?? ''));
  $mode  = trim((string)($in['mode'] ?? 'On-site'));
  $notes = trim((string)($in['notes'] ?? ''));

  if ($id <= 0) fail('Invalid applicant_id');
  if ($date === '' || $time === '') fail('Date and time are required');

  // Normalized values
  $d = preg_match('~^\d{4}-\d{2}-\d{2}$~', $date) ? $date : date('Y-m-d', strtotime($date));
  $t = date('H:i:s', strtotime($time));
  $scheduled_at = $d.' '.$t;

  // Make sure table exists (best-effort minimal)
  try {
    $pdo->query("SELECT 1 FROM interviews LIMIT 1");
  } catch (Throwable $__) {
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS interviews (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        applicant_id INT NOT NULL,
        scheduled_at DATETIME NULL,
        mode VARCHAR(60) NULL,
        notes TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
  }

  // Build INSERT only with columns that exist on your schema
  $cols = ['applicant_id']; $vals = [$id]; $qs = ['?'];

  if (column_exists($pdo,'interviews','scheduled_at')) { $cols[]='scheduled_at'; $vals[]=$scheduled_at; $qs[]='?'; }
  if (column_exists($pdo,'interviews','date'))         { $cols[]='date';         $vals[]=$d;           $qs[]='?'; }
  if (column_exists($pdo,'interviews','interview_date')){ $cols[]='interview_date'; $vals[]=$d;        $qs[]='?'; }
  if (column_exists($pdo,'interviews','sched_date'))   { $cols[]='sched_date';   $vals[]=$d;           $qs[]='?'; }

  if (column_exists($pdo,'interviews','time'))         { $cols[]='time';         $vals[]=$t;           $qs[]='?'; }
  if (column_exists($pdo,'interviews','start_time'))   { $cols[]='start_time';   $vals[]=$t;           $qs[]='?'; }

  if (column_exists($pdo,'interviews','mode'))         { $cols[]='mode';         $vals[]=$mode;        $qs[]='?'; }
  if (column_exists($pdo,'interviews','notes'))        { $cols[]='notes';        $vals[]=$notes;       $qs[]='?'; }
  if (column_exists($pdo,'interviews','created_at'))   { $cols[]='created_at';   $vals[] = date('Y-m-d H:i:s'); $qs[]='?'; }

  // Kung wala kahit isang date/time column na available, gamitin scheduled_at bilang fallback
  if (count($cols) === 1 /* only applicant_id so far */) {
    // ensure scheduled_at exists
    if (!column_exists($pdo,'interviews','scheduled_at')) {
      try { $pdo->exec("ALTER TABLE interviews ADD COLUMN scheduled_at DATETIME NULL"); } catch (Throwable $__) {}
    }
    $cols[]='scheduled_at'; $vals[]=$scheduled_at; $qs[]='?';
    if (column_exists($pdo,'interviews','mode'))  { $cols[]='mode';  $vals[]=$mode;  $qs[]='?'; }
    if (column_exists($pdo,'interviews','notes')) { $cols[]='notes'; $vals[]=$notes; $qs[]='?'; }
  }

  $sql = "INSERT INTO interviews (".implode(',',$cols).") VALUES (".implode(',',$qs).")";
  $pdo->prepare($sql)->execute($vals);

  // Tag applicant to screening (neutral)
  if (column_exists($pdo,'applicants','status')) {
  $pdo->prepare("UPDATE applicants SET status='interview' WHERE id=?")->execute([$id]);
  }


  audit_log($pdo, 'applicant', $id, 'schedule_interview', [
    'scheduled_at'=>$scheduled_at,'mode'=>$mode,'notes'=>$notes
  ]);

  ok(['id'=>$id,'scheduled_at'=>$scheduled_at,'mode'=>$mode]);
}

/* ---- Notify applicant (email templates) ---- */
if ($action === 'applicant.notify') {
  $id        = (int)($in['applicant_id'] ?? 0);
  $msgIn     = trim((string)($in['message'] ?? ''));     // optional manual override
  $tpl       = strtolower(trim((string)($in['template'] ?? ''))); // 'schedule','hired','offer','rejected','update'
  $statusIn  = trim((string)($in['status'] ?? 'update'));
  $viaE      = (int)($in['via_email'] ?? 1);
  $viaS      = (int)($in['via_sms']   ?? 0);
  $toEmailIn = trim((string)($in['to_email'] ?? ''));

  if ($id <= 0) fail('Invalid applicant_id');

  // lookup applicant (for name/role/email)
  $st = $pdo->prepare("SELECT name, full_name, email, role, status FROM applicants WHERE id=?");
  $st->execute([$id]);
  $app = $st->fetch(PDO::FETCH_ASSOC);
  if (!$app) fail('Applicant not found', 404);

  $name  = $app['name'] ?: ($app['full_name'] ?: 'Applicant');
  $role  = (string)($app['role'] ?? '');
  $dbEmail = trim((string)($app['email'] ?? ''));
  $toEmail = $toEmailIn !== '' ? $toEmailIn : $dbEmail;

  // helper: format datetime nicely
  $fmtDT = function(?string $d, ?string $t): string {
    $dt = trim($d.' '.$t);
    $ts = strtotime($dt ?: '');
    return $ts ? date('M d, Y, g:i A', $ts) : '';
  };

  // if template=schedule but walang date/time sa params, subukan kunin sa pinaka-recent na interview record
  $date = trim((string)($in['date'] ?? ''));
  $time = trim((string)($in['time'] ?? ''));
  $mode = trim((string)($in['mode'] ?? 'On-site'));
  $note = trim((string)($in['notes'] ?? ''));
  if ($tpl === 'schedule' && ($date === '' || $time === '')) {
    try {
      if (column_exists($pdo,'interviews','scheduled_at')) {
        $q = $pdo->prepare("SELECT scheduled_at, mode, notes FROM interviews WHERE applicant_id=? ORDER BY id DESC LIMIT 1");
        $q->execute([$id]);
        if ($r = $q->fetch(PDO::FETCH_ASSOC)) {
          $date = date('Y-m-d', strtotime($r['scheduled_at']));
          $time = date('H:i:s', strtotime($r['scheduled_at']));
          $mode = $r['mode']  ?? $mode;
          $note = $r['notes'] ?? $note;
        }
      }
    } catch(Throwable $__) {}
  }

  // subject/body builder
  $build = function(string $tpl, string $name, string $role) use($fmtDT,$date,$time,$mode,$note): array {
    switch ($tpl) {
      case 'schedule':
      case 'interview':
        $when = $fmtDT($date,$time);
        $sub  = "Interview Schedule – " . ($role ?: 'Application');
        $body = "
          Hi {$name},<br><br>
          We'd like to invite you for an interview for the <b>{$role}</b> role.<br><br>
          <b>When:</b> {$when}<br>
          <b>Mode:</b> {$mode}<br>" .
          ($note!=='' ? "<b>Notes:</b> ".nl2br(htmlspecialchars($note))."<br>" : "") . "
          <br>Please reply to confirm your availability. Thank you!";
        return [$sub, $body];

      case 'offer':
        $sub  = "Job Offer – " . ($role ?: 'Your Application');
        $body = "Hi {$name},<br><br>
                 We are pleased to offer you the position of <b>{$role}</b>. Kindly reply so we can discuss next steps.";
        return [$sub,$body];

      case 'hired':
        $sub  = "Welcome aboard – " . ($role ?: 'New Hire');
        $body = "Hi {$name},<br><br>
                 Congratulations! You are <b>HIRED</b> for the position of <b>{$role}</b>.<br>
                 Our team will reach out with onboarding details shortly.";
        return [$sub,$body];

      case 'rejected':
        $sub  = "Application Update";
        $body = "Hi {$name},<br><br>
                 Thank you for your time. After careful review, we won't be moving forward at this time.
                 We appreciate your interest and wish you the best.";
        return [$sub,$body];

      default:
        $sub  = "Application Update";
        $body = "Hi {$name},<br><br>This is an update regarding your application for <b>{$role}</b>.";
        return [$sub,$body];

        case 'applicant.archive': {
          $id = (int)($input['applicant_id'] ?? 0);
          if (!$id) return jerr('Missing applicant_id');
          $stmt = $pdo->prepare("UPDATE applicants SET archived = 1 WHERE id = ?");
          $stmt->execute([$id]);
          return jok(['id'=>$id]);
        }

    }
  };

  // pick template: explicit > by-status fallback
  if ($tpl === '') {
    $s = strtolower($statusIn);
    $tpl = in_array($s, ['schedule','interview','offer','hired','rejected'], true) ? $s : 'update';
  }
  [$subject, $autoHtml] = $build($tpl, $name, $role);

  // if may custom message from UI, gamitin; else use autoHtml
  $html = ($msgIn !== '') ? $msgIn : $autoHtml;

  $results = [];

  if ($viaE) {
    if ($toEmail === '') {
      $results[] = ['channel'=>'email','ok'=>false,'error'=>'No email on file'];
      $sentOk=false; $err='No email on file';
    } else {
      [$sentOk,$err] = sendHRMail($toEmail, $subject, $html);
      $results[] = ['channel'=>'email','ok'=>$sentOk,'error'=>$err];
    }
    // optional log to notifications table (same as before, kept flexible)
    if (column_exists($pdo,'notifications','applicant_id')) {
      if (column_exists($pdo,'notifications','channel')) {
        $pdo->prepare("INSERT INTO notifications
          (applicant_id, channel, subject, message, status_from, status_to, sent_ok, error_text, created_at)
          VALUES (?, 'email', ?, ?, ?, ?, ?, ?, NOW())")
          ->execute([$id,$subject,$html,(string)($app['status'] ?? ''),$statusIn,$sentOk?1:0,$err]);
      } else {
        $pdo->prepare("INSERT INTO notifications
          (applicant_id, channel_email, channel_sms, subject, message, status_from, status_to, sent_ok, error_text, created_at)
          VALUES (?, 1, 0, ?, ?, ?, ?, ?, ?, NOW())")
          ->execute([$id,$subject,$html,(string)($app['status'] ?? ''),$statusIn,$sentOk?1:0,$err]);
      }
    }
  }

  if ($viaS) {
    $results[] = ['channel'=>'sms','ok'=>false,'error'=>'SMS not configured'];
  }

  audit_log($pdo,'applicant',$id,'notify',['template'=>$tpl,'status'=>$statusIn,'email'=>$toEmail]);
  ok(['id'=>$id,'results'=>$results,'template_used'=>$tpl]);
}

/* ---- Forward resume (log only) ---- */
if ($action === 'applicant.forward') {
  $id   = (int)($in['applicant_id'] ?? 0);
  $site = trim((string)($in['site'] ?? 'HQ'));
  $note = trim((string)($in['note'] ?? ''));
  if ($id <= 0) fail('Invalid applicant_id');

  audit_log($pdo, 'applicant', $id, 'forward_resume', ['site'=>$site,'note'=>$note]);
  ok(['id'=>$id,'site'=>$site]);
}

/* ================== NEW HIRE (document upload) ================== */
if ($action === 'newhire.upload') {
  $id    = (int)($_POST['applicant_id'] ?? 0);
  $email = trim((string)($_POST['email'] ?? ''));
  if ($id <= 0) fail('Invalid applicant_id');

  // soft verify email if provided
  try {
    $st = $pdo->prepare("SELECT email,name,full_name FROM applicants WHERE id=?");
    $st->execute([$id]);
    $ap = $st->fetch(PDO::FETCH_ASSOC) ?: [];
    if (!$ap) fail('Applicant not found', 404);
    if ($email !== '' && isset($ap['email']) && strcasecmp($email,$ap['email'])!==0) {
      fail('Email does not match our record.');
    }
  } catch (Throwable $e) { /* ignore */ }

  $saveDir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'onboarding';
  if (!is_dir($saveDir)) { @mkdir($saveDir, 0775, true); }

  $DOCS = [
    'gov_id1'   => 'Government ID #1',
    'gov_id2'   => 'Government ID #2',
    'sss'       => 'SSS',
    'philhealth'=> 'PhilHealth',
    'pagibig'   => 'Pag-IBIG',
    'tin'       => 'TIN',
    'clearance' => 'NBI/Police Clearance',
    'civil_doc' => 'Birth/Marriage Certificate',
    'photo'     => '2x2 Photo',
    'bank'      => 'Bank Details',
    'edu'       => 'Education Document',
  ];

  $saved = [];
  foreach ($DOCS as $field => $label) {
    if (!isset($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;

    $f = $_FILES[$field];
    if ($f['size'] > 5*1024*1024) fail("$label too large (max 5MB)");
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['pdf','jpg','jpeg','png'], true)) fail("$label must be PDF/JPG/PNG");

    try { $rand = bin2hex(random_bytes(4)); } catch(Throwable $e){ $rand = uniqid(); }
    $safe = preg_replace('~[^a-z0-9]+~i','-', $label);
    $fname = sprintf('onbd-%d-%s-%s.%s', $id, $safe, $rand, $ext);
    $abs   = $saveDir . DIRECTORY_SEPARATOR . $fname;
    $rel   = 'uploads/onboarding/' . $fname;

    if (!move_uploaded_file($f['tmp_name'], $abs)) fail("Failed to save $label");

    // optional: record in onboarding_tasks (best-effort dynamic cols)
    if (column_exists($pdo,'onboarding_tasks','id')) {
      $colApplicant = pickcol($pdo,'onboarding_tasks',['applicant_id','employee_id','ref_id']);
      $colTask      = pickcol($pdo,'onboarding_tasks',['task_name','name','title']);
      $colPath      = pickcol($pdo,'onboarding_tasks',['doc_path','file_path','attachment','doc_url']);
      $colStatus    = pickcol($pdo,'onboarding_tasks',['status','state']);
      $colWhen      = pickcol($pdo,'onboarding_tasks',['submitted_at','created_at','uploaded_at']);

      $cols = []; $vals = [];
      if ($colApplicant) { $cols[]=$colApplicant; $vals[]=$id; }
      if ($colTask)      { $cols[]=$colTask;      $vals[]=$label; }
      if ($colPath)      { $cols[]=$colPath;      $vals[]=$rel; }
      if ($colStatus)    { $cols[]=$colStatus;    $vals[]='Submitted'; }
      if ($colWhen)      { $cols[]=$colWhen;      $vals[]=date('Y-m-d H:i:s'); }

      if ($cols) {
        $place = implode(',', array_fill(0,count($cols),'?'));
        $sql = "INSERT INTO onboarding_tasks (".implode(',',$cols).") VALUES ($place)";
        $pdo->prepare($sql)->execute($vals);
      }
    }

    $saved[] = ['field'=>$field,'label'=>$label,'path'=>$rel];
  }

  audit_log($pdo, 'applicant', $id, 'newhire_upload', ['files'=>$saved]);
  ok(['uploaded'=>$saved]);
}

/* ================== PUBLIC APPLY (from landing page) ================== */
if ($action === 'public.apply') {
  // --- Read fields from multipart form ---
  $full_name = trim((string)($_POST['full_name'] ?? ''));
  $email     = trim((string)($_POST['email'] ?? ''));
  $mobile    = trim((string)($_POST['mobile'] ?? ''));
  $address   = trim((string)($_POST['address'] ?? ''));
  $education = trim((string)($_POST['education'] ?? ''));
  $yoe       = (int)($_POST['yoe'] ?? 0);
  $role_in   = trim((string)($_POST['role'] ?? ''));
  $site      = trim((string)($_POST['site'] ?? ''));
  $start_dt  = trim((string)($_POST['start_date'] ?? ''));

  if ($full_name === '' || $email === '' || $mobile === '') {
    fail('Full name, email, and mobile are required.');
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('Invalid email address.');
  }

  // --- Handle file upload (resume) ---
  if (!isset($_FILES['resume']) || ($_FILES['resume']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    fail('Resume is required.');
  }
  $file = $_FILES['resume'];
  if ($file['size'] > 5 * 1024 * 1024) fail('File too large (max 5MB).');

  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  $allowed = ['pdf','doc','docx'];
  if (!in_array($ext, $allowed, true)) fail('Invalid file type. Allowed: PDF/DOC/DOCX.');

  $upDir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'resumes';
  if (!is_dir($upDir)) { @mkdir($upDir, 0775, true); }
  $safeBase = preg_replace('~[^a-z0-9]+~i','-', $full_name);
  try { $rand = bin2hex(random_bytes(4)); } catch(Throwable $e){ $rand = uniqid(); }
  $fileName = 'cv-' . date('Ymd-His') . '-' . $rand . '-' . $safeBase . '.' . $ext;
  $absPath  = $upDir . DIRECTORY_SEPARATOR . $fileName;
  $relPath  = 'uploads/resumes/' . $fileName;

  if (!move_uploaded_file($file['tmp_name'], $absPath)) {
    fail('Failed to save uploaded file.');
  }

  $cols = [];
  $vals = [];

  $nameCol = pickcol($pdo, 'applicants', ['full_name','name','applicant_name']);
  if (!$nameCol) fail('Applicants table is missing a name column (full_name/name).');
  $cols[] = $nameCol;  $vals[] = $full_name;

  if ($c = pickcol($pdo,'applicants',['email'])) { $cols[]=$c; $vals[]=$email; }
  if ($c = pickcol($pdo,'applicants',['mobile','phone','contact_no','contact'])) { $cols[]=$c; $vals[]=$mobile; }

  if ($c = pickcol($pdo,'applicants',['address'])) { $cols[]=$c; $vals[]=$address; }
  if ($c = pickcol($pdo,'applicants',['education','highest_education'])) { $cols[]=$c; $vals[]=$education; }
  if ($c = pickcol($pdo,'applicants',['yoe','years_of_experience','experience_years'])) { $cols[]=$c; $vals[]=$yoe; }

  if ($c = pickcol($pdo,'applicants',['role','position','position_applied','apply_for'])) { $cols[]=$c; $vals[]=$role_in; }
  if ($c = pickcol($pdo,'applicants',['site','location'])) { $cols[]=$c; $vals[]=$site; }

  if ($start_dt !== '' && $c = pickcol($pdo,'applicants',['start_date','preferred_start_date','availability_date'])) {
    $cols[]=$c; $vals[]=$start_dt;
  }
  if ($c = pickcol($pdo,'applicants',['resume_path','resume','cv_path','cv_file'])) { $cols[]=$c; $vals[]=$relPath; }
  if ($c = pickcol($pdo,'applicants',['status'])) { $cols[]=$c; $vals[]='new'; }
  if ($c = pickcol($pdo,'applicants',['created_at'])) { $cols[]=$c; $vals[] = date('Y-m-d H:i:s'); }

  $placeholders = implode(',', array_fill(0, count($cols), '?'));
  $sql = "INSERT INTO applicants (".implode(',', $cols).") VALUES ($placeholders)";
  $st = $pdo->prepare($sql);
  $st->execute($vals);
  $newId = (int)$pdo->lastInsertId();

  audit_log($pdo, 'applicant', $newId, 'public_apply', ['site'=>$site,'role'=>$role_in,'resume'=>$relPath]);

  ok(['saved'=>true,'applicant_id'=>$newId,'resume'=>$relPath]);
}

fail('Unknown action: '.$action, 404);

} catch (Throwable $e) {
  fail($e->getMessage(), 500);
}
