<?php
declare(strict_types=1);

// Start session once, and as early as possible (no output above this line!)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Require login
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Convenience vars
$CURRENT_USER = $_SESSION['user'];
$CURRENT_ROLE = strtolower(trim($CURRENT_USER['role'] ?? ''));
