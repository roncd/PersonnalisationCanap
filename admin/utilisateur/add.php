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

    $stmt = $pdo->prepare("SELECT id FROM utilisateur WHERE mail = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = 'Cet email est déjà utilisé.';
        $_SESSION['message_type'] = 'error';
        header("Location: add.php");
        exit();
    }

    // Validation des champs obligatoires
    if (empty($nom) || empty($prenom) || empty($email) || empty($mdp)) {
        $_SESSION['message'] = 'Tous les champs sont requis !';
        $_SESSION['message_type'] = 'error';
    }
    $plainPassword = $_POST['mdp'];

    if (
        strlen($plainPassword) < 8 ||
        !preg_match('/[A-Z]/', $plainPassword) ||
        !preg_match('/[a-z]/', $plainPassword) ||
        !preg_match('/[0-9]/', $plainPassword) ||
        !preg_match('/[^A-Za-z0-9]/', $plainPassword)
    ) {
        $_SESSION['message'] = 'Le mot de passe ne respecte pas les critères de sécurité.';
        $_SESSION['message_type'] = 'error';
        header("Location: add.php");
        exit();
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
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/admin/ajout.css">
    <link rel="stylesheet" href="../../styles/message.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <script type="module" src="../../script/mdp_check.js"></script>
    <script src="../../script/togglePassword.js"></script>

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
                                <option value="Pas précisé">-- Sélectionnez une option --</option>
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
                            <label for="tel">Téléphone</label>
                            <input type="phone" id="tel" name="tel" class="input-field">
                        </div>
                    </div>

                    <!-- Mot de passe -->
                    <div class="form-group">
                        <label for="mdp">Mot de passe <span class="required">*</span></label>
                        <div class="input-section">
                            <input type="password" id="mdp" name="mdp" class="input-field" required>
                            <span class="toggle-password-text"
                                style="cursor: pointer; color: #666; user-select: none; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-weight: 100;">
                                Afficher
                            </span>
                        </div>
                        <p id="password-strength-text" style="font-size: 0.9em; margin-top: 5px;"></p>

                        <!-- Checklist dynamique -->
                        <ul class="password-requirements">
                            <li id="check-length"><span class="check-icon"></span> Minimum 8 caractères</li>
                            <li id="check-uppercase"><span class="check-icon"></span> Une lettre majuscule</li>
                            <li id="check-lowercase"><span class="check-icon"></span> Une lettre minuscule</li>
                            <li id="check-number"><span class="check-icon"></span> Un chiffre</li>
                            <li id="check-special"><span class="check-icon"></span> Un caractère spécial (!@#$...)</li>
                        </ul>
                    </div>

                    <!-- Confirmation -->
                    <div class="form-row">
                        <div class="form-group password-confirm-wrapper">
                            <label for="confirm-password">Confirmer le mot de passe <span class="required">*</span></label>
                            <div class="input-section">
                                <input class="input-field" type="password" id="confirm-password" name="confirm-password" required>
                                <span class="toggle-password-text"
                                    style="cursor: pointer; color: #666; user-select: none; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-weight: 100;">
                                    Afficher
                                </span>
                            </div>

                            <span id="match-message" class="error-message">Les mots de passe ne correspondent pas.</span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="profil">Profil</label>
                            <select class="input-field" id="profil" name="profil">
                                <option value="Administrateur">-- Sélectionnez une option --</option>
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