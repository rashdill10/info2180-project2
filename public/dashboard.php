<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers/auth.php';
require_once __DIR__ . '/../src/helpers/partial.php';

require_login();

function e(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$filter = $_GET['filter'] ?? 'all';


// Build SQL based on filter
$sql = "
    SELECT 
        c.id,
        c.title,
        c.firstname,
        c.lastname,
        c.email,
        c.company,
        c.type
    FROM contacts c
";
$params = [];

if ($filter === 'sales') {
    $sql .= " WHERE c.type = 'Sales Lead'";
} elseif ($filter === 'support') {
    $sql .= " WHERE c.type = 'Support'";
} elseif ($filter === 'assigned') {
    $sql .= " WHERE c.assigned_to = :user_id";
    $params['user_id'] = $userId;
}

$sql .= " ORDER BY c.created_at DESC";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$contacts = $stmt->fetchAll();


// Page Content (this is what AJAX loads)
ob_start();
?>
<div class="page-header">
    <h1>Dashboard</h1>
    <a href="add_contact.php" class="btn-primary">+ Add Contact</a>
</div>

<div class="filters">
    <span>Filter By:</span>
    <a href="dashboard.php?filter=all" class="<?= $filter === 'all' ? 'active' : '' ?>" data-filter-link data-filter="all">All</a>
    <a href="dashboard.php?filter=sales" class="<?= $filter === 'sales' ? 'active' : '' ?>" data-filter-link data-filter="sales">Sales Leads</a>
    <a href="dashboard.php?filter=support" class="<?= $filter === 'support' ? 'active' : '' ?>" data-filter-link data-filter="support">Support</a>
    <a href="dashboard.php?filter=assigned" class="<?= $filter === 'assigned' ? 'active' : '' ?>" data-filter-link data-filter="assigned">Assigned to me</a>
</div>

<table class="contacts-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Company</th>
            <th>Type</th>
            <th></th>
        </tr>
    </thead>
    <tbody id="contactsTbody">
        <?php if (empty($contacts)): ?>
            <tr><td colspan="5">No contacts found.</td></tr>
        <?php else: ?>
            <?php foreach ($contacts as $c): ?>
                <tr>
                    <td><?= e($c['title'].' '.$c['firstname'].' '.$c['lastname']) ?></td>
                    <td><?= e($c['email']) ?></td>
                    <td><?= e($c['company']) ?></td>
                    <td>
                        <span class="badge <?= $c['type'] === 'Sales Lead' ? 'badge-sales' : 'badge-support' ?>">
                            <?= e($c['type']) ?>
                        </span>
                    </td>
                    <td><a href="contact.php?id=<?= (int)$c['id'] ?>">View</a></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?php
$pageContent = ob_get_clean();


// If partial request, return ONLY the inner content
if (is_partial_request()) {
    echo $pageContent;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dolphin CRM | Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<header class="navbar">
    <div class="brand">🐬 Dolphin CRM</div>
</header>

<div class="layout">
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
