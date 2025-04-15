<?php

$host = 'mysql-decodumonde.alwaysdata.net';
$dbname = 'decodumonde_personnalisation';
$username = '390609';
$password = '2024DDM!';

try {
    // Connexion à la base de données avec PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Configuration pour afficher les erreurs SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
