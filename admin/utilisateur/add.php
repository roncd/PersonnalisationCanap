<?php
require '../config.php';
session_start();
require '../include/session_expiration.php';

if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mdp = password_hash($_POST['mdp'], PASSWORD_BCRYPT);
    $nom = trim($_POST['name']);
    $prenom = trim($_POST['prenom']);
    $civilite = trim($_POST['civilite']);
    $profil = trim($_POST['profil']);
    $tel = trim($_POST['tel']);


    // Validation des champs obligatoires
    if (empty($nom) || empty($prenom) || empty($email) || empty($mdp)) {
        $_SESSION['message'] = 'Tous les champs sont requis !';
        $_SESSION['message_type'] = 'error';
    }

    // Tentative d'insertion dans la base de données
    try {
        $stmt = $pdo->prepare("INSERT INTO utilisateur (mail, mdp, nom, prenom, civilite, profil, tel) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$email, $mdp, $nom, $prenom, $civilite, $profil, $tel]);

        $_SESSION['message'] = 'Le membre a été ajouté avec succès !';
        $_SESSION['message_type'] = 'success';
        header("Location: visualiser.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['message'] = 'Erreur lors de l\'ajout du membre : ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
    header("Location: add.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajoute un membre</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/admin/ajout.css">
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link rel="stylesheet" href="../../styles/message.css">

    <link rel="stylesheet" href="../../styles/buttons.css">
</head>

<body>

    <header>
        <?php require '../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
            <h2>Ajoute un membre à l'équipe</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form class="formulaire-creation-compte" action="" method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="civilite">Titre de civilité</label>
                            <select class="input-field" id="civilite" name="civilite">
                                <option value="">-- Sélectionnez une option --</option>
                                <option value="Mme.">Madame</option>
                                <option value="M.">Monsieur</option>
                                <option value="Pas précisé">Ne souhaite pas préciser</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom <span class="required">*</span></label>
                            <input type="name" id="nom" name="name" class="input-field" required>
                        </div>
                        <div class="form-group">
                            <label for="prenom">Prénom <span class="required">*</span></label>
                            <input type="name" id="prenom" name="prenom" class="input-field" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Mail <span class="required">*</span></label>
                            <input type="email" id="email" name="email" class="input-field" required>
                        </div>
                        <div class="form-group">
                            <label for="mdp">Mot de passe <span class="required">*</span></label>
                            <input type="password" id="mdp" name="mdp" class="input-field" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="tel">Téléphone</label>
                            <input type="phone" id="tel" name="tel" class="input-field">
                        </div>
                        <div class="form-group">
                            <label for="profil">Profil</label>
                            <select class="input-field" id="profil" name="profil">
                                <option value="">-- Sélectionnez une option --</option>
                                <option value="Administrateur">Administrateur</option>
                                <option value="Commercial">Commercial</option>
                            </select>
                        </div>
                    </div>
                    <div class="button-section">
                        <div class="buttons">
                            <button type="button" id="btn-retour" class="btn-beige" onclick="history.go(-1)">Retour</button>
                            <input type="submit" class="btn-noir" value="Ajouter"></input>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <footer>
        <?php require '../squelette/footer.php'; ?>
    </footer>
</body>

</html>