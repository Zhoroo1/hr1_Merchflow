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

/* ------------ helpers ------------ */
function ok($data = []) { echo json_encode(['ok'=>true, 'data'=>$data], JSON_UNESCAPED_UNICODE); exit; }
function no($msg  = 'API error', $http = 200) { http_response_code($http); echo json_encode(['ok'=>false, 'error'=>$msg], JSON_UNESCAPED_UNICODE); exit; }

function pick(array $r, array $keys, $def='') {
  foreach ($keys as $k) if (array_key_exists($k, $r) && $r[$k] !== null) return trim((string)$r[$k]);
  return $def;
}

/* ------------ email senders ------------ */
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
  if ($HAS_HR_MAIL) {
    // Use your PHPMailer config if available
    return sendHRMail($to, $subject, $body);
  }
  // Fallback to PHP mail()
  return send_email_simple($to, $subject, $body);
}

/* ------------ onboarding helpers ------------ */
function gen_onboarding_token(): string {
  return bin2hex(random_bytes(24)); // 48-char token
}

function base_url_guess(): string {
  $scheme = $_SERVER['REQUEST_SCHEME'] ?? 'http';
  $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
  $dir    = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
  return rtrim("$scheme://$host$dir/..", '/');
}

/* ------------ actions ------------ */
$raw = file_get_contents('php://input') ?: '';
$body = [];
if ($raw !== '') { $tmp = json_decode($raw, true); if (is_array($tmp)) $body = $tmp; }

$action = pick($body + $_REQUEST, ['action']);
if (!$action) no('Missing action');

try {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  /* ---- Change applicant status ----
     Accept both: change_status  OR  applicant.update_status
     Normalizes to code (lowercase) + label (Title-case)
     Updates whichever columns exist: status (label), stage (code), status_code (code)
  ------------------------------------------------------------ */
  if (in_array($action, ['change_status','applicant.update_status'], true)) {

    // Accept both: id OR applicant_id
    $id = (int)pick($body + $_POST, ['id','applicant_id']);
    // Accept from any of these fields (status may be code or label)
    $in  = pick($body + $_POST, ['status','status_code','status_label'], '');
    if ($id <= 0 || $in === '') no('Missing id/status');

    // Normalize
    $labels = [
        'new'       => 'New',
        'pending'   => 'Pending',   // hidden in UI but still valid
        'screening' => 'Screening',
        'interview' => 'Interview', // hidden in UI
        'offered'   => 'Offered',   // hidden in UI
        'rejected'  => 'Rejected',
        'hired'     => 'Hired',
        'archived'  => 'Archived'
      ];

    $allowed = array_keys($labels);

    $lc = strtolower($in);
    $code = in_array($lc, $allowed, true) ? $lc : 'new';
    $label = $labels[$code];

    // Read current row (for audit + email)
    $stmt = $pdo->prepare("SELECT id, name, email, status FROM applicants WHERE id=?");
    $stmt->execute([$id]);
    $app = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$app) no('Applicant not found', 404);
    $old = (string)($app['status'] ?? '');

    $pdo->beginTransaction();

      // Build dynamic UPDATE based on existing columns
      $sets = []; $args = [':id'=>$id];

      if (column_exists($pdo, 'applicants', 'status')) {
        $sets[] = "status = :status";
        $args[':status'] = $label;      // human label
      }
      if (column_exists($pdo, 'applicants', 'stage')) {
        $sets[] = "stage = :stage";
        $args[':stage']  = $code;       // normalized code
      }
      if (column_exists($pdo, 'applicants', 'status_code')) {
        $sets[] = "status_code = :status_code";
        $args[':status_code'] = $code;  // normalized code
      }
      // Fallback if none of the three columns exist
      if (!$sets) {
        $sets[] = "status = :status";
        $args[':status'] = $label;
      }

      // optional updated_at
      $sql = "UPDATE applicants SET ".implode(', ',$sets);
      if (column_exists($pdo, 'applicants', 'updated_at')) {
        $sql .= ", updated_at = NOW()";
      }
      $sql .= " WHERE id = :id";
      $up = $pdo->prepare($sql);
      $up->execute($args);

      // audit (schema-safe)
      audit_log_applicant($pdo, $id, $old, $label);

      /* --- If status becomes Hired --- */
      if ($code === 'hired') {
        $token   = gen_onboarding_token();
        $expires = (new DateTime('+7 days'))->format('Y-m-d H:i:s');

        // store token + expiry (columns may or may not exist; ignore errors)
        try {
          $pdo->prepare("UPDATE applicants SET onboarding_token=?, onboarding_token_expires=? WHERE id=?")
              ->execute([$token, $expires, $id]);
        } catch (Throwable $e) {}

        // build onboarding link
        $base = rtrim((string)($_ENV['APP_BASE_URL'] ?? base_url_guess()), '/');
        $link = $base . "/newhire.php?t=" . urlencode($token);

        // send email if present
        $to   = trim((string)($app['email'] ?? ''));
        $name = $app['name'] ?? 'New Hire';
        if ($to !== '') {
          $subject = "Welcome to HR1 Nextgenmms – Onboarding";
          $bodyTxt = "Hi {$name},\n\n"
                   . "Congratulations! Your application status is now Hired.\n\n"
                   . "Please complete your new-hire requirements using this secure link:\n{$link}\n\n"
                   . "This link will expire in 7 days.\n\n"
                   . "Thank you,\nHR1 Nextgenmms – HR Department";

          [$sent, $err] = send_email_hr($to, $subject, $bodyTxt);
          notify_log($pdo, $id, 'email', $subject, $bodyTxt, $old, $label, $sent, $err);
        }
      }

    $pdo->commit();

    ok(['id'=>$id, 'status_label'=>$label, 'status_code'=>$code, 'prev'=>$old]);
  }

  /* ---- Manual notify ---- */
  if ($action === 'notify') {
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
      // placeholder SMS log (schema-safe)
      notify_log($pdo, $id, 'sms', $subj, $message, (string)($app['status'] ?? null), $status ?: null, false, 'SMS not configured');
      $results[] = ['channel'=>'sms','ok'=>false,'error'=>'SMS not configured'];
    }

    ok(['id'=>$id,'results'=>$results]);
  }

  no('Unknown action');

} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  no('Server error: '.$e->getMessage(), 500);
}

