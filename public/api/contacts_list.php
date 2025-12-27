<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/helpers/auth.php';

header('Content-Type: application/json');
require_login();

$userId = (int)($_SESSION['user_id'] ?? 0);
$filter = $_GET['filter'] ?? 'all';

$sql = "
  SELECT id, title, firstname, lastname, email, company, type
  FROM contacts
";
$params = [];

if ($filter === 'sales') {
  $sql .= " WHERE type = 'Sales Lead'";
} elseif ($filter === 'support') {
  $sql .= " WHERE type = 'Support'";
} elseif ($filter === 'assigned') {
  $sql .= " WHERE assigned_to = :uid";
  $params['uid'] = $userId;
}

$sql .= " ORDER BY created_at DESC";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$contacts = array_map(function($c) {
  return [
    'id' => (int)$c['id'],
    'name' => $c['title'].' '.$c['firstname'].' '.$c['lastname'],
    'email' => $c['email'],
    'company' => $c['company'],
    'type' => $c['type'],
  ];
}, $rows);

echo json_encode(['ok' => true, 'data' => ['contacts' => $contacts]]);
