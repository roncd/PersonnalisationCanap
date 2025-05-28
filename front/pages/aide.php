<?php
require '../../admin/config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
require '../../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["envoyer"])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $to = $_POST['mail'];
    $message = $_POST['message'];

    if (empty($nom) || empty($to) || empty($message) || empty($prenom)) {
        $_SESSION['error'] = 'Veuillez remplir tous les champs du formulaire.';
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        $mail = new PHPMailer(true);

        try {
            // Configuration SMTP
            $mail->isSMTP();
            $env = parse_ini_file(__DIR__ . '/../../.env');
            $mail->Host       = $env['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $env['SMTP_USER'];
            $mail->Password   = $env['SMTP_PASS'];
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            // From = Aligné avec SMTP
            $mail->setFrom($env['SMTP_USER'], 'Deco du Monde');
            $mail->addReplyTo($to, "$prenom $nom");

            // Mail vers l’utilisateur
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = 'Message envoyé via le formulaire avec succès';
            $mail->Body = "<h2>Merci pour votre message</h2>
                <p><strong>Nom :</strong> $nom<br>
                <strong>Prénom :</strong> $prenom<br>
                <strong>Email :</strong> $to<br>
                <strong>Message :</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>";
            $mail->AltBody = "Merci pour votre message\n\nNom: $nom\nPrénom: $prenom\nEmail: $to\nMessage: $message";

            $mail->send();

            // Mail vers l'admin
            $mail->clearAddresses();
            $mail->addAddress('decodumonde.alternance@gmail.com');
            $mail->Subject = 'Nouveau message via le formulaire';
            $mail->Body = "<h2>Message reçu via le formulaire</h2>
                <p><strong>Nom :</strong> $nom<br>
                <strong>Prénom :</strong> $prenom<br>
                <strong>Email :</strong> $to<br>
                <strong>Message :</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>";
            $mail->AltBody = "Nom: $nom\nPrénom: $prenom\nEmail: $to\nMessage: $message";

            $mail->send();

            $_SESSION['success'] = 'Votre message a été envoyé avec succès.';
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = 'Une erreur s’est produite : ' . $mail->ErrorInfo;
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Besoin d'aide ?</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/formulaire.css">
</head>

<body>
    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>
    <main>
        <?php
if (isset($_SESSION['success'])) {
    echo '<div class="banniere-succes">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="banniere-erreur">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
?>

        <div class="container">
            <div class="left-column">
                <h2>Besoin d'aide ?</h2>
                <p>Si tu as besoin d’un renseignement ou de l’aide tu peux appeler un vendeur : </p>
                <p>Tél : 01 48 22 98 05</p>
                <p>ou remplir ce formulaire :</p><br>
                <form action="" method="POST" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom</label>
                            <input type="text" id="nom" name="nom" class="input-field" required>

                        </div>
                        <div class="form-group">
                            <label for="prenom">Prénom</label>
                            <input type="text" id="prenom" name="prenom" class="input-field" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mail">Adresse mail</label>
                            <input type="email" id="mail" name="mail" class="input-field" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" class="input-field" name="message" required></textarea>
                        </div>
                    </div>
                    <div class="footer">
                        <div class="buttons">
                            <input type="submit" name="envoyer" class="btn-valider" value="Envoyer">
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
    <?php require_once '../../squelette/footer.php' ?>
</body>

</html>