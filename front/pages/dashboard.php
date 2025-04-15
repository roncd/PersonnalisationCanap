<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../formulaire/Connexion.php"); // Redirection vers la page de connexion
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <title>Dashboard</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">

    <style>
        /* Styles généraux */
        .body {
            font-family: 'Be Vietnam Pro', sans-serif;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        h1 {
            font-family: 'Baloo 2', sans-serif;
            font-size: 2.5rem;
            color: #000000;
            margin-bottom: 20px;
            animation: fadeIn 1s ease-in-out;
            /* Animation de fondu */
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }

        .img {
            width: 50%;
            /* Utilise toute la largeur disponible */
            max-width: 1200px;
            /* Limite la largeur maximale */
            height: auto;
            /* Maintient les proportions */
            max-height: 400px;
            /* Limite la hauteur maximale */
            object-fit: cover;
            /* Recadre l'image pour remplir le conteneur */
            margin: 20px 0;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            animation: fadeIn 1s ease-in-out;
            /* Appliquer la même animation que pour le texte */
        }

        .buttons {
            margin-top: 30px;
            animation: slideIn 1s ease-in-out;
            /* Animation de glissement */
        }

        @keyframes slideIn {
            0% {
                transform: translateY(20px);
                opacity: 0;
            }

            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .btn-valider {
            background-color: #000000;
            color: #fff;
            padding: 10px 30px;
            font-weight: bold;
            font-size: 16px;
            border: none;
            border-radius: 12px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
            /* Combinez les deux transitions */
            text-decoration: none;
            /* Supprimer le soulignement */
        }

        .btn-valider:hover {
            background-color: #555555;
            transform: scale(1.1);
            /* Agrandir légèrement le bouton au survol */
        }
    </style>
</head>

<body>
    <header>
        <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
        <?php require '../../squelette/header.php'; ?>
    </header>
    <main>
        <div class="body">
            <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
            <img class="img" src="../../medias/CanapéMeknès_VueDeAngle_-Photoroom.png" alt="Image d'illustration">
            <div class="buttons">
                <a href="../EtapesPersonnalisation/etape1-1-structure.php" class="btn-valider">Commencer la personnalisation</a>
            </div>
        </div>
    </main>
</body>

<footer>
    <?php require '../../squelette/footer.php'; ?>
</footer>

</html>