<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/helpers/auth.php';

header('Content-Type: application/json');

require_login();

$userId = (int)($_SESSION['user_id'] ?? 0);

$contactId = (int)($_POST['contact_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($contactId <= 0 || !in_array($action, ['assign_to_me', 'toggle_type'], true)) {
    echo json_encode(['ok' => false, 'message' => 'Invalid request.']);
    exit;
}

try {
    if ($action === 'assign_to_me') {
        $stmt = db()->prepare("UPDATE contacts SET assigned_to = :uid, updated_at = NOW() WHERE id = :cid");
        $stmt->execute(['uid' => $userId, 'cid' => $contactId]);

        // get assigned name
        $nameStmt = db()->prepare("SELECT CONCAT(firstname,' ',lastname) AS name FROM users WHERE id = :uid");
        $nameStmt->execute(['uid' => $userId]);
        $assignedName = $nameStmt->fetchColumn() ?: 'You';

        echo json_encode([
            'ok' => true,
            'message' => 'Contact assigned to you.',
            'data' => ['assigned_to_name' => $assignedName]
        ]);
        exit;
    }

    if ($action === 'toggle_type') {
        $cur = db()->prepare("SELECT type FROM contacts WHERE id = :cid LIMIT 1");
        $cur->execute(['cid' => $contactId]);
        $currentType = $cur->fetchColumn();

        if (!$currentType) {
            echo json_encode(['ok' => false, 'message' => 'Contact not found.']);
            exit;
        }

        $newType = ($currentType === 'Sales Lead') ? 'Support' : 'Sales Lead';

        $stmt = db()->prepare("UPDATE contacts SET type = :t, updated_at = NOW() WHERE id = :cid");
        $stmt->execute(['t' => $newType, 'cid' => $contactId]);

        $toggleLabel = ($newType === 'Sales Lead') ? 'Switch to Support' : 'Switch to Sales Lead';

        echo json_encode([
            'ok' => true,
            'message' => "Contact type updated to {$newType}.",
            'data' => ['type' => $newType, 'toggle_label' => $toggleLabel]
        ]);
        exit;
    }
} catch (PDOException $ex) {
    echo json_encode(['ok' => false, 'message' => 'Database error.']);
    exit;
}
