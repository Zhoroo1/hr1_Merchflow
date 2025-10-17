<?php
// api/applicants_api.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

require_once __DIR__ . '/../includes/db.php'; // must set $pdo = new PDO(...)

// Try to load your PHPMailer helpers (optional)
$HAS_HR_MAIL = false;
try {
  require_once __DIR__ . '/../mail_config.php'; // defines sendHRMail()
  if (function_exists('sendHRMail')) $HAS_HR_MAIL = true;
} catch (Throwable $e) {
  // ignore; we'll fall back to mail()
}

/* ------------ helpers (common) ------------ */
function ok($data = []) { echo json_encode(['ok'=>true, 'data'=>$data], JSON_UNESCAPED_UNICODE); exit; }
function no($msg  = 'API error', $http = 200) { http_response_code($http); echo json_encode(['ok'=>false, 'error'=>$msg], JSON_UNESCAPED_UNICODE); exit; }

function pick(array $r, array $keys, $def='') {
  foreach ($keys as $k) if (array_key_exists($k, $r) && $r[$k] !== null) return trim((string)$r[$k]);
  return $def;
}

function table_exists(PDO $pdo, string $t): bool {
  try { $st = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($t)); return (bool)$st->fetchColumn(); }
  catch (Throwable $e) { return false; }
}
function column_exists(PDO $pdo, string $table, string $col): bool {
  try { $st = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?"); $st->execute([$col]); return (bool)$st->fetch(); }
  catch (Throwable $e) { return false; }
}

/* ------------ email helpers ------------ */
function send_email_simple(string $to, string $subject, string $body): array {
  $headers = [];
  $headers[] = 'From: HR1 Nextgenmms <no-reply@example.test>';
  $headers[] = 'MIME-Version: 1.0';
  $headers[] = 'Content-Type: text/plain; charset=UTF-8';
  $ok = @mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $body, implode("\r\n", $headers));
  return [$ok, $ok ? null : 'mail() failed or not configured'];
}
function send_email_hr(string $to, string $subject, string $body): array {
  global $HAS_HR_MAIL;
  if ($HAS_HR_MAIL) return sendHRMail($to, $subject, $body);
  return send_email_simple($to, $subject, $body);
}

/* ------------ onboarding helpers ------------ */
function gen_onboarding_token(): string { return bin2hex(random_bytes(24)); } // 48 chars
function base_url_guess(): string {
  $scheme = $_SERVER['REQUEST_SCHEME'] ?? 'http';
  $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
  $dir    = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
  return rtrim("$scheme://$host$dir/..", '/');
}

/* ------------ audit / notify logs (schema-safe) ------------ */
function audit_log_applicant(PDO $pdo, int $appId, string $from, string $to, string $action='status_change', ?string $details=null): void {
  if (!table_exists($pdo, 'audit_logs')) return;

  $hasType  = column_exists($pdo, 'audit_logs', 'type');
  $hasRefId = column_exists($pdo, 'audit_logs', 'ref_id');
  $hasMsg   = column_exists($pdo, 'audit_logs', 'message');

  $hasEntType = column_exists($pdo, 'audit_logs', 'entity_type');
  $hasEntId   = column_exists($pdo, 'audit_logs', 'entity_id');
  $hasAction  = column_exists($pdo, 'audit_logs', 'action');
  $hasDetails = column_exists($pdo, 'audit_logs', 'details');

  $msg = $details ?? ("Status: {$from} -> {$to}");

  // v1 schema
  if ($hasType && $hasRefId && $hasMsg) {
    $st = $pdo->prepare("INSERT INTO audit_logs (type, ref_id, message, created_at) VALUES (?, ?, ?, NOW())");
    $st->execute([$action, $appId, $msg]);
    return;
  }

  // v2 schema
  if ($hasEntType && $hasEntId && $hasAction) {
    $cols = ['entity_type','entity_id','action','created_at'];
    $vals = ['?','?','?','NOW()'];
    $args = ['applicant',$appId,$action];
    if ($hasDetails) { $cols[]='details'; $vals[]='?'; $args[]=$msg; }
    $sql = "INSERT INTO audit_logs (".implode(',',$cols).") VALUES (".implode(',',$vals).")";
    $pdo->prepare($sql)->execute($args);
  }
}

