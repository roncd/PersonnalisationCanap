<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nouveau mot de passe</title>
  <link rel="icon" type="image/png" href="https://www.decorient.fr/medias/favicon.png">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/formulaire.css">
  <link rel="stylesheet" href="../../styles/message.css">
  <link rel="stylesheet" href="../../styles/reset-pswd.css">
  <link rel="stylesheet" href="../../styles/buttons.css">
  <script type="module" src="../../script/mdp_check.js"></script>
  <script type="module" src="../../script/togglePassword.js"></script>
</head>

<body>
  <main class="connexion">
    <div class="container">
      <h2>Entrez un nouveau mot de passe</h2>
      <?php
      session_start();
      require '../../admin/config.php';
      if (!isset($_GET['token'])) {
        die("<div class='message error'>Lien invalide.</div>");
      }

      $token = $_GET['token'];
      $stmt = $pdo->prepare("SELECT id, mdp, reset_token, reset_expires, token FROM client WHERE reset_token = :token AND reset_expires > NOW()");
      $stmt->execute(['token' => $token]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$user) {
        die("<div class='message error'>Lien invalide ou expiré.</div>");
      }

      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_password = $_POST['mdp'];
        $confirmer = $_POST['confirm-password'] ?? '';

        // Vérifier que le mot de passe est différent de l'ancien
        if (password_verify($new_password, $user['mdp'])) {
          $_SESSION['message'] = 'Le nouveau mot de passe doit être différent de l\'ancien.';
          $_SESSION['message_type'] = 'error';
          header("Location:" . $_SERVER['REQUEST_URI']);
          exit;
        }
        // Validation du nouveau mot de passe
        if (
          strlen($new_password) < 8 ||
          !preg_match('/[A-Z]/', $new_password) ||
          !preg_match('/[a-z]/', $new_password) ||
          !preg_match('/[0-9]/', $new_password) ||
          !preg_match('/[^A-Za-z0-9]/', $new_password)
        ) {
          $_SESSION['message'] = 'Le mot de passe ne respecte pas les critères de sécurité.';
          $_SESSION['message_type'] = 'error';
          header("Location:" . $_SERVER['REQUEST_URI']);
          exit;
        }

        // Hasher le nouveau mot de passe
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE client SET mdp = :mdp, reset_token = NULL, reset_expires = NULL WHERE id = :id");
        $stmt->execute(['mdp' => $hashed_password, 'id' => $user['id']]);
        $_SESSION['message'] = 'Votre mot de passe a été mis à jour.';
        $_SESSION['message_type'] = 'success';
        header("refresh:3;url=Connexion.php");
        exit;
      }
      require '../../admin/include/message.php';
      ?>
      <form method="POST" class="form">
        <div class="form-group">
          <label for="mdp">Nouveau mot de passe <span class="required">*</span></label>
          <div class="input-section">
            <input type="password" id="mdp" name="mdp" class="input-field" required>
            <span class="toggle-password-text">
              Afficher
            </span>
          </div>
        </div>
        <p id="password-strength-text" class="pwd-strenght-text"></p>

        <!-- Checklist dynamique -->
        <ul class="password-requirements">
          <li id="check-length"><span class="check-icon"></span> Minimum 8 caractères</li>
          <li id="check-uppercase"><span class="check-icon"></span> Une lettre majuscule</li>
          <li id="check-lowercase"><span class="check-icon"></span> Une lettre minuscule</li>
          <li id="check-number"><span class="check-icon"></span> Un chiffre</li>
          <li id="check-special"><span class="check-icon"></span> Un caractère spécial (!@#$...)</li>
        </ul>

        <div class="form-group password-confirm-wrapper">
          <label for="confirm-password">Confirmer le mot de passe <span class="required">*</span></label>
          <div class="input-section">
            <input class="input-field" type="password" id="confirm-password" name="confirm-password" required>
            <span class="toggle-password-text">
              Afficher
            </span>
          </div>
        </div>
        <span id="match-message" class="error-message">Les mots de passe ne correspondent pas.</span>

        <div class="button-section">
          <p>Revenir sur <span><a href="../pages/index.php" class="link-connect">Deco du monde</a></span></p>
          <div class="buttons">
            <button type="submit" class="btn-noir">Mettre à jour</button>
          </div>
        </div>
      </form>

    </div>
  </main>
</body>

</html>