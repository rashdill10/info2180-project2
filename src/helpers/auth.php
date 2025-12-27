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

    if (($_SESSION['user_role'] ?? '') !== 'Admin') {
        // If this request was loaded via AJAX/partial, keep it partial
        $isPartial = (isset($_GET['partial']) && $_GET['partial'] == '1')
            || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        $suffix = $isPartial ? '?partial=1' : '';

        header("Location: access_denied.php{$suffix}");
        exit;
    }
}

function format_date(string $datetime): string {
    return date('F j, Y', strtotime($datetime));
}

function format_note_datetime(string $datetime): string {
    return date('F j, Y \a\t g:ia', strtotime($datetime));
}



