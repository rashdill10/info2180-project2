<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: login.php?error=missing');
    exit;
}

$stmt = db()->prepare('SELECT id, email, password, firstname, lastname, role FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: login.php?error=invalid');
    exit;
}

/*
  IMPORTANT:
  Store hashed passwords in DB using password_hash().
  Then verify using password_verify().
*/
if (!password_verify($password, $user['password'])) {
    header('Location: login.php?error=invalid');
    exit;
}

// success: create session
$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_name'] = $user['firstname'] . ' ' . $user['lastname'];
$_SESSION['user_role'] = $user['role'];

header('Location: dashboard.php');
exit;