/* ========= utilities ========= */

function table_exists(PDO $pdo, string $t): bool {
  try {
    $st = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($t));
    return (bool)$st->fetchColumn();
  } catch (Throwable $e) { return false; }
}

function column_exists(PDO $pdo, string $table, string $col): bool {
  try {
    $st = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $st->execute([$col]);
    return (bool)$st->fetch();
  } catch (Throwable $e) { return false; }
}

// ---- SAFE audit insert: works with either schema variant
function audit_log_applicant(PDO $pdo, int $appId, string $from, string $to): void {
  if (!table_exists($pdo, 'audit_logs')) return;

  $hasType  = column_exists($pdo, 'audit_logs', 'type');
  $hasRefId = column_exists($pdo, 'audit_logs', 'ref_id');
  $hasMsg   = column_exists($pdo, 'audit_logs', 'message');

  $hasEntType = column_exists($pdo, 'audit_logs', 'entity_type');
  $hasEntId   = column_exists($pdo, 'audit_logs', 'entity_id');
  $hasAction  = column_exists($pdo, 'audit_logs', 'action');
  $hasDetails = column_exists($pdo, 'audit_logs', 'details');

  $msg = "Status: {$from} -> {$to}";

  // v1 schema: type/ref_id/message/created_at
  if ($hasType && $hasRefId && $hasMsg) {
    $st = $pdo->prepare("INSERT INTO audit_logs (type, ref_id, message, created_at) VALUES ('applicant_status', ?, ?, NOW())");
    $st->execute([$appId, $msg]);
    return;
  }

  // v2 schema: entity_type/entity_id/action/details/created_at
  if ($hasEntType && $hasEntId && $hasAction) {
    $cols = ['entity_type','entity_id','action','created_at'];
    $vals = ['?','?','?','NOW()'];
    $args = ['applicant',$appId,'status_change'];

    if ($hasDetails) { $cols[]='details'; $vals[]='?'; $args[]=$msg; }

    $sql = "INSERT INTO audit_logs (".implode(',',$cols).") VALUES (".implode(',',$vals).")";
    $pdo->prepare($sql)->execute($args);
  }
}

/**
 * Schema-safe notifications logger.
 * Will adapt to your `notifications` table: supports either `channel` or `type`,
 * `message` or `body`, `status_from/status_to` or `prev_status/new_status`,
 * `sent_ok` or `is_sent` or `success`, `error_text` or `error`.
 * If table/columns don’t exist, it just returns without throwing.
 */
function notify_log(PDO $pdo, int $appId, string $channel, string $subject, string $message,
                    ?string $from, ?string $to, bool $sentOk, ?string $errText): void {
  if (!table_exists($pdo,'notifications')) return;

  // Column detection
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

  $cols = [];
  $vals = [];
  $args = [];

  if ($colApp) { $cols[]=$colApp; $vals[]='?'; $args[]=$appId; }
  if ($colChan){ $cols[]=$colChan; $vals[]='?'; $args[]=$channel; }
  if ($colSubj){ $cols[]=$colSubj; $vals[]='?'; $args[]=$subject; }
  if ($colMsg) { $cols[]=$colMsg;  $vals[]='?'; $args[]=$message; }
  if ($colFrom){ $cols[]=$colFrom; $vals[]='?'; $args[]=$from; }
  if ($colTo)  { $cols[]=$colTo;   $vals[]='?'; $args[]=$to; }
  if ($colSent){ $cols[]=$colSent; $vals[]='?'; $args[]=$sentOk ? 1 : 0; }
  if ($colErr) { $cols[]=$colErr;  $vals[]='?'; $args[]=$errText; }
  if ($hasCreatedAt) { $cols[]='created_at'; $vals[]='NOW()'; }

  if (!$cols) return; // nothing compatible to insert

  $sql = "INSERT INTO $tbl (".implode(',',$cols).") VALUES (".implode(',',$vals).")";
  try {
    $pdo->prepare($sql)->execute($args);
  } catch (Throwable $e) {
    // swallow logging errors
  }
}
