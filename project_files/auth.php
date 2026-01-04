<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$timeout = 600; // 10 minutes

// Not logged in → signup
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: signup.php");
    exit;
}

// Session timeout
if (isset($_SESSION['LAST_ACTIVITY']) &&
    (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {

    session_unset();
    session_destroy();
    header("Location: signup.php");
    exit;
}

// Update activity time
$_SESSION['LAST_ACTIVITY'] = time();

