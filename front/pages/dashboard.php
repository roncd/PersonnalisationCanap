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
    <link rel="stylesheet" href="../../styles/dashboard.css">

</head>

<body>
    <header>
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