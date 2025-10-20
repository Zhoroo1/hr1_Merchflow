<?php
// absolute path kahit nasa subfolder (e.g. /hr1_Merchflow)
$__base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
$__ver  = 'v=7';                               // palitan number to bust cache
$__ico  = $__base . '/favicon.ico?' . $__ver;  // root ico (meron ka na)
$__png  = $__base . '/assets/logo3.png?' . $__ver;
?>
<link rel="icon" href="<?= htmlspecialchars($__ico) ?>" type="image/x-icon" sizes="any">
<link rel="icon" href="<?= htmlspecialchars($__png) ?>" type="image/png" sizes="32x32">
<link rel="apple-touch-icon" href="<?= htmlspecialchars($__png) ?>">
<link rel="shortcut icon" href="<?= htmlspecialchars($__ico) ?>">
