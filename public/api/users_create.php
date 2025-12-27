<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/helpers/auth.php';

header('Content-Type: application/json');
require_admin(); // admin only

$firstname = trim($_POST['firstname'] ?? '');
$lastname  = trim($_POST['lastname'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';
$role      = trim($_POST['role'] ?? 'Member');

$allowedRoles = ['Admin','Member'];
if ($firstname === '' || $lastname === '') exit(json_encode(['ok'=>false,'message'=>'First and last name required.']));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) exit(json_encode(['ok'=>false,'message'=>'Valid email required.']));
if (!in_array($role, $allowedRoles, true)) exit(json_encode(['ok'=>false,'message'=>'Invalid role.']));

// Password: >=8, 1 uppercase, 1 number, 1 letter
if (!preg_match('/^(?=.*[A-Z])(?=.*[a-zA-Z])(?=.*\d).{8,}$/', $password)) {
  exit(json_encode(['ok'=>false,'message'=>'Password must be 8+ chars with 1 uppercase and 1 number.']));
}

try {
  $exists = db()->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
  $exists->execute(['email'=>$email]);
  if ($exists->fetch()) exit(json_encode(['ok'=>false,'message'=>'Email already exists.']));

  $hash = password_hash($password, PASSWORD_DEFAULT);

  $stmt = db()->prepare("
    INSERT INTO users (firstname, lastname, email, password, role, created_at)
    VALUES (:fn,:ln,:em,:pw,:ro,NOW())
  ");
  $stmt->execute(['fn'=>$firstname,'ln'=>$lastname,'em'=>$email,'pw'=>$hash,'ro'=>$role]);

  echo json_encode(['ok'=>true,'message'=>'User added successfully.']);
} catch (PDOException $e) {
  echo json_encode(['ok'=>false,'message'=>'Database error.']);
}
