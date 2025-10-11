<?php
// Start session once only
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

/* ---------- Require login (guarded) ---------- */
if (!function_exists('require_login')) {
  function require_login(): void {
    if (!empty($_SESSION['user'])) return;
    header('Location: login.php'); exit;
  }
}
require_login();

/* ---------- Current user ---------- */
$u = $_SESSION['user'] ?? [];
if (!isset($GLOBALS['ROLE'])) {
  $GLOBALS['ROLE'] = strtolower(trim((string)($u['role'] ?? '')));
}

/* ---------- Role helpers (guarded) ---------- */
if (!function_exists('role_is')) {
  function role_is($roles): bool {
    $ROLE = $GLOBALS['ROLE'] ?? '';
    $roles = is_array($roles) ? $roles : [$roles];
    foreach ($roles as $r) {
      if ($ROLE === strtolower((string)$r)) return true;
    }
    return false;
  }
}

if (!function_exists('can')) {
  function can(array $roles): bool {
    return role_is($roles);
  }
}

/* Detect if request is API (guarded) */
if (!function_exists('is_api_request')) {
  function is_api_request(): bool {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, '/api/') !== false) return true;
    $accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
    return strpos($accept, 'application/json') !== false;
  }
}

/* ---------- Gate pages by role (guarded) ---------- */
if (!function_exists('require_role')) {
  function require_role(array $roles): void {
    if (can($roles)) return;

    // Not allowed
    if (is_api_request()) {
      http_response_code(403);
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(['ok' => false, 'error' => 'forbidden']);
      exit;
    }

    // For normal pages, redirect based on current role
    if (role_is('employee')) {
      header('Location: employee_home.php'); // self-service portal
    } else {
      header('Location: index.php');         // admin/dashboard fallback
    }
    exit;
  }
}
