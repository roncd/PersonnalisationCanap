<?php 
require '../config.php';
session_start();

if (!isset($_SESSION['id'])){
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
    <link rel="stylesheet" href="../../styles/bdd.css">


    
</head>
<body>

    <header>
    <?php require '../squelette/header.php'; ?>
    </header>

    <main>
        <div class="container">
            <!-- Colonne de gauche -->
            <h2>Vous êtes bien connecté !</h2>
            <h3>Visulaliser les données</h3>
            <div class="tableau">
                <div class="button-grid-container">
                    <a class="custom-button" href="../client/index.php">Client</a>
                    <a class="custom-button" href="../utilisateur/index.php">Utilisateur</a>
                    <a class="custom-button" href="../commande-detail/index.php">Commande</a>
                    
                    <a class="custom-button" href="../structure/index.php">Structure</a>
                    <a class="custom-button" href="../banquette/index.php">Type banquette</a>
                    <a class="custom-button" href="../mousse/index.php">Type mousse</a>
                </div>

                <div class="button-grid-container">
                    <a class="custom-button" href="../couleur-banquette-bois/index.php">Couleur bois</a>
                    <a class="custom-button" href="../decoration/index.php">Décoration bois</a>
                    <a class="custom-button" href="../accoudoirs-bois/index.php">Accoudoir bois</a>
              
                    <a class="custom-button" href="../dossier-bois/index.php">Dossier bois</a>
                    <a class="custom-button" href="../couleur-tissu-bois/index.php">Couleur tissu bois</a>
                    <a class="custom-button" href="../motif-bois/index.php">Motif coussins bois</a>
                </div>

                <div class="button-grid-container">
                    <a class="custom-button" href="../modele-banquette-tissu/index.php">Modèle banquette</a>
                    <a class="custom-button" href="../couleur-tissu-tissu/index.php">Couleur tissu</a>
                    <a class="custom-button"href="../motif-tissu/index.php">Motif coussin tissu</a>
              
                    <a class="custom-button" href="../dossier-tissu/index.php">Dossier tissu</a>
                    <a class="custom-button" href="../accoudoirs-tissu/index.php">Accoudoir tissu</a>
                </div>
            </div> 
            <a href="export_bdd.php" class="export-button">
                <i class="fa-solid fa-download"></i>  Base de données
            </a>     
        </div>        
    </main>
    <footer>
        <?php require '../squelette/footer.php'; ?>
    </footer>
</body>
</html>

