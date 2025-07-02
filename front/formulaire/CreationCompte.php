<?php
require '../../admin/config.php';
session_start();
require '../../admin/include/session_expiration.php';

// determine le lien du link
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['PHP_SELF']); // chemin du dossier actuel
$baseUrl = $protocol . '://' . $host . $path;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $plainPassword = $_POST['motdepasse']; // le bon nom

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
  if ($_POST['motdepasse'] !== $_POST['confirm-password']) {
    $_SESSION['message'] = 'Les mots de passe ne correspondent pas.';
    $_SESSION['message_type'] = 'error';
    header("Location: CreationCompte.php");
    exit();
}

}


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
  <title>Créer ton compte</title>
  <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/formulaire.css">
  <link rel="stylesheet" href="../../styles/transition.css">
  <script type="module" src="../../script/transition.js"></script>
  <link rel="stylesheet" href="../../styles/message.css">
  <link rel="stylesheet" href="../../styles/buttons.css">
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
  <!-- Ligne 1 : Nom / Prénom -->
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

  <!-- Ligne 2 : Adresse mail / Téléphone -->
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

  <!-- Ligne 3 : Adresse de livraison / Informations supplémentaires -->
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

  <!-- Ligne 4 : Code postal / Ville -->
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

<!-- Ligne 5 : Mot de passe -->
<div class="form-row">
  <div class="form-group">
    <label for="motdepasse">Mot de passe <span class="required">*</span></label>
    
    <div class="input-section" style="position: relative;">
      <input type="password" id="motdepasse" name="motdepasse" class="input-field" required style="padding-right: 60px;">
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


  <!-- Ligne 6 : Confirmer mot de passe -->
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
        <section class="main-display">
          <img src="../../medias/meknes.png" alt="Armoire">
        </section>
      </div>
    </div>
  </main>

  <?php require_once '../../squelette/footer.php' ?>

<script>
      const mdpInput = document.getElementById("motdepasse");
      const checklist = document.querySelector(".password-requirements");

      // Affiche la checklist au focus
      mdpInput.addEventListener("focus", () => {
        checklist.classList.add("show");
      });

      // Cache la checklist si on sort du champ ET qu’on ne clique pas dessus
      mdpInput.addEventListener("blur", () => {
        setTimeout(() => {
          // Ne pas masquer si on clique dans la checklist
          if (!document.activeElement.closest(".password-requirements")) {
            checklist.classList.remove("show");
          }
        }, 100);
      });
</script>


<script>
const passwordInput = document.getElementById("motdepasse");
const strengthText = document.getElementById("password-strength-text");

passwordInput.addEventListener("input", function () {
  const value = passwordInput.value;

  const hasLength = value.length >= 8;
  const hasUppercase = /[A-Z]/.test(value);
  const hasLowercase = /[a-z]/.test(value);
  const hasNumber = /[0-9]/.test(value);
  const hasSpecialChar = /[^A-Za-z0-9]/.test(value);

  const criteriaCount = [hasLength, hasUppercase, hasLowercase, hasNumber, hasSpecialChar].filter(Boolean).length;

  // Met à jour visuellement la force du mot de passe
  passwordInput.classList.remove("input-weak", "input-medium", "input-strong");
  if (criteriaCount <= 2) {
    passwordInput.classList.add("input-weak");
    strengthText.textContent = "Mot de passe faible";
    strengthText.style.color = "red";
  } else if (criteriaCount <= 4) {
    passwordInput.classList.add("input-medium");
    strengthText.textContent = "Mot de passe moyen";
    strengthText.style.color = "orange";
  } else {
    passwordInput.classList.add("input-strong");
    strengthText.textContent = "Mot de passe fort";
    strengthText.style.color = "green";
  }

  // Met à jour la checklist
  toggleClass("check-length", hasLength);
  toggleClass("check-uppercase", hasUppercase);
  toggleClass("check-lowercase", hasLowercase);
  toggleClass("check-number", hasNumber);
  toggleClass("check-special", hasSpecialChar);
});

// Fonction pour appliquer ou retirer la classe "valid" sur les éléments de checklist
function toggleClass(id, isValid) {
  const item = document.getElementById(id);
  if (isValid) {
    item.classList.add("valid");
  } else {
    item.classList.remove("valid");
  }
}


</script>

<script>
const password = document.getElementById('motdepasse');
const confirmPassword = document.getElementById('confirm-password');
const matchMessage = document.getElementById('match-message');

function checkPasswordMatch() {
  const pwd = password.value;
  const confirmPwd = confirmPassword.value;

  if (confirmPwd.length === 0) {
    matchMessage.style.display = 'none';
    confirmPassword.classList.remove('input-error', 'input-valid');
    confirmPassword.setCustomValidity('');
    return;
  }

  if (pwd === confirmPwd) {
    matchMessage.style.display = 'none';
    confirmPassword.classList.remove('input-error');
    confirmPassword.classList.add('input-valid');
    confirmPassword.setCustomValidity('');
  } else {
    matchMessage.style.display = 'block';
    confirmPassword.classList.remove('input-valid');
    confirmPassword.classList.add('input-error');
    confirmPassword.setCustomValidity('Les mots de passe ne correspondent pas.');
  }
}

password.addEventListener('input', checkPasswordMatch);
confirmPassword.addEventListener('input', checkPasswordMatch);

</script>
<script src="../../script/togglePassword.js"></script>
</body>

</html>