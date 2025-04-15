<?php
require '../../admin/config.php';
session_start();

// Initialiser la variable du message
$message = '';

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mail = $_POST['adresse'];
    $password = $_POST['motdepasse'];

    // Vérifier si l'email existe dans la base de données
    $stmt = $pdo->prepare("SELECT * FROM client WHERE mail = :mail");
    $stmt->execute(['mail' => $mail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['mdp'])) {
        // Définir les variables de session
        $_SESSION['user_id'] = $user['id'];
        header("Location: index.php");
        exit();
    } else {
        // Email ou mot de passe incorrect
        header("Location: Connexion.php?erreur=1");
        exit();
    }
}

// Vérifier les messages dans les paramètres URL
if (isset($_GET['message']) && $_GET['message'] == 'success') {
    $message = '<p class="success">Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.</p>';
} elseif (isset($_GET['message']) && $_GET['message'] == 'error') {
    $message = '<p class="error">Une erreur est survenue lors de la création de votre compte. Veuillez réessayer.</p>';
} elseif (isset($_GET['erreur']) && $_GET['erreur'] == 1) {
    $message = '<p class="error">Adresse e-mail ou mot de passe incorrect.</p>';
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/formulaire.css">
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <title>Connexion</title>
</head>

<body>

    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>


    <main>
        <div class="container">
            <div class="left-column">
                <h2>Connexion</h2>
                <?php if (!empty($message)) {
                    echo $message;
                } ?>

                <form class="formulaire-creation-compte" method="POST" action="traitement_connexion.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="adresse">Adresse mail</label>
                            <input type="email" id="adresse" name="adresse" class="input-field" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="motdepasse">Mot de passe</label>
                            <input type="password" id="motdepasse" name="motdepasse" class="input-field" required>
                            <div class="footer">

                                <p><span><a href="reset_password.php" class="link-connect">Mot de passe oublié ?</a></span></p>


                            </div>
                        </div>

                    </div>
                    <div class="footer">
                        <p>Tu n'as pas de compte ? <span><a href="CreationCompte.php" class="link-connect">Inscris-toi</a></span></p>


                        <div class="buttons">
                            <button type="submit" class="btn-valider">Valider</button>
                        </div>

                    </div>
                </form>

            </div>

            <!-- Colonne de droite avec l'image -->
            <div class="right-column">
                <section class="main-display">
                    <img src="../../medias/meknes.png" alt="Image d'illustration">
                </section>
            </div>
        </div>
    </main>
    <?php require_once '../../squelette/footer.php' ?>
</body>


</html>