function notify_log(PDO $pdo, int $appId, string $channel, string $subject, string $message,
                    ?string $from, ?string $to, bool $sentOk, ?string $errText): void {
  if (!table_exists($pdo,'notifications')) return;

  $tbl = 'notifications';
  $colApp   = column_exists($pdo,$tbl,'applicant_id') ? 'applicant_id' : (column_exists($pdo,$tbl,'app_id') ? 'app_id' : null);
  $colChan  = column_exists($pdo,$tbl,'channel') ? 'channel' : (column_exists($pdo,$tbl,'type') ? 'type' : null);
  $colSubj  = column_exists($pdo,$tbl,'subject') ? 'subject' : null;
  $colMsg   = column_exists($pdo,$tbl,'message') ? 'message' : (column_exists($pdo,$tbl,'body') ? 'body' : null);
  $colFrom  = column_exists($pdo,$tbl,'status_from') ? 'status_from' : (column_exists($pdo,$tbl,'prev_status') ? 'prev_status' : null);
  $colTo    = column_exists($pdo,$tbl,'status_to') ? 'status_to' : (column_exists($pdo,$tbl,'new_status') ? 'new_status' : null);
  $colSent  = column_exists($pdo,$tbl,'sent_ok') ? 'sent_ok' : (column_exists($pdo,$tbl,'is_sent') ? 'is_sent' : (column_exists($pdo,$tbl,'success') ? 'success' : null));
  $colErr   = column_exists($pdo,$tbl,'error_text') ? 'error_text' : (column_exists($pdo,$tbl,'error') ? 'error' : null);
  $hasCreatedAt = column_exists($pdo,$tbl,'created_at');

  $cols = []; $vals = []; $args = [];
  if ($colApp) { $cols[]=$colApp; $vals[]='?'; $args[]=$appId; }
  if ($colChan){ $cols[]=$colChan; $vals[]='?'; $args[]=$channel; }
  if ($colSubj){ $cols[]=$colSubj; $vals[]='?'; $args[]=$subject; }
  if ($colMsg) { $cols[]=$colMsg;  $vals[]='?'; $args[]=$message; }
  if ($colFrom){ $cols[]=$colFrom; $vals[]='?'; $args[]=$from; }
  if ($colTo)  { $cols[]=$colTo;   $vals[]='?'; $args[]=$to; }
  if ($colSent){ $cols[]=$colSent; $vals[]='?'; $args[]=$sentOk ? 1 : 0; }
  if ($colErr) { $cols[]=$colErr;  $vals[]='?'; $args[]=$errText; }
  if ($hasCreatedAt) { $cols[]='created_at'; $vals[]='NOW()'; }

  if (!$cols) return;
  $sql = "INSERT INTO $tbl (".implode(',',$cols).") VALUES (".implode(',',$vals).")";
  try { $pdo->prepare($sql)->execute($args); } catch (Throwable $e) { /* ignore logging errors */ }
}

/* ------------ input ------------ */
$raw = file_get_contents('php://input') ?: '';
$body = [];
if ($raw !== '') { $tmp = json_decode($raw, true); if (is_array($tmp)) $body = $tmp; }
$action = pick($body + $_REQUEST, ['action']);
if (!$action) no('Missing action');

