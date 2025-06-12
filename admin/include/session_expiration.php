
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
require '../include/session_expiration.php';
}
// Expire après 3h d'inactivité
$timeout_duration = 10800;

if (isset($_SESSION['LAST_ACTIVITY']) && 
    (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {

    echo "Session expirée. Redirection...";
    $_SESSION = array();
    session_destroy();
    header("Location: ../pages/index.php?expired=1");
    exit;
}

$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} elseif (time() - $_SESSION['CREATED'] > 43200) {
    // ID expiré après 12h
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
}