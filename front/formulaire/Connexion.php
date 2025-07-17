<?php
require '../../admin/config.php';
session_start();
require '../../admin/include/session_expiration.php';


/* ───────────── Gestion redirection si non connecté ───────────── */
if (!isset($_SESSION['user_id'])) {
    $currentPage = $_SERVER['HTTP_REFERER'] ?? '/index.php';
    if (!isset($_SESSION['redirect_to']) && strpos($currentPage, 'Connexion.php') === false) {
        $_SESSION['redirect_to'] = $currentPage;
    }
}

/* ───────────── Restauration éventuelle d’un add‑to‑cart ───────────── */
if (isset($_SESSION['pending_add_to_cart']) && isset($_SESSION['user_id'])) {
    $_SESSION['temp_post'] = $_SESSION['pending_add_to_cart'];
    unset($_SESSION['pending_add_to_cart']);
    header('Location: ../pages/nosproduits.php?post_restore=1');
    exit;
}

/* ───────────── Initialisation du message ───────────── */
$message = '';

/* ───────────── Formulaire soumis ? ───────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['adresse'];
    $password = $_POST['mdp'];

    /* On récupère TOUTES les infos pour pouvoir tester verified */
    $stmt = $pdo->prepare("SELECT id, prenom, mdp, verified FROM client WHERE mail = :mail LIMIT 1");
    $stmt->execute(['mail' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {

        /* 1) Compte non vérifié */
        if (!$user['verified']) {
            $message = '<p class="error">Compte non vérifié&nbsp;: consulte ton e‑mail de confirmation.</p>';

        /* 2) Mot de passe ok → connexion */
        } elseif (password_verify($password, $user['mdp'])) {

            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['prenom'];

            /* Restaurer éventuel post add‑to‑cart */
            if (isset($_SESSION['pending_add_to_cart'])) {
                $_SESSION['temp_post'] = $_SESSION['pending_add_to_cart'];
                unset($_SESSION['pending_add_to_cart']);
                header('Location: ../pages/nosproduits.php?post_restore=1');
                exit;
            }

            /* Redirection vers la page demandée avant login */
            if (!empty($_SESSION['redirect_to'])) {
                $redirect_to = $_SESSION['redirect_to'];
                unset($_SESSION['redirect_to']);
                header("Location: $redirect_to");
                exit;
            }

            /* Sinon page d’accueil */
            header("Location: ../pages/index.php");
            exit;

        /* 3) Mot de passe incorrect */
        } else {
            $message = '<p class="error">Mot de passe incorrect.</p>';
        }

    } else {
        $message = '<p class="error">Utilisateur non trouvé.</p>';
    }
}

/* ───────────── Messages GET (création compte, etc.) ───────────── */
if (isset($_GET['message']) && $_GET['message'] == 'success') {
    $message = '<p class="success">Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.</p>';
} elseif (isset($_GET['message']) && $_GET['message'] == 'error') {
    $message = '<p class="error">Une erreur est survenue lors de la création de votre compte. Veuillez réessayer.</p>';
} elseif (isset($_GET['erreur']) && $_GET['erreur'] == 1) {
    $message = '<p class="error">Adresse e‑mail ou mot de passe incorrect.</p>';
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/formulaire.css">
    <link rel="stylesheet" href="../../styles/transition.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/message.css">
    <script type="module" src="../../script/togglePassword.js"></script>
    <script type="module" src="../../script/transition.js"></script>
    <script type="module" src="../../script/warningMajActive.js"></script>

</head>

<body>
    <?php include '../cookies/index.html'; ?>
    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
            <div class="left-column transition-all">
                <h2 class="h2">Connexion</h2>
                <?php if (!empty($message)) {
                    echo $message;
                } ?>
                <form class="formulaire-creation-compte" method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="adresse">Adresse mail</label>
                            <input type="email" id="adresse" name="adresse" class="input-field" required
                                value="<?php echo isset($_POST['adresse']) ? htmlspecialchars($_POST['adresse']) : ''; ?>">

                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="mdp">Mot de passe</label>
                            <div class="input-section">
                                <input type="password" id="mdp" name="mdp" class="input-field" required>
                                <span class="toggle-password-text"
                                    style="cursor: pointer; color: #666; user-select: none; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-weight: 100;">
                                    Afficher
                                </span>

                            </div>
                            <div id="caps-lock-warning" style="display:none; font-size: 0.85em; margin-top: 4px;">
                                ⚠️ Attention : Verr Maj est activé !
                            </div>
                            <div id="shift-warning" style="display:none; font-size: 0.85em; margin-top: 4px;">
                                ⚠️ Attention : La touche Maj (Shift) est maintenue !
                            </div>
                            <div class="footer">
                                <p><span><a href="reset_password.php" class="link-connect">Mot de passe oublié ?</a></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="footer">
                        <p>Tu n'as pas de compte ? <span><a href="CreationCompte.php" class="link-connect">Inscris-toi</a></span></p>
                        <div class="buttons">
                            <button type="submit" class="btn-noir">Valider</button>
                        </div>
                    </div>
                </form>

            </div>

            <!-- Colonne de droite avec l'image -->
            <div class="right-column  transition-boom">
                <section class="main-display">
                    <img src="../../medias/apropos-galerie8.jpg" alt="canape-marocain">
                </section>
            </div>
        </div>
    </main>
    <?php require_once '../../squelette/footer.php' ?>


</body>



</html>