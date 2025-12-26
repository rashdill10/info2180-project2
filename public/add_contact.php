<?php
// public/add_contact.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers/auth.php';

require_login();

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$userId = (int)($_SESSION['user_id'] ?? 0);

$errors = [];
$success = "";

// Default form values
$title = 'Mr';
$firstname = '';
$lastname = '';
$email = '';
$telephone = '';
$company = '';
$type = 'Sales Lead';
$assigned_to = 0;

// Load users for "Assigned To" dropdown
$usersStmt = db()->query("SELECT id, firstname, lastname FROM users ORDER BY firstname, lastname");
$users = $usersStmt->fetchAll();

// Helper: validate type and title
$allowedTitles = ['Mr', 'Mrs', 'Ms', 'Dr', 'Prof'];
$allowedTypes  = ['Sales Lead', 'Support'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Sanitize and trim
    $title      = trim($_POST['title'] ?? 'Mr');
    $firstname  = trim($_POST['firstname'] ?? '');
    $lastname   = trim($_POST['lastname'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $telephone  = trim($_POST['telephone'] ?? '');
    $company    = trim($_POST['company'] ?? '');
    $type       = trim($_POST['type'] ?? 'Sales Lead');
    $assigned_to = (int)($_POST['assigned_to'] ?? 0);

    // 2) Validate title/type
    if (!in_array($title, $allowedTitles, true)) {
        $errors[] = "Invalid title selected.";
        $title = 'Mr';
    }
    if (!in_array($type, $allowedTypes, true)) {
        $errors[] = "Invalid contact type selected.";
        $type = 'Sales Lead';
    }

    // 3) Validate required fields
    if ($firstname === '') $errors[] = "First name is required.";
    if ($lastname === '')  $errors[] = "Last name is required.";

    if ($email === '') {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if ($telephone === '') $errors[] = "Telephone is required.";
    if ($company === '')   $errors[] = "Company is required.";

    // 4) Validate assigned_to exists
    $validUserIds = array_map(fn($u) => (int)$u['id'], $users);
    if ($assigned_to === 0 || !in_array($assigned_to, $validUserIds, true)) {
        $errors[] = "Please select a valid user for Assigned To.";
    }

    // 5) If no errors -> insert contact
    if (empty($errors)) {
        try {
            // Optional: prevent duplicate email contacts (if you want)
            // $dup = db()->prepare("SELECT id FROM contacts WHERE email = :email LIMIT 1");
            // $dup->execute(['email' => $email]);
            // if ($dup->fetch()) { $errors[] = "A contact with that email already exists."; }

            if (empty($errors)) {
                $stmt = db()->prepare("
                    INSERT INTO contacts
                        (title, firstname, lastname, email, telephone, company, type, assigned_to, created_by, created_at, updated_at)
                    VALUES
                        (:title, :firstname, :lastname, :email, :telephone, :company, :type, :assigned_to, :created_by, NOW(), NOW())
                ");

                $stmt->execute([
                    'title'       => $title,
                    'firstname'   => $firstname,
                    'lastname'    => $lastname,
                    'email'       => $email,
                    'telephone'   => $telephone,
                    'company'     => $company,
                    'type'        => $type,
                    'assigned_to' => $assigned_to,
                    'created_by'  => $userId
                ]);

                $success = "Contact added successfully.";

                // Reset form after success
                $title = 'Mr';
                $firstname = $lastname = $email = $telephone = $company = '';
                $type = 'Sales Lead';
                $assigned_to = 0;
            }
        } catch (PDOException $ex) {
            $errors[] = "Database error: " . $ex->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Dolphin CRM | New Contact</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/styles.css" />
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
        <div class="page-header">
            <h1>New Contact</h1>
        </div>

        <div class="panel">
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

            <form method="POST" action="add_contact.php" class="form-grid" novalidate>

                <div class="field full">
                    <label for="title">Title</label>
                    <select class="input" id="title" name="title">
                        <?php foreach ($allowedTitles as $t): ?>
                            <option value="<?= e($t) ?>" <?= $title === $t ? 'selected' : '' ?>>
                                <?= e($t) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="firstname">First Name</label>
                    <input class="input" type="text" id="firstname" name="firstname" value="<?= e($firstname) ?>" required>
                </div>

                <div class="field">
                    <label for="lastname">Last Name</label>
                    <input class="input" type="text" id="lastname" name="lastname" value="<?= e($lastname) ?>" required>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input class="input" type="email" id="email" name="email" value="<?= e($email) ?>" required>
                </div>

                <div class="field">
                    <label for="telephone">Telephone</label>
                    <input class="input" type="text" id="telephone" name="telephone" value="<?= e($telephone) ?>" required>
                </div>

                <div class="field">
                    <label for="company">Company</label>
                    <input class="input" type="text" id="company" name="company" value="<?= e($company) ?>" required>
                </div>

                <div class="field">
                    <label for="type">Type</label>
                    <select class="input" id="type" name="type">
                        <?php foreach ($allowedTypes as $ct): ?>
                            <option value="<?= e($ct) ?>" <?= $type === $ct ? 'selected' : '' ?>>
                                <?= e($ct) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field full">
                    <label for="assigned_to">Assigned To</label>
                    <select class="input" id="assigned_to" name="assigned_to" required>
                        <option value="0">Select a user</option>
                        <?php foreach ($users as $u): ?>
                            <?php $uid = (int)$u['id']; ?>
                            <option value="<?= $uid ?>" <?= $assigned_to === $uid ? 'selected' : '' ?>>
                                <?= e($u['firstname'] . ' ' . $u['lastname']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="actions">
                    <button type="submit" class="btn-primary">Save</button>
                </div>

            </form>
        </div>
    </main>
</div>

</body>
</html>
