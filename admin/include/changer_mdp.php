<?php
session_start();
require '../include/session_expiration.php';
require '../config.php';

// 1. Vérification de l’authentification
if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Étape 1 : Récupération des champs
    $ancien    = $_POST['ancien'] ?? '';
    $nouveau   = $_POST['mdp'] ?? '';
    $confirmer = $_POST['confirm-password'] ?? '';

    // Étape 2 : Vérification de l'ancien mot de passe
    $stmt = $pdo->prepare("SELECT mdp FROM utilisateur WHERE id = ?");
    $stmt->execute([$id]);
    $utilisateur = $stmt->fetch();

    if (!$utilisateur || !password_verify($ancien, $utilisateur['mdp'])) {
        $_SESSION['message'] = 'Ancien mot de passe incorrect.';
        $_SESSION['message_type'] = 'error';
        header("Location:" .$_SERVER['REQUEST_URI']);
        exit;
    }

    // Vérifier que le mot de passe est différent de l'ancien
    if (password_verify($nouveau, $utilisateur['mdp'])) {
        $_SESSION['message'] = 'Le nouveau mot de passe doit être différent de l\'ancien.';
        $_SESSION['message_type'] = 'error';
        header("Location:" .$_SERVER['REQUEST_URI']);
        exit;
    }

    // Étape 3 : Validation du nouveau mot de passe
    if (
        strlen($nouveau) < 8 ||
        !preg_match('/[A-Z]/', $nouveau) ||
        !preg_match('/[a-z]/', $nouveau) ||
        !preg_match('/[0-9]/', $nouveau) ||
        !preg_match('/[^A-Za-z0-9]/', $nouveau)
    ) {
        $_SESSION['message'] = 'Le mot de passe ne respecte pas les critères de sécurité.';
        $_SESSION['message_type'] = 'error';
        header("Location:" .$_SERVER['REQUEST_URI']);
        exit;
    }

    // Étape 4 : Mise à jour du mot de passe
    $hash = password_hash($nouveau, PASSWORD_BCRYPT);
    $update = $pdo->prepare("UPDATE utilisateur SET mdp = ? WHERE id = ?");
    $update->execute([$hash, $id]);

    // Étape 5 : Message de succès et redirection
    $_SESSION['flash_message'] = 'Mot de passe modifié avec succès.';
    $_SESSION['flash_type'] = 'success';
    header('Location: ../pages/information.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le mot de passe</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/changer_mdp.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/message.css">
    <script type="module" src="../../script/mdp_check.js"></script>
    <script src="../../script/togglePassword.js"></script>
</head>

<body>
    <header>
        <?php require '../squelette/header.php'; ?>
    </header>

    <main>
        <div class="password-page">
            <div class="container">
                <h2>Modifier le mot de passe</h2>
                <?php require 'message.php'; ?>
                <form method="POST">
                    <label for="ancien">Ancien mot de passe <span class="required">*</span></label>
                    <div class="input-section">
                        <input type="password" id="ancien" name="ancien" required>
                        <span class="toggle-password-text"
                            style="cursor: pointer; color: #666; user-select: none; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-weight: 100;">
                            Afficher
                        </span>
                    </div>

                    <label for="mdp">Nouveau mot de passe <span class="required">*</span></label>
                    <div class="input-section">
                        <input type="password" id="mdp" name="mdp" required>
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

                    <label for="confirm-password">Confirmer le mot de passe <span class="required">*</span></label>
                    <div class="input-section">
                        <input class="password-confirm-wrapper" type="password" id="confirm-password" name="confirm-password" required>
                        <span class="toggle-password-text"
                            style="cursor: pointer; color: #666; user-select: none; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-weight: 100;">
                            Afficher
                        </span>
                    </div>
                    <span id="match-message" class="error-message">Les mots de passe ne correspondent pas.</span>


                    <div class="button-footer">
                        <a class="btn-beige" href="../utilisateur/edit.php?id=<?php echo ($id) ?>">Retour</a>
                        <button type="submit" class="btn-noir">
                            Mettre à jour
                        </button>
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