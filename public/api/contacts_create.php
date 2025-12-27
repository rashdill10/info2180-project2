<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/helpers/auth.php';

header('Content-Type: application/json');
require_login();

$userId = (int)($_SESSION['user_id'] ?? 0);

$title = trim($_POST['title'] ?? '');
$firstname = trim($_POST['firstname'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');
$email = trim($_POST['email'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$company = trim($_POST['company'] ?? '');
$type = trim($_POST['type'] ?? '');
$assignedTo = (int)($_POST['assigned_to'] ?? 0);

$allowedTitles = ['Mr','Mrs','Ms','Dr','Prof'];
$allowedTypes = ['Sales Lead','Support'];

if (!in_array($title, $allowedTitles, true))  exit(json_encode(['ok'=>false,'message'=>'Invalid title.']));
if ($firstname === '' || $lastname === '')   exit(json_encode(['ok'=>false,'message'=>'First name and last name required.']));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) exit(json_encode(['ok'=>false,'message'=>'Valid email required.']));
if ($telephone === '' || $company === '')     exit(json_encode(['ok'=>false,'message'=>'Telephone and company required.']));
if (!in_array($type, $allowedTypes, true))    exit(json_encode(['ok'=>false,'message'=>'Invalid type.']));
if ($assignedTo <= 0)                         exit(json_encode(['ok'=>false,'message'=>'Assigned To is required.']));

try {
  // Ensure assigned user exists
  $chk = db()->prepare("SELECT id FROM users WHERE id = :id");
  $chk->execute(['id'=>$assignedTo]);
  if (!$chk->fetch()) exit(json_encode(['ok'=>false,'message'=>'Assigned user not found.']));

  $stmt = db()->prepare("
    INSERT INTO contacts (title, firstname, lastname, email, telephone, company, type, assigned_to, created_by, created_at, updated_at)
    VALUES (:t,:fn,:ln,:em,:tel,:co,:ty,:as,:cb,NOW(),NOW())
  ");
  $stmt->execute([
    't'=>$title,'fn'=>$firstname,'ln'=>$lastname,'em'=>$email,'tel'=>$telephone,
    'co'=>$company,'ty'=>$type,'as'=>$assignedTo,'cb'=>$userId
  ]);

  echo json_encode(['ok'=>true,'message'=>'Contact added successfully.']);
} catch (PDOException $e) {
  echo json_encode(['ok'=>false,'message'=>'Database error.']);
}
