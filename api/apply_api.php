<?php
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['fullname'] ?? '';
  $email = $_POST['email'] ?? '';
  $phone = $_POST['phone'] ?? '';
  $address = $_POST['address'] ?? '';
  $position = $_POST['position'] ?? '';

  if (!$name || !$email || !$position) {
    die("Missing required fields.");
  }

  $resumePath = null;
  if (!empty($_FILES['resume']['name'])) {
    $targetDir = __DIR__ . '/../uploads/resumes/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    $fname = time() . '_' . basename($_FILES['resume']['name']);
    $targetFile = $targetDir . $fname;
    move_uploaded_file($_FILES['resume']['tmp_name'], $targetFile);
    $resumePath = 'uploads/resumes/' . $fname;
  }

  $stmt = $pdo->prepare("INSERT INTO applicants (name, email, phone, address, role, resume, status, created_at)
                         VALUES (?,?,?,?,?,?, 'Applied', NOW())");
  $stmt->execute([$name,$email,$phone,$address,$position,$resumePath]);

  echo "Application submitted successfully!";
}
