<?php
// public/dashboard.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers/auth.php';

require_login();

$userId = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'all';

/*
|--------------------------------------------------------------------------
| Build SQL based on filter
|--------------------------------------------------------------------------
*/
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dolphin CRM | Dashboard</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<header class="navbar">
    <div class="brand">🐬 Dolphin CRM</div>
</header>

<div class="layout">

    <!-- Sidebar -->
    <aside class="sidebar">
        <a href="dashboard.php" class="active">Home</a>
        <a href="add_contact.php">New Contact</a>
        <a href="users.php">Users</a>
        <a href="logout.php">Logout</a>
    </aside>

    <!-- Main Content -->
    <main class="content">

        <div class="page-header">
            <h1>Dashboard</h1>
            <a href="add_contact.php" class="btn-primary">+ Add Contact</a>
        </div>

        <!-- Filters -->
        <div class="filters">
            <span>Filter By:</span>
            <a href="?filter=all" class="<?= $filter === 'all' ? 'active' : '' ?>">All</a>
            <a href="?filter=sales" class="<?= $filter === 'sales' ? 'active' : '' ?>">Sales Leads</a>
            <a href="?filter=support" class="<?= $filter === 'support' ? 'active' : '' ?>">Support</a>
            <a href="?filter=assigned" class="<?= $filter === 'assigned' ? 'active' : '' ?>">Assigned to me</a>
        </div>

        <!-- Contacts Table -->
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
            <tbody>
                <?php if (empty($contacts)): ?>
                    <tr>
                        <td colspan="5">No contacts found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($contacts as $c): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($c['title'] . ' ' . $c['firstname'] . ' ' . $c['lastname']) ?>
                            </td>
                            <td><?= htmlspecialchars($c['email']) ?></td>
                            <td><?= htmlspecialchars($c['company']) ?></td>
                            <td>
                                <span class="badge <?= $c['type'] === 'Sales Lead' ? 'badge-sales' : 'badge-support' ?>">
                                    <?= htmlspecialchars($c['type']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="contact.php?id=<?= $c['id'] ?>">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

    </main>

</div>

</body>
</html>
