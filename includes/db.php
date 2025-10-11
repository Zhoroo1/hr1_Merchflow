<?php
// includes/db.php  (no output, no closing tag)
$DB_HOST = '127.0.0.1';
$DB_PORT = 3307;            // bagayan sa MySQL port mo (3306 kung default)
$DB_NAME = 'hr1_merch';
$DB_USER = 'root';
$DB_PASS = '';

$dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4";

$pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
]);

// Optional: timezone para pareho ang timestamps
$pdo->exec("SET time_zone = '+08:00'");
