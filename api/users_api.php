<?php
declare(strict_types=1);
ob_start();
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../mail_config.php'; // <-- dito nanggagaling ang sendHRMail()

// --- Auth guard (admins lang) ---
if (empty($_SESSION['user'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Not authenticated']); exit; }
$meRole = strtolower($_SESSION['user']['role'] ?? '');
if (!in_array($meRole, ['admin','superadmin'])) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
date_default_timezone_set('Asia/Manila');

function read_json() {
  $raw = file_get_contents('php://input');
  $j = json_decode($raw, true);
  return is_array($j) ? $j : [];
}
function out($data) {
  echo json_encode(['ok'=>true,'data'=>$data], JSON_UNESCAPED_UNICODE); exit;
}
function fail($msg, $code=400) {
  http_response_code($code); echo json_encode(['ok'=>false,'error'=>$msg]); exit;
}
function user_row(PDO $pdo, int $id) {
  $st = $pdo->prepare("SELECT id, name, email, role, is_active, created_at FROM users WHERE id=?");
  $st->execute([$id]);
  return $st->fetch(PDO::FETCH_ASSOC);
}

$in = read_json();
$action = $in['action'] ?? '';

try {

  /* ---------- LIST ---------- */
  if ($action === 'list') {
    $q = trim((string)($in['q'] ?? ''));
    if ($q !== '') {
      $st = $pdo->prepare("SELECT id,name,email,role,is_active,created_at
                           FROM users
                           WHERE name LIKE ? OR email LIKE ?
                           ORDER BY id DESC");
      $like = "%$q%";
      $st->execute([$like,$like]);
    } else {
      $st = $pdo->query("SELECT id,name,email,role,is_active,created_at
                         FROM users
                         ORDER BY id DESC");
    }
    out(['rows'=>$st->fetchAll(PDO::FETCH_ASSOC)]);
  }

  /* ---------- ADD (auto-send welcome email) ---------- */
  if ($action === 'add') {
    $name  = trim((string)($in['name'] ?? ''));
    $email = trim((string)($in['email'] ?? ''));
    $role  = strtolower(trim((string)($in['role'] ?? 'employee')));
    $pwd   = (string)($in['password'] ?? '');

    if ($name==='' || $email==='' || $pwd==='') fail('Name, email, password required');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail('Invalid email');

    $roles = ['admin','hr manager','employee'];
    if (!in_array($role, $roles, true)) $role = 'employee';

    // unique email
    $chk = $pdo->prepare("SELECT 1 FROM users WHERE email=?");
    $chk->execute([$email]);
    if ($chk->fetch()) fail('Email already exists');

    // insert
    $hash = password_hash($pwd, PASSWORD_DEFAULT);
    $ins = $pdo->prepare("INSERT INTO users (name,email,role,password_hash,is_active,created_at)
                          VALUES (?,?,?,?,1,NOW())");
    $ins->execute([$name,$email,$role,$hash]);
    $newId = (int)$pdo->lastInsertId();

    // Absolute login URL (works kahit nasa /api/)
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base   = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/\\');
    $loginUrl = "{$scheme}://{$host}{$base}/login.php";

    $roleNice = ucwords($role);
    $subject = 'Welcome to HR1 Nextgenmms';
    $html = <<<HTML
    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:14px;color:#0f172a;line-height:1.5">
      <h2 style="margin:0 0 12px">Welcome to HR1 Nextgenmms</h2>
      <p>Hi {$name},</p>
      <p>Your account has been created.</p>
      <ul style="padding-left:16px;margin:8px 0 16px">
        <li><b>Email:</b> {$email}</li>
        <li><b>Password:</b> {$pwd}</li>
        <li><b>Role:</b> {$roleNice}</li>
      </ul>
      <p>For security, please log in and change your password immediately.</p>
      <div style="margin:18px 0">
        <a href="{$loginUrl}"
           style="display:inline-block;background:#e11d48;border-radius:10px;color:#ffffff;
                  padding:10px 18px;text-decoration:none;font-weight:600">Login Here</a>
      </div>
      <p style="color:#64748b;margin-top:22px">— HR1 Nextgenmms</p>
    </div>
    HTML;
    $alt = "Welcome to HR1 Nextgenmms\n\nEmail: {$email}\nPassword: {$pwd}\nRole: {$roleNice}\n\nLogin: {$loginUrl}";

    // send (hindi pipigil sa create kung mag-fail ang email)
    [$okMail, $errMail] = sendHRMail($email, $subject, $html, $alt);
    if (!$okMail) error_log('[users_api add] mail error: ' . ($errMail ?? 'unknown'));

    out([
      'user'       => user_row($pdo, $newId),
      'mail_sent'  => $okMail ? 1 : 0,
      'mail_error' => $okMail ? null : $errMail
    ]);
  }

  /* ---------- UPDATE ---------- */
  if ($action === 'update') {
    $id    = (int)($in['id'] ?? 0);
    $name  = trim((string)($in['name'] ?? ''));
    $email = trim((string)($in['email'] ?? ''));
    $role  = strtolower(trim((string)($in['role'] ?? 'employee')));
    $active = (int)($in['is_active'] ?? 1);

    if ($id <= 0) fail('Invalid id');
    if ($name==='' || $email==='') fail('Name and email required');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail('Invalid email');

    $roles = ['admin','hr manager','employee'];
    if (!in_array($role, $roles, true)) $role = 'employee';
    $active = $active ? 1 : 0;

    // unique email (exclude self)
    $chk = $pdo->prepare("SELECT 1 FROM users WHERE email=? AND id<>?");
    $chk->execute([$email,$id]);
    if ($chk->fetch()) fail('Email already exists');

    $up = $pdo->prepare("UPDATE users SET name=?, email=?, role=?, is_active=? WHERE id=?");
    $up->execute([$name,$email,$role,$active,$id]);
    out(['user'=>user_row($pdo,$id)]);
  }

  /* ---------- DELETE ---------- */
  if ($action === 'delete') {
    $id = (int)($in['id'] ?? 0);
    if ($id <= 0) fail('Invalid id');
    $meId = (int)($_SESSION['user']['id'] ?? 0);
    if ($id === $meId) fail('You cannot delete your own user');
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
    out(['ok'=>true]);
  }

  /* ---------- TOGGLE ACTIVE ---------- */
  if ($action === 'toggle_active') {
    $id = (int)($in['id'] ?? 0);
    if ($id <= 0) fail('Invalid id');
    $pdo->prepare("UPDATE users SET is_active = 1 - is_active WHERE id=?")->execute([$id]);
    out(['user'=>user_row($pdo,$id)]);
  }

  /* ---------- RESET PASSWORD ---------- */
  if ($action === 'reset_pw') {
    $id  = (int)($in['id'] ?? 0);
    $pwd = (string)($in['password'] ?? '');
    if ($id <= 0 || $pwd === '') fail('Invalid request');
    $hash = password_hash($pwd, PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hash,$id]);
    out(['ok'=>true]);
  }

  fail('Unknown action', 404);

} catch (Throwable $e) {
  fail($e->getMessage(), 500);
}
