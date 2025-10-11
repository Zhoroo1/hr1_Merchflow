<?php
/* ---------- Session (single start) ---------- */
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

/* ---------- User / role helpers ---------- */
function current_user() { return $_SESSION['user'] ?? null; }

function user_role(): string {
  $r = strtolower(trim((string)($_SESSION['user']['role'] ?? '')));
  return $r !== '' ? $r : 'employee';
}

/* canonicalize simple aliases if ever you use them */
function norm_role(string $r): string {
  $r = strtolower(trim($r));
  // Add any aliases you may use
  if ($r === 'hr' || $r === 'hrmanager') $r = 'hr manager';
  return $r;
}

function has_role(array $roles): bool {
  $cur = norm_role(user_role());
  foreach ($roles as $r) if ($cur === norm_role($r)) return true;
  return false;
}

function is_admin(): bool    { return has_role(['admin']); }
function is_employee(): bool { return has_role(['employee']); }

/* ---------- Require login (keeps your 2FA flow) ---------- */
function require_login(): void {
  if (!empty($_SESSION['user'])) return;

  if (!empty($_SESSION['pending_user'])) {
    if (basename($_SERVER['PHP_SELF']) !== 'verify_2fa.php') {
      header('Location: verify_2fa.php'); exit;
    }
    return;
  }

  header('Location: login.php'); exit;
}

/* ---------- Detect API request (so we can return JSON 403) ---------- */
function is_api_request(): bool {
  $uri = $_SERVER['REQUEST_URI'] ?? '';
  if (strpos($uri, '/api/') !== false) return true;
  $h = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
  return (strpos($h, 'application/json') !== false);
}

/* ---------- Restrict pages by role ---------- */
function require_role(array $allowed): void {
  // always ensure logged in first
  require_login();

  if (has_role($allowed)) return; // allowed

  // Forbidden:
  if (is_api_request()) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok'=>false,'error'=>'forbidden']); // no HTML for APIs
    exit;
  }

  // For normal pages, redirect employees to their portal; others to dashboard/login
  if (is_employee()) {
    header('Location: employee_home.php'); // adjust to your employee page
  } else {
    header('Location: index.php');
  }
  exit;
}
