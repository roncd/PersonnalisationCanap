<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié</title>
    <link rel="icon" type="image/png" href="https://www.decorient.fr/medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/formulaire.css">
    <link rel="stylesheet" href="../../styles/message.css">
    <link rel="stylesheet" href="../../styles/reset-pswd.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
</head>

<body>
    <main class="connexion">
        <div class="container">
            <h2>Réinitialisation du mot de passe</h2>
            <?php
            require '../../admin/config.php';

            // determine le lien du reset_link
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $path = dirname($_SERVER['PHP_SELF']); // chemin du dossier actuel
            $baseUrl = $protocol . '://' . $host . $path;

            //Méthode SMTP
            use PHPMailer\PHPMailer\PHPMailer;
            use PHPMailer\PHPMailer\Exception;

            require '../../vendor/autoload.php';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $email = $_POST['adresse'];
                $stmt = $pdo->prepare("SELECT mail, mdp, reset_token, reset_expires, token FROM utilisateur WHERE mail = :mail");
                $stmt->execute(['mail' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $token = bin2hex(random_bytes(50));
                    $stmt = $pdo->prepare("UPDATE utilisateur SET reset_token = :token, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE mail = :mail");
                    $stmt->execute(['token' => $token, 'mail' => $email]);

                    $reset_link = $baseUrl . "/new_pswd.php?token=$token";

                    $mail = new PHPMailer(true);
                    try {
                        $env = include __DIR__ . '/../../config/mail.php';

                        $mail->isSMTP();
                        $mail->Host = $env['SMTP_HOST'];
                        $mail->SMTPAuth = true;
                        $mail->Username = $env['SMTP_USER'];
                        $mail->Password = $env['SMTP_PASS'];
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = 587;

                        $mail->setFrom($env['SMTP_USER'],  mb_convert_encoding('Déco du Monde', "ISO-8859-1", "UTF-8"));
                        $mail->addAddress($email);

                        $mail->Subject = mb_convert_encoding('Réinitialisation de votre mot de passe', "ISO-8859-1", "UTF-8");
                        $mail->Body = "Bonjour,\n\nCliquez sur ce lien pour réinitialiser votre mot de passe de l'administration : $reset_link\n\nCe lien expirera dans une heure.";

                        $mail->send();
                        echo "<div class='message success'>Un e-mail de réinitialisation a été envoyé.</div>";
                    } catch (Exception $e) {
                        error_log('Mailer Error: ' . $mail->ErrorInfo);
                        echo "<div class='message error'>Erreur lors de l'envoi de l'e-mail : " . $mail->ErrorInfo . "</div>";
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
                            <label for="adresse">Entrez votre adresse e-mail <span class="required">*</span></label>
                            <input type="email" id="adresse" name="adresse" class="input-field" required>
                        </div>
                    </div>
                    <div class="button-section">
                        <p>Revenir sur <span><a href="../index.php" class="link-connect">la page de connexion</a></span></p>
                        <div class="buttons">
                            <button type="submit" class="btn-noir">Valider</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </main>
</body>

</html>