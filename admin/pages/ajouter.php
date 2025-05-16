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
    <link rel="stylesheet" href="../../styles/admin/bdd.css">
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
</head>

<body>

    <header>
        <?php require '../squelette/header.php'; ?>
    </header>

    <main class="content">
        <div class="container">
            <div class="header">
                <h1>Ajouter des options</h1>
                <p>Sélectionner une catégorie</p>
            </div>

            <div class="grid-wrapper">
                <!-- <section class="category-block">
                    <h2>Administration</h2>
                    <div class="button-grid">
                        <a href="../utilisateur/add.php" class="btn">Equipe</a>
                    </div>
                </section> -->

                <section class="category-block">
                    <h2>Bases canapé</h2>
                    <div class="button-grid">
                        <a href="../structure/add.php" class="btn">Structure</a>
                        <a href="../banquette/add.php" class="btn">Banquette</a>
                        <a href="../mousse/add.php" class="btn">Mousse</a>
                    </div>
                </section>

                <section class="category-block">
                    <h2>Canapé en bois</h2>
                    <div class="button-grid">
                        <a href="../couleur-banquette-bois/add.php" class="btn">Couleur bois</a>
                        <a href="../decoration/add.php" class="btn">Décoration</a>
                        <a href="../accoudoirs-bois/add.php" class="btn">Accoudoirs</a>
                        <a href="../dossier-bois/add.php" class="btn">Dossier</a>
                        <a href="#" class="btn">Couleur tissu</a>
                        <a href="../couleur-tissu-bois/add.php" class="btn">Motif tissu</a>
                        <a href="../motif-bois/add.php" class="btn">Motif coussin</a>
                    </div>
                </section>

                <section class="category-block">
                    <h2>Canapé en tissu</h2>
                    <div class="button-grid">
                        <a href="../modele-banquette-tissu/add.php" class="btn">Modèle</a>
                        <a href="../couleur-tissu-tissu/add.php" class="btn">Accoudoirs</a>
                        <a href="../dossier-tissu/add.php" class="btn">Dossier</a>
                        <a href="../accoudoirs-tissu/add.php" class="btn">Couleur tissu</a>
                        <a href="../motif-tissu/add.php" class="btn">Motif coussin</a>
                    </div>
                </section>
            </div>
        </div>
    </main>
    <footer>
        <?php require '../squelette/footer.php'; ?>
    </footer>
</body>

</html>