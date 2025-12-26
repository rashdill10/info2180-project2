<?php
// public/users_new.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers/auth.php';

require_admin(); // only Admin can add users

$errors = [];
$success = "";

// Keep old values so form doesn’t wipe on error
$firstname = '';
$lastname  = '';
$email     = '';
$role      = 'Member';

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Password rule: at least 8 chars, 1 letter, 1 number, 1 uppercase
function valid_password(string $password): bool {
    return (bool) preg_match('/^(?=.*[A-Z])(?=.*[a-zA-Z])(?=.*\d).{8,}$/', $password);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Sanitize/trim inputs
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname  = trim($_POST['lastname'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $role      = trim($_POST['role'] ?? 'Member');

    // 2) Validate required fields
    if ($firstname === '') $errors[] = "First name is required.";
    if ($lastname === '')  $errors[] = "Last name is required.";

    if ($email === '') {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if ($password === '') {
        $errors[] = "Password is required.";
    } elseif (!valid_password($password)) {
        $errors[] = "Password must be at least 8 characters long and include at least one uppercase letter and one number.";
    }

    // 3) Validate role
    $allowedRoles = ['Admin', 'Member'];
    if (!in_array($role, $allowedRoles, true)) {
        $errors[] = "Invalid role selected.";
        $role = 'Member';
    }

    // 4) If no errors, insert user
    if (empty($errors)) {
        try {
            // Check if email already exists
            $check = db()->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $check->execute(['email' => $email]);

            if ($check->fetch()) {
                $errors[] = "A user with that email already exists.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = db()->prepare("
                    INSERT INTO users (firstname, lastname, email, password, role, created_at)
                    VALUES (:firstname, :lastname, :email, :password, :role, NOW())
                ");

                $stmt->execute([
                    'firstname' => $firstname,
                    'lastname'  => $lastname,
                    'email'     => $email,
                    'password'  => $hash,
                    'role'      => $role
                ]);

                $success = "User added successfully.";

                // Reset fields after success
                $firstname = $lastname = $email = '';
                $role = 'Member';
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
    <title>Dolphin CRM | New User</title>
    <link rel="stylesheet" href="assets/css/styles.css" />
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
        <a href="users.php" class="active">Users</a>
        <a href="logout.php">Logout</a>
    </aside>

    <!-- Main content -->
    <main class="content">

        <div class="page-header">
            <h1>New User</h1>
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

            <form method="POST" action="users_new.php" class="form-grid" novalidate>

                <div class="field">
                    <label for="firstname">First Name</label>
                    <input class="input" type="text" id="firstname" name="firstname"
                           value="<?= e($firstname) ?>" required>
                </div>

                <div class="field">
                    <label for="lastname">Last Name</label>
                    <input class="input" type="text" id="lastname" name="lastname"
                           value="<?= e($lastname) ?>" required>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input class="input" type="email" id="email" name="email"
                           value="<?= e($email) ?>" required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input class="input" type="password" id="password" name="password" required
                           placeholder="At least 8 chars, 1 uppercase, 1 number">
                </div>

                <div class="field full">
                    <label for="role">Role</label>
                    <select class="input" id="role" name="role">
                        <option value="Member" <?= $role === 'Member' ? 'selected' : '' ?>>Member</option>
                        <option value="Admin" <?= $role === 'Admin' ? 'selected' : '' ?>>Admin</option>
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
