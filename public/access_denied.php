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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<header class="navbar">
    <div class="brand">🐬 Dolphin CRM</div>
</header>

<div class="layout">

    <!-- Sidebar -->
    <aside class="sidebar">
        <a href="dashboard.php" class="active">
            <i class="fa-regular fa-house"></i>
            Home
        </a>

        <a href="add_contact.php">
            <i class="fa-solid fa-user-plus"></i>
            New Contact
        </a>

        <a href="users.php">
            <i class="fa-solid fa-users"></i>
            Users
        </a>

        <a href="logout.php">
            <i class="fa-solid fa-right-from-bracket"></i>
            Logout
        </a>
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
