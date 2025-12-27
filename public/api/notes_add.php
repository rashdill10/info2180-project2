<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/helpers/auth.php';

header('Content-Type: application/json');

require_login();

$userId = (int)($_SESSION['user_id'] ?? 0);
$contactId = (int)($_POST['contact_id'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($contactId <= 0) {
    echo json_encode(['ok' => false, 'message' => 'Invalid contact.']);
    exit;
}
if ($comment === '') {
    echo json_encode(['ok' => false, 'message' => 'Note cannot be empty.']);
    exit;
}
if (mb_strlen($comment) > 2000) {
    echo json_encode(['ok' => false, 'message' => 'Note too long (max 2000).']);
    exit;
}

try {
    $stmt = db()->prepare("
        INSERT INTO notes (contact_id, comment, created_by, created_at)
        VALUES (:cid, :comment, :uid, NOW())
    ");
    $stmt->execute(['cid' => $contactId, 'comment' => $comment, 'uid' => $userId]);

    db()->prepare("UPDATE contacts SET updated_at = NOW() WHERE id = :cid")
        ->execute(['cid' => $contactId]);

    // Get author name for display
    $authorStmt = db()->prepare("SELECT CONCAT(firstname,' ',lastname) FROM users WHERE id = :uid");
    $authorStmt->execute(['uid' => $userId]);
    $author = $authorStmt->fetchColumn() ?: 'You';

    // Send current datetime string from DB
    $dt = db()->query("SELECT NOW()")->fetchColumn();

    echo json_encode([
        'ok' => true,
        'message' => 'Note added.',
        'data' => [
            'author' => $author,
            'comment' => $comment,
            'created_at' => $dt
        ]
    ]);
    exit;

} catch (PDOException $ex) {
    echo json_encode(['ok' => false, 'message' => 'Database error.']);
    exit;
}
