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

    <title>Vérification réussie</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .message-container {
            margin-top: 20%;
            padding: 20px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .message-container h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .message-container p {
            font-size: 16px;
        }
    </style>
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
?>
