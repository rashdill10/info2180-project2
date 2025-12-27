<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers/auth.php';
require_once __DIR__ . '/../src/helpers/partial.php';

require_login();

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$userId = (int)($_SESSION['user_id'] ?? 0);

$contactId = (int)($_GET['id'] ?? 0);
if ($contactId <= 0) {
    header("Location: dashboard.php");
    exit;
}

$errors = [];
$success = "";


// Handle POST actions (your old non-AJAX fallback)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'assign_to_me') {
            $stmt = db()->prepare("
                UPDATE contacts
                SET assigned_to = :uid, updated_at = NOW()
                WHERE id = :cid
            ");
            $stmt->execute(['uid' => $userId, 'cid' => $contactId]);
            $success = "Contact assigned to you.";

        } elseif ($action === 'toggle_type') {
            $cur = db()->prepare("SELECT type FROM contacts WHERE id = :cid LIMIT 1");
            $cur->execute(['cid' => $contactId]);
            $row = $cur->fetch();

            if (!$row) {
                header("Location: dashboard.php");
                exit;
            }

            $currentType = $row['type'];
            $newType = ($currentType === 'Sales Lead') ? 'Support' : 'Sales Lead';

            $stmt = db()->prepare("
                UPDATE contacts
                SET type = :newType, updated_at = NOW()
                WHERE id = :cid
            ");
            $stmt->execute(['newType' => $newType, 'cid' => $contactId]);
            $success = "Contact type updated to {$newType}.";

        } elseif ($action === 'add_note') {
            $comment = trim($_POST['comment'] ?? '');

            if ($comment === '') {
                $errors[] = "Note cannot be empty.";
            } elseif (mb_strlen($comment) > 2000) {
                $errors[] = "Note is too long (max 2000 characters).";
            }

            if (empty($errors)) {
                $stmt = db()->prepare("
                    INSERT INTO notes (contact_id, comment, created_by, created_at)
                    VALUES (:cid, :comment, :uid, NOW())
                ");
                $stmt->execute([
                    'cid' => $contactId,
                    'comment' => $comment,
                    'uid' => $userId
                ]);

                $up = db()->prepare("UPDATE contacts SET updated_at = NOW() WHERE id = :cid");
                $up->execute(['cid' => $contactId]);

                $success = "Note added successfully.";
            }
        }
    } catch (PDOException $ex) {
        $errors[] = "Database error: " . $ex->getMessage();
    }
}


// Fetch contact details + notes
$contactStmt = db()->prepare("
    SELECT
        c.*,
        CONCAT(cb.firstname, ' ', cb.lastname) AS created_by_name,
        CONCAT(at.firstname, ' ', at.lastname) AS assigned_to_name
    FROM contacts c
    LEFT JOIN users cb ON cb.id = c.created_by
    LEFT JOIN users at ON at.id = c.assigned_to
    WHERE c.id = :cid
    LIMIT 1
");
$contactStmt->execute(['cid' => $contactId]);
$contact = $contactStmt->fetch();

if (!$contact) {
    header("Location: dashboard.php");
    exit;
}

$notesStmt = db()->prepare("
    SELECT
        n.comment,
        n.created_at,
        CONCAT(u.firstname, ' ', u.lastname) AS author_name
    FROM notes n
    INNER JOIN users u ON u.id = n.created_by
    WHERE n.contact_id = :cid
    ORDER BY n.created_at DESC
");
$notesStmt->execute(['cid' => $contactId]);
$notes = $notesStmt->fetchAll();

$toggleLabel = ($contact['type'] === 'Sales Lead') ? 'Switch to Support' : 'Switch to Sales Lead';


// Page Content (this is what partial=1 returns)
ob_start();
?>

<div id="flashArea"></div>

<div class="page" data-page="contact" data-contact-id="<?= (int)$contactId ?>">

    <?php if (!empty($success)): ?>
        <div class="alert success"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert error">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="contact-header">
        <div class="contact-title">
            <h1><?= e($contact['title'] . ' ' . $contact['firstname'] . ' ' . $contact['lastname']) ?></h1>
            <div class="meta">
                Created on <?= format_date($contact['created_at']) ?>
                by <?= e($contact['created_by_name'] ?? 'Unknown') ?><br>
                Updated on <?= format_date($contact['updated_at']) ?>
            </div>
        </div>

        <div class="contact-actions">
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="assign_to_me">
                <button type="button" id="assignBtn" class="btn-success">
                    <i class="fa-solid fa-hand"></i> Assign to me
                </button>
            </form>

            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="toggle_type">
                <button type="button" id="toggleTypeBtn" class="btn-warning">
                    <i class="fa-solid fa-repeat"></i> <?= e($toggleLabel) ?>
                </button>
            </form>
        </div>
    </div>

    <div class="panel contact-card">
        <div class="contact-grid">
            <div class="info-block">
                <div class="label">Email</div>
                <div class="value"><?= e($contact['email']) ?></div>
            </div>

            <div class="info-block">
                <div class="label">Telephone</div>
                <div class="value"><?= e($contact['telephone']) ?></div>
            </div>

            <div class="info-block">
                <div class="label">Company</div>
                <div class="value"><?= e($contact['company']) ?></div>
            </div>

            <div class="info-block">
                <div class="label">Assigned To</div>
                <div class="value" id="assignedToValue"><?= e($contact['assigned_to_name'] ?? 'Unassigned') ?></div>
            </div>
        </div>
    </div>

    <div class="panel notes-panel">
        <div class="notes-header">
            <strong>Notes</strong>
        </div>

        <div class="notes-list" id="notesList">
            <?php if (empty($notes)): ?>
                <p class="muted">No notes yet.</p>
            <?php else: ?>
                <?php foreach ($notes as $n): ?>
                    <div class="note-item">
                        <div class="note-author"><?= e($n['author_name']) ?></div>
                        <div class="note-comment"><?= nl2br(e($n['comment'])) ?></div>
                        <div class="note-date"><?= format_note_datetime($n['created_at']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="note-form-title">
            Add a note about <?= e($contact['firstname']) ?>
        </div>

        <form id="noteForm" class="note-form">
            <textarea id="noteComment" name="comment" class="input textarea" placeholder="Enter details here" required></textarea>
            <div class="actions">
                <button type="submit" class="btn-primary">Add Note</button>
            </div>
        </form>
    </div>
</div>


<?php
$pageContent = ob_get_clean();

if (is_partial_request()) {
    echo $pageContent;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Dolphin CRM | Contact Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/styles.css" />
</head>
<body>

<header class="navbar">
    <div class="brand">🐬 Dolphin CRM</div>
</header>

<div class="layout">
    <aside class="sidebar">
        <a href="dashboard.php" class="active">
            <i class="fa-solid fa-house"></i> Home
        </a>

        <a href="add_contact.php">
            <i class="fa-solid fa-user-plus"></i> New Contact
        </a>

        <a href="users.php">
            <i class="fa-solid fa-users"></i> Users
        </a>

        <a href="logout.php">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </aside>

    <main class="content">
        <div id="appContent">
            <?= $pageContent ?>
        </div>
    </main>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
