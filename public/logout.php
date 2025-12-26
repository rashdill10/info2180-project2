<?php
// public/logout.php

require_once __DIR__ . '/../config/config.php';

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: login.php');
exit;
