<?php
require '../../admin/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données envoyées via le formulaire
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $mail = filter_var($_POST['adresse'], FILTER_SANITIZE_EMAIL);
    $tel = htmlspecialchars($_POST['telephone']);
    $mdp = password_hash($_POST['motdepasse'], PASSWORD_BCRYPT);
    $adresse = htmlspecialchars($_POST['adresse-livraison']);
    $info = htmlspecialchars($_POST['infos-supplementaires']);
    $codepostal = htmlspecialchars($_POST['code-postal']);
    $ville = htmlspecialchars($_POST['ville']);

    // Vérification si l'email existe déjà dans la base
    $stmt = $pdo->prepare("SELECT id FROM client WHERE mail = ?");
    $stmt->execute([$mail]);
    if ($stmt->rowCount() > 0) {
        echo "Cet email est déjà utilisé.";
        exit();
    }

    // Génération du token de vérification
    $token = bin2hex(random_bytes(32));  // Crée un token unique pour la vérification

    try {
        // Insertion en base avec le token et statut "non vérifié"
        $stmt = $pdo->prepare("INSERT INTO client(nom, prenom, mail, tel, mdp, adresse, info, codepostal, ville, token, verified) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([$nom, $prenom, $mail, $tel, $mdp, $adresse, $info, $codepostal, $ville, $token]);

        // Envoi de l'email de vérification
        $subject = "Vérification de votre compte";
        $message = "Bonjour $prenom,\n\nVeuillez cliquer sur ce lien pour vérifier votre compte :\n";
        $message .= "https://diangou-cmr.alwaysdata.net/PersonnalisationCanapLocal/front/formulaire/verification.php?token=$token";  
        $headers = "From: no-reply@votre-site.com";

        // Envoi de l'email
        if (mail($mail, $subject, $message, $headers)) {
            echo "Un email de vérification a été envoyé à $mail. Veuillez vérifier votre boîte de réception.";
            exit();
        } else {
            echo "Erreur lors de l'envoi de l'email de vérification.";
            exit();
        }
    } catch (Exception $e) {
        echo "Erreur lors de la création du compte : " . $e->getMessage();
    }
}

?>