try {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  /* ============================================================
   * 1) CHANGE STATUS  (supports 'change_status' and 'applicant.update_status')
   * ============================================================ */
  if (in_array($action, ['change_status','applicant.update_status'], true)) {
    $id = (int)pick($body + $_POST, ['id','applicant_id']);
    $in = pick($body + $_POST, ['status','status_code','status_label'], '');
    if ($id <= 0 || $in === '') no('Missing id/status');

    $labels = [
      'new'=>'New','pending'=>'Pending','screening'=>'Screening','interview'=>'Interview',
      'offered'=>'Offered','rejected'=>'Rejected','hired'=>'Hired','archived'=>'Archived'
    ];
    $allowed = array_keys($labels);
    $lc = strtolower($in);
    $code = in_array($lc,$allowed,true) ? $lc : 'new';
    $label = $labels[$code];

    $stmt = $pdo->prepare("SELECT id, name, email, status FROM applicants WHERE id=?");
    $stmt->execute([$id]);
    $app = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$app) no('Applicant not found', 404);
    $old = (string)($app['status'] ?? '');

    $pdo->beginTransaction();
      $sets=[]; $args=[':id'=>$id];

      if (column_exists($pdo,'applicants','status'))      { $sets[]="status = :status";           $args[':status']=$label; }
      if (column_exists($pdo,'applicants','stage'))       { $sets[]="stage = :stage";             $args[':stage']=$code; }
      if (column_exists($pdo,'applicants','status_code')) { $sets[]="status_code = :status_code"; $args[':status_code']=$code; }

      if (!$sets) { $sets[]="status = :status"; $args[':status']=$label; }

      if (column_exists($pdo,'applicants','updated_at')) { $sets[]="updated_at = NOW()"; }

      $up = $pdo->prepare("UPDATE applicants SET ".implode(', ',$sets)." WHERE id = :id");
      $up->execute($args);

      audit_log_applicant($pdo, $id, $old, $label);

      if ($code === 'hired') {
        $token   = gen_onboarding_token();
        $expires = (new DateTime('+7 days'))->format('Y-m-d H:i:s');
        try {
          $pdo->prepare("UPDATE applicants SET onboarding_token=?, onboarding_token_expires=? WHERE id=?")
              ->execute([$token, $expires, $id]);
        } catch (Throwable $e) {}

        $base = rtrim((string)($_ENV['APP_BASE_URL'] ?? base_url_guess()), '/');
        $link = $base . "/newhire.php?t=" . urlencode($token);

        $to   = trim((string)($app['email'] ?? ''));
        $name = $app['name'] ?? 'New Hire';
        if ($to !== '') {
          $subject = "Welcome to HR1 Nextgenmms – Onboarding";
          $bodyTxt = "Hi {$name},\n\nCongratulations! Your application status is now Hired.\n\n"
                   . "Please complete your new-hire requirements using this secure link:\n{$link}\n\n"
                   . "This link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department";
          [$sent,$err] = send_email_hr($to,$subject,$bodyTxt);
          notify_log($pdo,$id,'email',$subject,$bodyTxt,$old,$label,$sent,$err);
        }
      }
    $pdo->commit();

    /* ---------- AUTO-ADD TO EMPLOYEES WHEN HIRED ---------- */
if ($code === 'hired') {
  try {
    // Fetch applicant data
    $st = $pdo->prepare("SELECT name, role, email, mobile, address FROM applicants WHERE id=?");
    $st->execute([$id]);
    $ap = $st->fetch(PDO::FETCH_ASSOC);

    if ($ap) {
      // Prevent duplicates
      $chk = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE name=? OR email=?");
      $chk->execute([$ap['name'], $ap['email']]);
      $exists = (int)$chk->fetchColumn();

      if ($exists === 0) {
        $cols = ['name','role','department','email','mobile','status','date_hired','created_at'];
        $vals = ['?','?','?','?','?','?','?','NOW()'];
        $args = [
          $ap['name'],
          $ap['role'] ?: 'New Hire',
          'Operations',
          $ap['email'],
          $ap['mobile'],
          'Active',
          date('Y-m-d'),
        ];
        $sql = "INSERT INTO employees (".implode(',',$cols).") VALUES (".implode(',',$vals).")";
        $pdo->prepare($sql)->execute($args);
      }
    }
  } catch (Throwable $e) {
    // Silent fail (no break)
  }
}


    ok(['id'=>$id,'status_label'=>$label,'status_code'=>$code,'prev'=>$old]);
  }

  /* ============================================================
   * 2) NOTIFY (manual)
   * ============================================================ */
  if (in_array($action, ['notify', 'applicant.notify'], true)) {
    $id       = (int)pick($body + $_POST, ['id','applicant_id']);
    $status   = pick($body + $_POST, ['status'], 'update');
    $message  = pick($body + $_POST, ['message']);
    $viaEmail = (int)pick($body + $_POST, ['via_email','email'], '1');
    $viaSMS   = (int)pick($body + $_POST, ['via_sms','sms'], '0');

    if ($id <= 0) no('Missing id');
    if ($message === '') no('Message is empty');

    $stmt = $pdo->prepare("SELECT id, name, email, status FROM applicants WHERE id=?");
    $stmt->execute([$id]);
    $app = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$app) no('Applicant not found', 404);

    $email  = trim((string)($app['email'] ?? ''));
    $subj   = "Application Update - " . ucfirst($status);
    $results = [];

    if ($viaEmail) {
      if ($email === '') {
        $results[] = ['channel'=>'email','ok'=>false,'error'=>'No email on file'];
      } else {
        [$sentOk, $err] = send_email_hr($email, $subj, $message);
        $results[] = ['channel'=>'email','ok'=>$sentOk,'error'=>$err];
        notify_log($pdo, $id, 'email', $subj, $message, (string)($app['status'] ?? null), $status ?: null, $sentOk, $err);
      }
    }

    if ($viaSMS) {
      notify_log($pdo, $id, 'sms', $subj, $message, (string)($app['status'] ?? null), $status ?: null, false, 'SMS not configured');
      $results[] = ['channel'=>'sms','ok'=>false,'error'=>'SMS not configured'];
    }

    ok(['id'=>$id,'results'=>$results]);
  }

  /* ============================================================
   * 3) ARCHIVE / UNARCHIVE / DELETE
   * ============================================================ */
  if ($action === 'applicant.archive' || $action === 'applicant.unarchive' || $action === 'applicant.delete') {
    $id = (int)pick($body + $_POST, ['id','applicant_id']);
    if ($id <= 0) no('Missing id');

    // get current row
    $st = $pdo->prepare("SELECT * FROM applicants WHERE id=?");
    $st->execute([$id]);
    $app = $st->fetch(PDO::FETCH_ASSOC);
    if (!$app) no('Applicant not found', 404);

    if ($action === 'applicant.delete') {
      // allow delete; if archived column exists, optionally enforce archived first
      $pdo->prepare("DELETE FROM applicants WHERE id=?")->execute([$id]);
      audit_log_applicant($pdo, $id, 'exists', 'deleted', 'delete', 'Applicant permanently deleted');
      ok(['id'=>$id,'deleted'=>true]);
    }

    $toArchived = ($action === 'applicant.archive') ? 1 : 0;

    $sets = []; $args = [':id'=>$id];

    // archived flag
    if (column_exists($pdo,'applicants','archived'))      { $sets[]="archived = :arch";      $args[':arch']=$toArchived; }
    if (column_exists($pdo,'applicants','is_archored'))    { $sets[]="is_archored = :arch";  $args[':arch']=$toArchived; } // typo-proofing
    if (column_exists($pdo,'applicants','is_archived'))    { $sets[]="is_archived = :arch";  $args[':arch']=$toArchived; }

    // status label if present
    if (column_exists($pdo,'applicants','status'))      { $sets[]="status = :status";      $args[':status'] = $toArchived ? 'Archived' : 'Pending'; }
    if (column_exists($pdo,'applicants','stage'))       { $sets[]="stage = :stage";        $args[':stage']  = $toArchived ? 'archived' : 'pending'; }
    if (column_exists($pdo,'applicants','status_code')) { $sets[]="status_code = :scode";  $args[':scode']  = $toArchived ? 'archived' : 'pending'; }

    if (!$sets) { // fallback
      $sets[]="status = :status"; $args[':status'] = $toArchived ? 'Archived' : 'Pending';
    }
    if (column_exists($pdo,'applicants','updated_at')) { $sets[]="updated_at = NOW()"; }

    $sql = "UPDATE applicants SET ".implode(', ',$sets)." WHERE id=:id";
    $pdo->prepare($sql)->execute($args);

    audit_log_applicant($pdo, $id,
      (string)($app['status'] ?? 'Unknown'),
      $toArchived ? 'Archived' : 'Pending',
      $toArchived ? 'archive' : 'unarchive',
      $toArchived ? 'Moved to archive' : 'Restored from archive'
    );

    ok(['id'=>$id,'archived'=>$toArchived]);
  }

  /* ============================================================
   * 4) FORWARD (update branch/site + audit)
   * ============================================================ */
  if ($action === 'applicant.forward') {
    $id   = (int)pick($body + $_POST, ['id','applicant_id']);
    $site = pick($body + $_POST, ['site','branch','location','office'], '');
    $note = pick($body + $_POST, ['note','notes'], '');

    if ($id <= 0) no('Missing id');
    if ($site === '') no('Missing site');

    // pick best-fit column name
    $col = null;
    foreach (['site','location','branch','office'] as $c) {
      if (column_exists($pdo,'applicants',$c)) { $col = $c; break; }
    }

    if ($col) {
      $pdo->prepare("UPDATE applicants SET `$col` = ?, updated_at = NOW() WHERE id = ?")->execute([$site, $id]);
    }

    audit_log_applicant($pdo, $id, '—', '—', 'forward', "Forwarded to: {$site}".($note ? " | Note: {$note}" : ''));
    ok(['id'=>$id,'site_column'=>$col,'site'=>$site]);
  }

    /* ============================================================
   * 5) SCHEDULE INTERVIEW (schema-safe insert + auto email)
   * ============================================================ */
  if ($action === 'applicant.schedule') {
    $id    = (int)pick($body + $_POST, ['id','applicant_id','applicant']);
    $date  = pick($body + $_POST, ['date','scheduled_date']);
    $time  = pick($body + $_POST, ['time','scheduled_time']);
    $mode  = pick($body + $_POST, ['mode','interview_mode'], 'On-site');
    $notes = pick($body + $_POST, ['notes','note'],'');

    if ($id <= 0) no('Missing id');
    if ($date === '' || $time === '') no('Missing date/time');

    $saved = false;

    /* ---------- Option A: interviews table (only insert existing cols) ---------- */
    if (table_exists($pdo,'interviews')) {
      $cols = []; $vals = []; $args = [];

      // always try to record applicant_id
      if (column_exists($pdo,'interviews','applicant_id')) { $cols[]='applicant_id'; $vals[]='?'; $args[]=$id; }
      if (column_exists($pdo,'interviews','date'))         { $cols[]='date';         $vals[]='?'; $args[]=$date; }
      if (column_exists($pdo,'interviews','time'))         { $cols[]='time';         $vals[]='?'; $args[]=$time; }
      if (column_exists($pdo,'interviews','scheduled_at')) { $cols[]='scheduled_at'; $vals[]='?'; $args[]="$date $time:00"; }
      if (column_exists($pdo,'interviews','mode'))         { $cols[]='mode';         $vals[]='?'; $args[]=$mode; }
      if (column_exists($pdo,'interviews','notes'))        { $cols[]='notes';        $vals[]='?'; $args[]=$notes; }
      if (column_exists($pdo,'interviews','created_at'))   { $cols[]='created_at';   $vals[]='NOW()'; }

      if ($cols) {
        $sql = "INSERT INTO interviews (".implode(',',$cols).") VALUES (".implode(',',$vals).")";
        $pdo->prepare($sql)->execute($args);
        $saved = true;
      }
    }

    /* ---------- Option B: schedules table (fallback) ---------- */
    if (!$saved && table_exists($pdo,'schedules')) {
      // be tolerant to column names
      $hasRefType   = column_exists($pdo,'schedules','ref_type');
      $hasRefId     = column_exists($pdo,'schedules','ref_id');
      $hasTitle     = column_exists($pdo,'schedules','title');
      $hasDetails   = column_exists($pdo,'schedules','details');
      $hasSchedAt   = column_exists($pdo,'schedules','scheduled_at');
      $hasCreatedAt = column_exists($pdo,'schedules','created_at');

      $cols = []; $vals = []; $args = [];
      if ($hasRefType)   { $cols[]='ref_type';     $vals[]='?';      $args[]='applicant'; }
      if ($hasRefId)     { $cols[]='ref_id';       $vals[]='?';      $args[]=$id; }
      if ($hasTitle)     { $cols[]='title';        $vals[]='?';      $args[]='Interview'; }
      if ($hasDetails)   { $cols[]='details';      $vals[]='?';      $args[]= $notes ?: $mode; }
      if ($hasSchedAt)   { $cols[]='scheduled_at'; $vals[]='?';      $args[]="$date $time:00"; }
      if ($hasCreatedAt) { $cols[]='created_at';   $vals[]='NOW()'; }

      if ($cols) {
        $sql = "INSERT INTO schedules (".implode(',',$cols).") VALUES (".implode(',',$vals).")";
        $pdo->prepare($sql)->execute($args);
        $saved = true;
      }
    }

    /* ---------- Soft status nudge -> Screening (schema-safe) ---------- */
    $sets=[]; $args=[':id'=>$id];
    if (column_exists($pdo,'applicants','status'))      { $sets[]="status = 'Screening'"; }
    if (column_exists($pdo,'applicants','stage'))       { $sets[]="stage = 'screening'"; }
    if (column_exists($pdo,'applicants','status_code')) { $sets[]="status_code = 'screening'"; }
    if (column_exists($pdo,'applicants','updated_at'))  { $sets[]="updated_at = NOW()"; }
    if ($sets) $pdo->prepare("UPDATE applicants SET ".implode(', ',$sets)." WHERE id=:id")->execute($args);

    /* ---------- Fetch applicant (for email) ---------- */
    $st = $pdo->prepare("SELECT name, email, status FROM applicants WHERE id=?");
    $st->execute([$id]);
    $app = $st->fetch(PDO::FETCH_ASSOC) ?: [];

    $emailTo = trim((string)($app['email'] ?? ''));
    $name    = $app['name'] ?: 'Applicant';
    $whenTxt = "{$date} {$time}";
    $subject = "Interview Schedule Confirmation";
    $bodyTxt = "Hi {$name},\n\nThis is to confirm your interview schedule:\n\n"
             . "Date/Time: {$whenTxt}\nMode: {$mode}\n"
             . ($notes !== '' ? "Notes: {$notes}\n" : '')
             . "\nPlease reply to confirm your availability. Thank you.";

    $emailed = false; $mailErr = null;
    if ($emailTo !== '') {
      [$emailed, $mailErr] = send_email_hr($emailTo, $subject, $bodyTxt);
      notify_log($pdo, $id, 'email', $subject, $bodyTxt, (string)($app['status'] ?? null), 'Screening', $emailed, $mailErr);
    }

    audit_log_applicant($pdo,$id,'(current)','Screening','schedule',
      "Interview on {$date} {$time}".($mode ? " | Mode: {$mode}" : '').($notes ? " | Notes: {$notes}" : '')
    );

    ok(['id'=>$id,'scheduled'=>true,'stored'=>$saved,'emailed'=>$emailed,'email_error'=>$mailErr]);
  }


  /* ============================================================
   * Default: unknown action
   * ============================================================ */
  no('Unknown action');

} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  no('Server error: '.$e->getMessage(), 500);
}
