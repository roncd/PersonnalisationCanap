<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../formulaire/Connexion.php"); // Redirection vers la page de connexion
    exit();
}

$userId = $_SESSION['user_id']; // Utilisation de l'ID du client depuis la session

// Récupérer les données actuelles du client
$stmt = $pdo->prepare("SELECT * FROM client WHERE id = ?");
$stmt->execute([$userId]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    $_SESSION['message'] = 'Client introuvable.';
    $_SESSION['message_type'] = 'error';
    header("Location: ../formulaire/Connexion.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et valider les données
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $mail = trim($_POST['mail']);
    $tel = trim($_POST['tel']);
    $mdp = password_hash($_POST['mdp'], PASSWORD_BCRYPT);
    $adresse = trim($_POST['adresse']);
    $info = trim($_POST['info']);
    $codepostal = trim($_POST['codepostal']);
    $ville = trim($_POST['ville']);

    if (empty($nom) || empty($prenom) || empty($mail) || empty($tel) || empty($mdp) || empty($adresse) || empty($codepostal) || empty($ville)) {
        $_SESSION['message'] = 'Tous les champs requis doivent être remplis.';
        $_SESSION['message_type'] = 'error';
    } else {
        // Mettre à jour du client dans la base de données
        $stmt = $pdo->prepare("UPDATE client SET nom = ?, prenom = ?, mail = ?, tel = ?, mdp = ?, adresse = ?, info = ?, codepostal = ?, ville = ? WHERE id = ?");
        if ($stmt->execute([$nom, $prenom, $mail, $tel, $mdp, $adresse, $info, $codepostal, $ville, $userId])) {  // Utilisation de $userId
            $_SESSION['message'] = 'Vos informations ont été mises à jour avec succès !';
            $_SESSION['message_type'] = 'success';
            header("Location: information.php");
            exit();
        } else {
            $_SESSION['message'] = 'Erreur lors de la mise à jour de vos informations.';
            $_SESSION['message_type'] = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informations</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/formulaire.css">
    <style>
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>

    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
            <div class="left-column">
                <h2>Modifiez vos informations</h2>
                <?php
                if (isset($_SESSION['message'])) {
                    echo '<div class="message ' . htmlspecialchars($_SESSION['message_type']) . '">';
                    echo htmlspecialchars($_SESSION['message']);
                    echo '</div>';
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                }
                ?>
                <form action="" method="POST" enctype="multipart/form-data" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom</label>
                            <input type="name" id="nom" name="nom" class="input-field" value="<?php echo htmlspecialchars($client['nom']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="prenom">Prénom</label>
                            <input type="name" id="prenom" name="prenom" class="input-field" value="<?php echo htmlspecialchars($client['prenom']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Mail</label>
                            <input type="email" id="email" name="mail" class="input-field" value="<?php echo htmlspecialchars($client['mail']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="tel">Téléphone</label>
                            <input type="phone" id="tel" name="tel" class="input-field" value="<?php echo htmlspecialchars($client['tel']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mdp">Mot de passe</label>
                            <input type="password" id="mdp" class="input-field" name="mdp" value="<?php echo htmlspecialchars($client['mdp']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="adresse">Adresse</label>
                            <input type="text" id="adresse" class="input-field" name="adresse" value="<?php echo htmlspecialchars($client['adresse']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="info">Info suplémentaire</label>
                            <input type="text" id="info" class="input-field" name="info" value="<?php echo htmlspecialchars($client['info']); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="codepostal">Code postal</label>
                            <input type="codepostal" id="codepostal" class="input-field" name="codepostal" value="<?php echo htmlspecialchars($client['codepostal']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="ville">Ville</label>
                            <input type="ville" id="ville" class="input-field" name="ville" value="<?php echo htmlspecialchars($client['ville']); ?>" required>
                        </div>
                    </div>
                    <div class="footer">
                        <div class="buttons">
                            <button type="button" class="btn-retour" onclick="history.go(-1)">Retour</button>
                            <input type="submit" class="btn-valider" value="Mettre à jour"></input>
                        </div>
                    </div>
                </form>
            </div>
            <div class="right-column">
                <section class="main-display">
                    <img src="../../medias/meknes.png" alt="Armoire">
                </section>
            </div>
        </div>
    </main>
    <footer>
        <?php require '../../squelette/footer.php'; ?>
    </footer>
</body>

</html>