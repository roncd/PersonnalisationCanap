<?php
require '../../admin/config.php';

if (!isset($_GET['token'])) {
    die("Lien invalide.");
}

$token = $_GET['token'];

// Vérifier si le token existe en base
$stmt = $pdo->prepare("SELECT id FROM client WHERE token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Lien invalide ou déjà utilisé.");
}

// Mettre à jour l'utilisateur comme vérifié
$stmt = $pdo->prepare("UPDATE client SET verified = 1, token = NULL WHERE id = ?");
$stmt->execute([$user['id']]);

// Afficher un message stylisé avant la redirection
echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <link rel='icon' type='image/x-icon' href='../../medias/favicon.png'>
    <link rel='stylesheet' href='../../styles/verif.css'>
    <title>Vérification réussie</title>
</head>
<body>
    <div class='message-container'>
        <h1>Votre compte a été vérifié avec succès !</h1>
        <p>Vous serez redirigé(e) vers la page de connexion dans un instant.</p>
    </div>
</body>
</html>";

// Redirige après 3 secondes
header("refresh:3;url=Connexion.php");
exit();
