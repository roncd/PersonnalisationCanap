<?php
require '../../admin/config.php';
session_start();

// determine le lien du link
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['PHP_SELF']); // chemin du dossier actuel
$baseUrl = $protocol . '://' . $host . $path;

//Méthode SMTP
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../vendor/autoload.php'; // adapte le chemin si besoin
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/formulaire.css">
  <link rel="stylesheet" href="../../styles/message.css">
  <link rel="stylesheet" href="../../styles/buttons.css">
  <title>Créer ton compte</title>
  <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
</head>

<body>

  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <main>
    <div class="container">
      <!-- Colonne de gauche -->
      <div class="left-column">
        <h2 class="h2">Créer ton compte</h2>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $nom = htmlspecialchars($_POST['nom']);
          $prenom = htmlspecialchars($_POST['prenom']);
          $mail = filter_var($_POST['adresse'], FILTER_SANITIZE_EMAIL);
          $tel = htmlspecialchars($_POST['telephone']);
          $mdp = password_hash($_POST['motdepasse'], PASSWORD_BCRYPT);
          $adresse = htmlspecialchars($_POST['adresse-livraison']);
          $info = htmlspecialchars($_POST['infos-supplementaires']);
          $codepostal = htmlspecialchars($_POST['code-postal']);
          $ville = htmlspecialchars($_POST['ville']);
        
          $stmt = $pdo->prepare("SELECT id FROM client WHERE mail = ?");
          $stmt->execute([$mail]);
        
          if ($stmt->rowCount() > 0) {
            echo "<div class='message error'>Cet email est déjà utilisé.</div>";
          } else {
            $token = bin2hex(random_bytes(32));
            $link = $baseUrl . "/verification.php?token=$token";
        
            try {
              $stmt = $pdo->prepare("INSERT INTO client(nom, prenom, mail, tel, mdp, adresse, info, codepostal, ville, token, verified) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
              $stmt->execute([$nom, $prenom, $mail, $tel, $mdp, $adresse, $info, $codepostal, $ville, $token]);
        
              // Envoi de l'e-mail de vérification via SMTP
              $env = parse_ini_file(__DIR__ . '/../../.env');
        
              $mailSMTP = new PHPMailer(true);
              $mailSMTP->isSMTP();
              $mailSMTP->Host       = $env['SMTP_HOST'];
              $mailSMTP->SMTPAuth   = true;
              $mailSMTP->Username   = $env['SMTP_USER'];
              $mailSMTP->Password   = $env['SMTP_PASS'];
              $mailSMTP->SMTPSecure = 'tls';
              $mailSMTP->Port       = 587;
        
              $mailSMTP->setFrom($env['SMTP_USER'],  mb_convert_encoding('Déco du Monde', "ISO-8859-1", "UTF-8"));
              $mailSMTP->addAddress($mail, $prenom . ' ' . $nom);
        
              $mailSMTP->Subject =  mb_convert_encoding('Vérification de votre adresse mail', "ISO-8859-1", "UTF-8");
              $mailSMTP->Body    = "Bonjour $prenom,\n\nVeuillez cliquer sur ce lien pour vérifier votre compte :\n$link";
        
              $mailSMTP->send();
              echo "<div class='message success'>Un email de vérification a été envoyé à $mail. Veuillez vérifier votre boîte de réception.</div>";
            } catch (Exception $e) {
              echo "<div class='message error'>Erreur lors de l'envoi de l'e-mail : " . $mailSMTP->ErrorInfo;
            }
          }
        }
        ?>
        <form class="formulaire-creation-compte" method="POST" action="">
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
              <label for="adresse">Adresse mail</label>
              <input type="email" id="adresse" name="adresse" class="input-field" required>
            </div>
            <div class="form-group">
              <label for="telephone">Téléphone</label>
              <input type="text" id="telephone" name="telephone" class="input-field" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="motdepasse">Mot de passe</label>
              <input type="password" id="motdepasse" name="motdepasse" class="input-field" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="adresse-livraison">Adresse de livraison</label>
              <input type="text" id="adresse-livraison" name="adresse-livraison" class="input-field">
            </div>
            <div class="form-group">
              <label for="infos-supplementaires">Informations supplémentaires</label>
              <input type="text" id="infos-supplementaires" name="infos-supplementaires" class="input-field">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="code-postal">Code postal</label>
              <input type="text" id="code-postal" name="code-postal" class="input-field" required>
            </div>
            <div class="form-group">
              <label for="ville">Ville</label>
              <input type="text" id="ville" name="ville" class="input-field" required>
            </div>
          </div>
          <div class="footer">
            <p>Tu as déjà un compte ? <span><a href="Connexion.php" class="link-connect">Connecte-toi</a></span></p>
            <div class="buttons">
              <button type="submit" class="btn-noir">Valider</button>
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