<?php
declare(strict_types=1);

function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function require_admin(): void {
    require_login();

    if ($_SESSION['user_role'] !== 'Admin') {
        header('Location: access_denied.php');
        exit;
    }
}
