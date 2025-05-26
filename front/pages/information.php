<?php
session_start();
require '../../admin/config.php';

// --- Flash message depuis changer_mdp.php ---
if (isset($_SESSION['flash_message'])) {
    $_SESSION['message']      = $_SESSION['flash_message'];
    $_SESSION['message_type'] = $_SESSION['flash_type'];
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../formulaire/Connexion.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Récupérer les données actuelles du client
$stmt = $pdo->prepare("SELECT * FROM client WHERE id = ?");
$stmt->execute([$userId]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    $_SESSION['message']      = 'Client introuvable.';
    $_SESSION['message_type'] = 'error';
    header("Location: ../formulaire/Connexion.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et valider les données
    $nom        = trim($_POST['nom']);
    $prenom     = trim($_POST['prenom']);
    $mail       = trim($_POST['mail']);
    $tel        = trim($_POST['tel']);
    $adresse    = trim($_POST['adresse']);
    $info       = trim($_POST['info']);
    $codepostal = trim($_POST['codepostal']);
    $ville      = trim($_POST['ville']);

    if (empty($nom) || empty($prenom) || empty($mail) || empty($tel) 
        || empty($adresse) || empty($codepostal) || empty($ville)) {
        $_SESSION['message']      = 'Tous les champs requis doivent être remplis.';
        $_SESSION['message_type'] = 'error';
    } else {
        // Mettre à jour le client dans la base de données
        $stmt = $pdo->prepare("
            UPDATE client 
            SET nom = ?, prenom = ?, mail = ?, tel = ?, adresse = ?, info = ?, codepostal = ?, ville = ? 
            WHERE id = ?
        ");
        if ($stmt->execute([
            $nom, $prenom, $mail, $tel, 
            $adresse, $info, $codepostal, $ville, 
            $userId
        ])) {
            $_SESSION['message']      = 'Vos informations ont été mises à jour avec succès !';
            $_SESSION['message_type'] = 'success';
            header("Location: information.php");
            exit();
        } else {
            $_SESSION['message']      = 'Erreur lors de la mise à jour de vos informations.';
            $_SESSION['message_type'] = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informations</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/formulaire.css">
    <link rel="stylesheet" href="../../styles/message.css">
</head>
<body>
    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
            <div class="left-column">
                <h2 class="h2">Modifiez vos informations</h2>

                <?php
                // Affichage des messages d'alerte (erreur ou succès)
                if (isset($_SESSION['message'])) {
                    echo '<div class="message ' . 
                         htmlspecialchars($_SESSION['message_type']) . '">';
                    echo htmlspecialchars($_SESSION['message']);
                    echo '</div>';
                    unset($_SESSION['message'], $_SESSION['message_type']);
                }
                ?>

                <form action="" method="POST" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom</label>
                            <input type="text" id="nom" name="nom" class="input-field"
                                   value="<?= htmlspecialchars($client['nom']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="prenom">Prénom</label>
                            <input type="text" id="prenom" name="prenom" class="input-field"
                                   value="<?= htmlspecialchars($client['prenom']) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Mail</label>
                            <input type="email" id="email" name="mail" class="input-field"
                                   value="<?= htmlspecialchars($client['mail']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="tel">Téléphone</label>
                            <input type="tel" id="tel" name="tel" class="input-field"
                                   value="<?= htmlspecialchars($client['tel']) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <a href="changer_mdp.php" 
                               class="input-field" 
                               style="display: inline-block; text-decoration: none; color: black; text-align: center;">
                                Modifier le mot de passe
                            </a>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="adresse">Adresse</label>
                            <input type="text" id="adresse" name="adresse" class="input-field"
                                   value="<?= htmlspecialchars($client['adresse']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="info">Info supplémentaire</label>
                            <input type="text" id="info" name="info" class="input-field"
                                   value="<?= htmlspecialchars($client['info']) ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="codepostal">Code postal</label>
                            <input type="text" id="codepostal" name="codepostal" class="input-field"
                                   value="<?= htmlspecialchars($client['codepostal']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="ville">Ville</label>
                            <input type="text" id="ville" name="ville" class="input-field"
                                   value="<?= htmlspecialchars($client['ville']) ?>" required>
                        </div>
                    </div>

                    <div class="footer">
                        <div class="buttons">
                            <button type="button" class="btn-retour" onclick="history.back()">
                                ← Retour
                            </button>
                            <button type="submit" class="btn-valider">
                                Mettre à jour
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="right-column">
                <section class="main-display">
                    <img src="../../medias/meknes.png" alt="Illustration">
                </section>
            </div>
        </div>
    </main>
    <footer>
        <?php require '../../squelette/footer.php'; ?>
    </footer>
</body>
</html>
