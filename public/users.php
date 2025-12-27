<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers/auth.php';
require_once __DIR__ . '/../src/helpers/partial.php';

require_admin(); // ONLY Admins can view users list

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$stmt = db()->query("
    SELECT id, firstname, lastname, email, role, created_at
    FROM users
    ORDER BY created_at DESC
");
$users = $stmt->fetchAll();


// Page Content (this is what partial=1 returns)
ob_start();
?>
<div class="page-header">
    <h1>Users</h1>
    <a href="users_new.php" class="btn-primary">+ Add User</a>
</div>

<div class="panel">
    <table class="contacts-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="4">No users found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= e($u['firstname'] . ' ' . $u['lastname']) ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td><?= e($u['role']) ?></td>
                        <td><?= e((string)$u['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
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
    <title>Dolphin CRM | Users</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/styles.css" />
</head>
<body>

<header class="navbar">
    <div class="brand">🐬 Dolphin CRM</div>
</header>

<div class="layout">

    <aside class="sidebar">
        <a href="dashboard.php">
            <i class="fa-solid fa-house"></i> Home
        </a>

        <a href="add_contact.php">
            <i class="fa-solid fa-user-plus"></i> New Contact
        </a>

        <a href="users.php" class="active">
            <i class="fa-solid fa-users"></i> Users
        </a>

        <a href="logout.php">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </aside>

    <main class="content">
        <div id="appFlash"></div>

        <div id="appContent">
            <?= $pageContent ?>
        </div>
    </main>

</div>

<script src="assets/js/app.js"></script>
</body>
</html>
