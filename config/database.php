<?php

declare(strict_types=1);

function db(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $host = 'localhost'; //127.0.0.1
        $dbname = 'dolphin_crm';
        $user = '';
        $pass = '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        $pdo = new PDO($dsn, $user, $pass, $options);
    }

    return $pdo;
}
