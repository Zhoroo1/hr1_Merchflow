<?php
// api/recognition_api.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
session_start();

if (empty($_SESSION['user'])) { echo json_encode(['ok'=>false,'error'=>'unauthorized']); exit; }
$me = $_SESSION['user'];
$meId = (int)($me['id'] ?? 0);
$meRole = strtolower($me['role'] ?? '');
$isAdminHr = in_array($meRole, ['admin','superadmin','hr','hr manager','human resources'], true);

require_once __DIR__ . '/../includes/db.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
date_default_timezone_set('Asia/Manila');
try { $pdo->exec("SET time_zone = '+08:00'"); } catch (Throwable $e) {}

$action = $_POST['action'] ?? ''; // 'delete' or insert

/* ------------------ DELETE ------------------ */
if ($action === 'delete') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id <= 0) { echo json_encode(['ok'=>false,'error'=>'Missing id']); exit; }

  $st = $pdo->prepare("SELECT from_user_id FROM recognitions WHERE id=?");
  $st->execute([$id]);
  $row = $st->fetch(PDO::FETCH_ASSOC);
  if (!$row) { echo json_encode(['ok'=>false,'error'=>'Not found']); exit; }
  if (!$isAdminHr && (int)$row['from_user_id'] !== $meId) {
    echo json_encode(['ok'=>false,'error'=>'forbidden']); exit;
  }

  $del = $pdo->prepare("DELETE FROM recognitions WHERE id=?");
  $del->execute([$id]);
  echo json_encode(['ok'=>true,'data'=>['deleted'=>true,'id'=>$id]]);
  exit;
}

/* ------------------ INSERT (Give Kudos) ------------------ */
/*
 Accepts:
   - from_user_id (required)
   - employee_id OR recipient_name (at least one)
   - badge (required)
   - note (optional)
*/
$from = (int)($_POST['from_user_id'] ?? 0);
$to   = (int)($_POST['employee_id'] ?? 0);
$badge= trim((string)($_POST['badge'] ?? ''));
$note = trim((string)($_POST['note'] ?? ''));
$recName = trim((string)($_POST['recipient_name'] ?? ''));

/* Resolve recipient if employee_id not provided */
if ($to === 0 && $recName !== '') {
  // 1) exact match on users
  $q = $pdo->prepare("SELECT id FROM users WHERE name = ? LIMIT 1");
  $q->execute([$recName]);
  $to = (int)($q->fetchColumn() ?: 0);

  // 2) fallback: LIKE on users
  if ($to === 0) {
    $q = $pdo->prepare("SELECT id FROM users WHERE name LIKE ? ORDER BY CHAR_LENGTH(name) ASC LIMIT 1");
    $q->execute(['%'.$recName.'%']);
    $to = (int)($q->fetchColumn() ?: 0);
  }

  // 3) fallback: employees table (if users not found)
  if ($to === 0) {
    $q = $pdo->prepare("SELECT id FROM employees WHERE name = ? LIMIT 1");
    $q->execute([$recName]);
    $to = (int)($q->fetchColumn() ?: 0);

    if ($to === 0) {
      $q = $pdo->prepare("SELECT id FROM employees WHERE name LIKE ? ORDER BY CHAR_LENGTH(name) ASC LIMIT 1");
      $q->execute(['%'.$recName.'%']);
      $to = (int)($q->fetchColumn() ?: 0);
    }
  }
}

if (!$from || !$badge) { echo json_encode(['ok'=>false,'error'=>'Missing required fields']); exit; }
if ($to === 0) { echo json_encode(['ok'=>false,'error'=>'Recipient not found']); exit; }

try {
  $stmt = $pdo->prepare("INSERT INTO recognitions(from_user_id,employee_id,badge,note) VALUES(?,?,?,?)");
  $stmt->execute([$from,$to,$badge,$note]);
  $id = (int)$pdo->lastInsertId();

  // Return joined row (users or employees)
  $rowSql = "SELECT r.*,
                    uf.name AS from_name,
                    COALESCE(ut.name, e.name) AS to_name,
                    DATE_FORMAT(r.created_at, '%b %d, %Y') AS created_fmt
             FROM recognitions r
             LEFT JOIN users uf ON uf.id = r.from_user_id
             LEFT JOIN users ut ON ut.id = r.employee_id
             LEFT JOIN employees e ON e.id = r.employee_id
             WHERE r.id = ?";
  $row = $pdo->prepare($rowSql);
  $row->execute([$id]);
  $data = $row->fetch(PDO::FETCH_ASSOC);

  echo json_encode(['ok'=>true,'data'=>$data]);
} catch (Throwable $e) {
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
