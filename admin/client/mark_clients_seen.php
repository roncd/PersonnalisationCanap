<?php
date_default_timezone_set('Europe/Paris');

session_start();
require '../config.php';
require '../include/session_expiration.php';

if (!isset($_SESSION['id'])) {
    header('Location: ../index.php');
    exit();
}

$now = date('Y-m-d H:i:s');

// Mettre à jour la date dans la base de données
$stmt = $pdo->prepare("UPDATE utilisateur SET last_client_check = :now WHERE id = :id");
$stmt->execute([
    'now' => $now,
    'id' => $_SESSION['id']
]);

// Rediriger vers la page de visualisation
header('Location: ../client/visualiser.php');
exit();
