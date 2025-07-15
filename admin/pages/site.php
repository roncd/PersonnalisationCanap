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
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
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
            <h2>Section - FAQ</h2>
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