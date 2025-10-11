<?php
require_once __DIR__.'/../includes/auth.php';
require_once __DIR__.'/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? '';

if ($action === 'create') {
  // RBAC (admin / hr lang)
  require_role(['admin','hr manager','superadmin','recruiter']); // adjust if needed

  $empId  = (int)($_POST['employee_id'] ?? 0);
  $period = trim((string)($_POST['period'] ?? ''));
  $due    = !empty($_POST['due_date']) ? $_POST['due_date'] : null;

  if ($empId <= 0 || $period === '') {
    http_response_code(422);
    echo json_encode(['ok'=>false,'error'=>'Missing employee or period']);
    exit;
  }

  // Insert — siguraduhing may columns na 'employee_id','period','status','due_date'
  $st = $pdo->prepare("
    INSERT INTO evaluations (employee_id, period, status, due_date, created_at)
    VALUES (:emp, :period, 'pending', :due, NOW())
  ");
  $st->execute([
    ':emp'    => $empId,
    ':period' => $period,
    ':due'    => $due,
  ]);

  echo json_encode(['ok'=>true, 'id'=>$pdo->lastInsertId()]);
  exit;
}

/* ... other actions (update, complete, delete) ... */
echo json_encode(['ok'=>false,'error'=>'Unknown action']);
