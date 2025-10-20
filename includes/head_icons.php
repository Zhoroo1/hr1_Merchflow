<?php
  $ver = @filemtime(__DIR__ . '/../assets/logo3.png') ?: time();
  $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
  $png  = $base . '/assets/logo3.png?v=' . $ver;
?>
<link rel="icon" type="image/png" href="<?= $png ?>">
<link rel="shortcut icon" type="image/png" href="<?= $png ?>">
<link rel="apple-touch-icon" href="<?= $png ?>">
