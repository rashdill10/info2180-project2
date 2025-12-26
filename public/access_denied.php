<?php
// public/access_denied.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/helpers/auth.php';

require_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dolphin CRM | Access Denied</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<header class="navbar">
    <div class="brand">🐬 Dolphin CRM</div>
</header>

<div class="layout">

    <!-- Sidebar -->
    <aside class="sidebar">
        <a href="dashboard.php">Home</a>
        <a href="add_contact.php">New Contact</a>
        <a href="users.php">Users</a>
        <a href="logout.php">Logout</a>
    </aside>

    <!-- Main content -->
    <main class="content">
        <div class="panel access-denied">
            <h2>Access Restricted</h2>
            <p>Only <strong>Admin</strong> users can access this page.</p>
        </div>
    </main>

</div>

</body>
</html>
