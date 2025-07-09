<?php
require '../../admin/config.php';
session_start();
require '../../admin/include/session_expiration.php';
require '../../vendor/autoload.php'; 

// determine le lien du link
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['PHP_SELF']); // chemin du dossier actuel
$baseUrl = $protocol . '://' . $host . $path;

//Méthode SMTP
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nom = htmlspecialchars($_POST['nom']);
  $prenom = htmlspecialchars($_POST['prenom']);
  $mail = filter_var($_POST['adresse'], FILTER_SANITIZE_EMAIL);
  $tel = htmlspecialchars($_POST['telephone']);
  $mdp = password_hash($_POST['mdp'], PASSWORD_BCRYPT);
  $adresse = htmlspecialchars($_POST['adresse-livraison']);
  $info = htmlspecialchars($_POST['infos-supplementaires']);
  $codepostal = htmlspecialchars($_POST['code-postal']);
  $ville = htmlspecialchars($_POST['ville']);

  $stmt = $pdo->prepare("SELECT id FROM client WHERE mail = ?");
  $stmt->execute([$mail]);

  if ($stmt->rowCount() > 0) {
    $_SESSION['message'] = 'Cet email est déjà utilisé.';
    $_SESSION['message_type'] = 'error';
    header("Location: CreationCompte.php");
    exit();
  }

  $plainPassword = $_POST['mdp'];

  if (
    strlen($plainPassword) < 8 ||
    !preg_match('/[A-Z]/', $plainPassword) ||
    !preg_match('/[a-z]/', $plainPassword) ||
    !preg_match('/[0-9]/', $plainPassword) ||
    !preg_match('/[^A-Za-z0-9]/', $plainPassword)
  ) {
    $_SESSION['message'] = 'Le mot de passe ne respecte pas les critères de sécurité.';
    $_SESSION['message_type'] = 'error';
    header("Location: CreationCompte.php");
    exit();
  }

  // ... le reste de ton code pour insérer en base, etc.
  if ($_POST['mdp'] !== $_POST['confirm-password']) {
    $_SESSION['message'] = 'Les mots de passe ne correspondent pas.';
    $_SESSION['message_type'] = 'error';
    header("Location: CreationCompte.php");
    exit();
  }

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
    $_SESSION['message'] = 'Un email de vérification a été envoyé à ' . $mail . '. Veuillez vérifier votre boîte de réception.';
    $_SESSION['message_type'] = 'success';
    header("Location: CreationCompte.php");
    exit();
  } catch (Exception $e) {
    $_SESSION['message'] = 'Erreur lors de l’envoi de l’e-mail : ' . htmlspecialchars($mailSMTP->ErrorInfo);
    $_SESSION['message_type'] = 'error';
    header("Location: CreationCompte.php");
    exit();
  }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Créer ton compte</title>
  <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/formulaire.css">
  <link rel="stylesheet" href="../../styles/transition.css">
  <link rel="stylesheet" href="../../styles/message.css">
  <link rel="stylesheet" href="../../styles/buttons.css">
  <script type="module" src="../../script/transition.js"></script>
  <script src="../../script/togglePassword.js"></script>
  <script type="module" src="../../script/mdp_check.js"></script>
</head>

<body>
  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <main>
    <div class="container">
      <!-- Colonne de gauche -->
      <div class="left-column transition-all">
        <h2 class="h2">Créer ton compte</h2>
        <?php require '../../admin/include/message.php';  ?>
        <form class="formulaire-creation-compte" method="POST" action="">
          <!--  Nom / Prénom -->
          <div class="form-row">
            <div class="form-group">
              <label for="nom">Nom <span class="required">*</span></label>
              <input type="text" id="nom" name="nom" class="input-field" required>
            </div>
            <div class="form-group">
              <label for="prenom">Prénom <span class="required">*</span></label>
              <input type="text" id="prenom" name="prenom" class="input-field" required>
            </div>
          </div>

          <!-- Adresse mail / Téléphone -->
          <div class="form-row">
            <div class="form-group">
              <label for="adresse">Adresse mail <span class="required">*</span></label>
              <input type="email" id="adresse" name="adresse" class="input-field" required>
            </div>
            <div class="form-group">
              <label for="telephone">Téléphone <span class="required">*</span></label>
              <input type="text" id="telephone" name="telephone" class="input-field" required>
            </div>
          </div>

          <!--  Mot de passe -->
          <div class="form-row">
            <div class="form-group">
              <label for="mdp">Mot de passe <span class="required">*</span></label>

              <div class="input-section" style="position: relative;">
                <input type="password" id="mdp" name="mdp" class="input-field" required style="padding-right: 60px;">
                <span class="toggle-password-text"
                  style="cursor: pointer; color: #666; user-select: none; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-weight: 100;">
                  Afficher
                </span>
              </div>

              <p id="password-strength-text" style="font-size: 0.9em; margin-top: 5px;"></p>

              <ul class="password-requirements" id="password-checklist">
                <li id="check-length"><span class="check-icon"></span> Minimum 8 caractères</li>
                <li id="check-uppercase"><span class="check-icon"></span> Une lettre majuscule</li>
                <li id="check-lowercase"><span class="check-icon"></span> Une lettre minuscule</li>
                <li id="check-number"><span class="check-icon"></span> Un chiffre</li>
                <li id="check-special"><span class="check-icon"></span> Un caractère spécial (!@#$...)</li>
              </ul>
            </div>
          </div>


          <!-- Confirmer mot de passe -->
          <div class="form-row">
            <div class="form-group password-confirm-wrapper">
              <label for="confirm-password">Confirmer le mot de passe <span class="required">*</span></label>

              <div class="input-section" style="position: relative;">
                <input type="password" id="confirm-password" name="confirm-password" class="input-field" required style="padding-right: 60px;">
                <span class="toggle-password-text"
                  style="cursor: pointer; color: #666; user-select: none; position: absolute; right: 10px; top: 50%; 
                   transform: translateY(-50%); font-weight: 100;">
                  Afficher
                </span>
              </div>

              <p id="match-message" class="error-message">Les mots de passe ne correspondent pas.</p>
            </div>
          </div>

          <!-- Adresse de livraison / Informations supplémentaires -->
          <div class="form-row">
            <div class="form-group">
              <label for="adresse-livraison">Adresse de livraison <span class="required">*</span></label>
              <input type="text" id="adresse-livraison" name="adresse-livraison" class="input-field" required>
            </div>
            <div class="form-group">
              <label for="infos-supplementaires">Informations supplémentaires</label>
              <input type="text" id="infos-supplementaires" name="infos-supplementaires" class="input-field">
            </div>
          </div>

          <!-- Code postal / Ville -->
          <div class="form-row">
            <div class="form-group">
              <label for="code-postal">Code postal <span class="required">*</span></label>
              <input type="text" id="code-postal" name="code-postal" class="input-field" required>
            </div>
            <div class="form-group">
              <label for="ville">Ville <span class="required">*</span></label>
              <input type="text" id="ville" name="ville" class="input-field" required>
            </div>
          </div>
          
          <!-- Pied de formulaire -->
          <div class="footer">
            <p>Tu as déjà un compte ? <span><a href="Connexion.php" class="link-connect">Connecte-toi</a></span></p>
            <div class="buttons">
              <button type="submit" class="btn-noir">Valider</button>
            </div>
          </div>
        </form>

      </div>
      <div class="right-column transition-boom">
        <section class="main-display space">
          <img src="../../medias/apropos-galerie8.jpg" alt="canape-marocain">
        </section>
      </div>
    </div>
  </main>

  <?php require_once '../../squelette/footer.php' ?>
</body>

</html>