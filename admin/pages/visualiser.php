<?php
require '../config.php';
session_start();
require '../include/session_expiration.php';

if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
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

            <section class="category-block">
                    <h2>Produits à l'unité / Canapé Pré-fait</h2>
                    <div class="button-grid">
                        <a href="../vente-produit/visualiser.php" class="btn">Produit</a>
                        <a href="../categorie/visualiser.php" class="btn">Catégorie produits</a>
                        <a href="../commande-prefait/visualiser.php" class="btn">Canapé Pré-fait</a>
                    </div>
                </section>
                
                <section class="category-block">
                    <h2>Bases canapé</h2>
                    <div class="button-grid">
                        <a href="../structure/visualiser.php" class="btn">Structure</a>
                        <a href="../banquette/visualiser.php" class="btn">Banquette</a>
                        <a href="../mousse/visualiser.php" class="btn">Mousse</a>
                    </div>
                </section>

                <section class="category-block">
                    <h2>Options canapé en bois</h2>
                    <div class="button-grid">
                        <a href="../couleur-banquette-bois/visualiser.php" class="btn">Couleur bois</a>
                        <a href="../decoration/visualiser.php" class="btn">Décoration</a>
                        <a href="../accoudoirs-bois/visualiser.php" class="btn">Accoudoirs</a>
                        <a href="../dossier-bois/visualiser.php" class="btn">Dossier</a>
                        <a href="../couleur/visualiser.php" class="btn">Couleur tissu</a>
                        <a href="../couleur-tissu-bois/visualiser.php" class="btn">Motif tissu</a>
                        <a href="../motif-bois/visualiser.php" class="btn">Kit de coussins</a>
                    </div>
                </section>

                <section class="category-block">
                    <h2>Options canapé en tissu</h2>
                    <div class="button-grid">
                        <a href="../modele-banquette-tissu/visualiser.php" class="btn">Modèle</a>
                        <a href="../accoudoirs-tissu/visualiser.php" class="btn">Accoudoirs</a>
                        <a href="../dossier-tissu/visualiser.php" class="btn">Dossier</a>
                        <a href="../couleur-tissu-tissu/visualiser.php" class="btn">Couleur tissu</a>
                        <a href="../motif-tissu/visualiser.php" class="btn">Kit de coussins</a>
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