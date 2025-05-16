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
    <title>Base de données</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/admin/bdd.css">
</head>

<body>

    <header>
        <?php require '../squelette/header.php'; ?>
    </header>

    <main>
        <div class="container">
            <div class="header">
                <h1>Visualiser des options</h1>
                <p>Sélectionner une catégorie</p>
            </div>

            <div class="grid-wrapper">
                <!-- <section class="category-block">
                    <h2>Administration</h2>
                    <div class="button-grid">
                        <a href="../client/index.php" class="btn">Clients</a>
                        <a href="../utilisateur/index.php" class="btn">Equipe</a>
                        <a href="../commande-detail/index.php" class="btn">Commandes</a>
                    </div>
                </section> -->

                <section class="category-block">
                    <h2>Bases canapé</h2>
                    <div class="button-grid">
                        <a href="../structure/index.php" class="btn">Structure</a>
                        <a href="../banquette/index.php" class="btn">Banquette</a>
                        <a href="../mousse/index.php" class="btn">Mousse</a>
                    </div>
                </section>

                <section class="category-block">
                    <h2>Canapé en bois</h2>
                    <div class="button-grid">
                        <a href="../couleur-banquette-bois/index.php" class="btn">Couleur bois</a>
                        <a href="../decoration/index.php" class="btn">Décoration</a>
                        <a href="../accoudoirs-bois/index.php" class="btn">Accoudoirs</a>
                        <a href="../dossier-bois/index.php" class="btn">Dossier</a>
                        <a href="#" class="btn">Couleur tissu</a>
                        <a href="../couleur-tissu-bois/index.php" class="btn">Motif tissu</a>
                        <a href="../motif-bois/index.php" class="btn">Motif coussin</a>
                    </div>
                </section>

                <section class="category-block">
                    <h2>Canapé en tissu</h2>
                    <div class="button-grid">
                        <a href="../modele-banquette-tissu/index.php" class="btn">Modèle</a>
                        <a href="../couleur-tissu-tissu/index.php" class="btn">Accoudoirs</a>
                        <a href="../dossier-tissu/index.php" class="btn">Dossier</a>
                        <a href="../accoudoirs-tissu/index.php" class="btn">Couleur tissu</a>
                        <a href="../motif-tissu/index.php" class="btn">Motif coussin</a>
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