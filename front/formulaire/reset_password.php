<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/formulaire.css">
    <link rel="stylesheet" href="../../styles/modif-pswd.css">
</head>

<body>
    <main class="connexion">
        <div class="container">
            <h2>Réinitialisation du mot de passe</h2>
            <?php
            require '../../admin/config.php';
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $email = $_POST['adresse'];
                $stmt = $pdo->prepare("SELECT * FROM client WHERE mail = :mail");
                $stmt->execute(['mail' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $token = bin2hex(random_bytes(50));
                    $stmt = $pdo->prepare("UPDATE client SET reset_token = :token, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE mail = :mail");
                    $stmt->execute(['token' => $token, 'mail' => $email]);
                    $reset_link = "http://diangou-cmr.alwaysdata.net/PersonnalisationCanapLocal/front/formulaire/new_password.php?token=$token";
                    $to = $email;
                    $subject = "Réinitialisation de votre mot de passe";
                    $message = "Cliquez sur ce lien pour réinitialiser votre mot de passe : $reset_link";
                    $headers = "From: decodumonde.alternance@gmail.com\r\n";
                    if (mail($to, $subject, $message, $headers)) {
                        echo "<div class='message success'>Un e-mail de réinitialisation a été envoyé.</div>";
                    } else {
                        echo "<div class='message error'>Erreur lors de l'envoi de l'e-mail.</div>";
                    }
                } else {
                    echo "<div class='message error'>Aucun compte trouvé avec cet e-mail.</div>";
                }
            }
            ?>
            <div class="form">
                <form method="POST" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="adresse">Entrez votre adresse e-mail :</label>
                            <input type="email" id="adresse" name="adresse" class="input-field" required>
                        </div>
                    </div>
                    <div class="buttons">
                        <button type="submit" class="btn-connexion">Envoyer</button>
                    </div>
                </form>
            </div>
            <div class="footer">
                <p>Revenir sur <span><a href="../pages/index.php" class="link-connect">Deco du monde</a></span></p>
            </div>
        </div>
    </main>
</body>

</html>