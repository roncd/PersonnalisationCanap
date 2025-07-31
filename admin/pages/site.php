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
    <title>Site Web - Pages</title>
    <link rel="icon" type="image/png" href="https://www.decorient.fr/medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/admin/bdd.css">
</head>

<body>

    <header>
        <?php require '../squelette/header.php'; ?>
    </header>

    <main class="content">
        <div class="container">
            <div class="header">
                <h1>Éléments du site</h1>
                <p>Sélectionner un élément</p>
            </div>

            <div class="grid-wrapper">
                <section class="category-block">
                    <h2>Page - Accueil</h2>
                    <div class="button-grid">
                        <a href="../sections-accueil/visualiser-site.php" class="btn">Sections</a>
                        <a href="../etapes-accueil/visualiser-site.php" class="btn">Étapes</a>
                        <a href="../stats-accueil/visualiser-site.php" class="btn">Stats</a>
                    </div>
                </section>

                <section class="category-block">
                    <h2>Page - Tableau de bord</h2>
                    <div class="button-grid">
                        <a href="../sections-dashboard/visualiser-site.php" class="btn">Sections</a>
                        <a href="../liste-dashboard/visualiser-site.php" class="btn">Liste</a>
                    </div>
                </section>

                <section class="category-block">
                    <h2>Page - Catalogue</h2>
                    <div class="button-grid">
                        <a href="../sections-catalogue/visualiser-site.php" class="btn">Bannières</a>
                    </div>
                </section>

                <section class="category-block">
                    <h2>Page - À propos</h2>
                    <div class="button-grid">
                        <a href="../sections-apropos/visualiser-site.php" class="btn">Sections</a>
                    </div>
                </section>

                <section class="category-block">
                    <h2>Page - FAQ</h2>
                    <div class="button-grid">
                        <a href="../faq/visualiser-site.php" class="btn">FAQ</a>
                        <a href="../faq-categorie/visualiser-site.php" class="btn">Catégorie FAQ</a>
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