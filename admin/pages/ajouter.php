<?php
require '../config.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajout d'une donnée</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/bdd.css">
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
</head>

<body>

    <header>
        <?php require '../squelette/header.php'; ?>
    </header>

    <main>
        <div class="container">
            <!-- Colonne de gauche -->
            <h2>Ajoute une donnée</h2>
            <div class="tableau">
                <div class="button-grid-container">
                    <a class="custom-button" href="../client/add.php">Un Client</a>
                    <a class="custom-button" href="../utilisateur/add.php">Un Utilisateur</a>
                    <a class="custom-button" href="../structure/add.php">Une Structure</a>
                    <a class="custom-button" href="../banquette/add.php">Un Type de banquette</a>
                    <a class="custom-button" href="../mousse/add.php">Un Type de mousse</a>
                </div>

                <div class="button-grid-container">
                    <a class="custom-button" href="../couleur-banquette-bois/add.php">Une Couleur bois</a>
                    <a class="custom-button" href="../decoration/add.php">Une Décoration bois</a>
                    <a class="custom-button" href="../accoudoirs-bois/add.php">Un Accoudoir bois</a>

                    <a class="custom-button" href="../dossier-bois/add.php">Un Dossier bois</a>
                    <a class="custom-button" href="../couleur-tissu-bois/add.php">Une Couleur tissu bois</a>
                    <a class="custom-button" href="../motif-bois/add.php">Un Motif coussins bois</a>
                </div>

                <div class="button-grid-container">
                    <a class="custom-button" href="../modele-banquette-tissu/add.php">Un Modèle banquette</a>
                    <a class="custom-button" href="../couleur-tissu-tissu/add.php">Une Couleur tissu</a>
                    <a class="custom-button" href="../motif-tissu/add.php">Un Motif coussin tissu</a>

                    <a class="custom-button" href="../dossier-tissu/add.php">Un Dossier tissu</a>
                    <a class="custom-button" href="../accoudoirs-tissu/add.php">Un Accoudoir tissu</a>
                </div>
            </div>
        </div>
        </div>

    </main>
    <footer>
        <?php require '../squelette/footer.php'; ?>
    </footer>
</body>

</html>