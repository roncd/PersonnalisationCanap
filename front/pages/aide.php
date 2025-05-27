<?php
require '../../admin/config.php';
session_start();
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
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/message.css">
</head>

<body>
    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
            <!-- Colonne de gauche -->
            <div class="left-column">
                <h2 class="h2">Besoin d'aide ?</h2>
                <p>Si tu as besoin d’un renseignement ou de l’aide tu peux appeler un vendeur : </p>
                <p>Tél : 01 48 22 98 05</p>
                <p>ou remplir ce formulaire :</p><br>
                <?php

                if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["envoyer"])) {
                    $nom = $_POST['nom'];
                    $prenom = $_POST['prenom'];
                    $to = $_POST['mail'];;
                    $message = $_POST['message'];

                    if (empty($nom) or empty($to) or empty($message) or empty($prenom)) {
                        echo '<div class="banniere-erreur">Veuillez remplir les champs vides du formulaire.</div>';
                    } else {
                        $sujet = "Confirmation du message";
                        $txt = "Voici le message que vous avez envoyé avec les informations suivantes :\n\n";
                        $txt .= "Nom: $nom\n";
                        $txt .= "Prénom: $prenom\n";
                        $txt .= "Email: $to\n";
                        $txt .= "Message: $message\n";
                        $header = "From: decodumonde.alternance@gmail.com";

                        // Envoie du mail de confirmation à l'user
                        if (mail($to, $sujet, $txt, $header)) {
                            // Envoi du mail à l'admin
                            $sujet_admin = "Copie du message envoyé";
                            $txt_admin = $txt;
                            mail('decodumonde.alternance@gmail.com', $sujet_admin, $txt_admin, $header);

                            echo '<div class="banniere-succes">Votre message a été envoyé avec succès !</div>';
                        } else {
                            echo '<div class="banniere-erreur">Une erreur s\'est produite lors de l\'envoi du message. Veuillez réessayer plus tard.</div>';
                        }
                    }
                }
                ?>
                <form action="" method="POST" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom</label>
                            <input type="text" id="nom" name="nom" class="input-field" require>
                        </div>
                        <div class="form-group">
                            <label for="prenom">Prénom</label>
                            <input type="text" id="prenom" name="prenom" class="input-field" require>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mail">Adresse mail</label>
                            <input type="text" id="mail" name="mail" class="input-field" require>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" class="input-field" name="message" require></textarea>
                        </div>
                    </div>
                    <div class="footer">
                        <div class="buttons">
                            <input type="submit" name="envoyer" class="btn-noir" value="Envoyer">
                        </div>
                    </div>
                </form>

            </div>
            <!-- Colonne de droite avec l'image -->
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