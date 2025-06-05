<?php
session_start();
require '../../admin/config.php';

// 1. Vérification de l’authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: ../formulaire/Connexion.php');
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ancien    = $_POST['ancien']    ?? '';
    $nouveau   = $_POST['nouveau']   ?? '';
    $confirmer = $_POST['confirmer'] ?? '';

    // 2. Récupération du hash actuel
    $stmt = $pdo->prepare("SELECT mdp FROM client WHERE id = ?");
    $stmt->execute([$userId]);
    $client = $stmt->fetch();

    if ($client && password_verify($ancien, $client['mdp'])) {
        if ($nouveau === $confirmer) {
            // 3. Mise à jour en base
            $hash = password_hash($nouveau, PASSWORD_BCRYPT);
            $update = $pdo->prepare("UPDATE client SET mdp = ? WHERE id = ?");
            $update->execute([$hash, $userId]);

            // 4. Préparer le « flash » et rediriger
            $_SESSION['flash_message'] = "Mot de passe modifié avec succès.";
            $_SESSION['flash_type']    = "success";

            header('Location: information.php');
            exit;
        } else {
            $message     = "Les nouveaux mots de passe ne correspondent pas.";
            $messageType = "error";
        }
    } else {
        $message     = "Ancien mot de passe incorrect.";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer le mot de passe</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/changer_mdp.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/message.css">
</head>

<body>
    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>

    <main class="password-page">
        <div class="container">
            <h2>Changer le mot de passe</h2>
            <?php if (isset($message)): ?>
                <div class="message <?= $messageType ?? '' ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="POST">
                <label for="ancien">Ancien mot de passe <span class="required">*</span></label>
                <input type="password" id="ancien" name="ancien" required>

                <label for="nouveau">Nouveau mot de passe <span class="required">*</span></label>
                <input type="password" id="nouveau" name="nouveau" required>

                <label for="confirmer">Confirmer le mot de passe <span class="required">*</span></label>
                <input type="password" id="confirmer" name="confirmer" required>

                <div class="button-footer">
                    <button type="button" id="btn-retour" class="btn-beige" onclick="location.href='../pages/information.php'">
                        Retour
                    </button>
                    <button type="submit" class="btn-noir">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <?php require '../../squelette/footer.php'; ?>
    </footer>
</body>

</html>