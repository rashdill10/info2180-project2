<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/helpers/auth.php';
require_once __DIR__ . '/../src/helpers/partial.php';

require_login();

ob_start();
?>
<div class="panel access-denied">
    <h2>Access Restricted</h2>
    <p>Only <strong>Admin</strong> users can access this page.</p>
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
    <meta charset="UTF-8">
    <title>Dolphin CRM | Access Denied</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/styles.css">
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
        <a href="users.php">
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
