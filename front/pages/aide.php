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

    require '../../admin/include/cleanFileName.php';
  }
  try {
    // Configuration SMTP
    $mail->isSMTP();
    $env = include __DIR__ . '/../../config/mail.php';
    $mail->Host       = $env['SMTP_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $env['SMTP_USER'];
    $mail->Password   = $env['SMTP_PASS'];
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // From = Aligné avec SMTP
    $mail->setFrom($env['SMTP_USER'], mb_convert_encoding('Déco du Monde', "ISO-8859-1", "UTF-8"));
    $mail->addReplyTo($to, "$prenom $nom");

    // Mail vers l’utilisateur
    $mail->addAddress($to);
    $mail->isHTML(true);
    $mail->Subject = 'Confirmation de l\'envoie du message';
    $mail->Body = "<h2>Merci pour votre message</h2>
                <p><strong>Nom :</strong> $nom<br>
                <strong>Prénom :</strong> $prenom<br>
                <strong>Email :</strong> $to<br>
                <strong>Message :</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>";
    $mail->AltBody = "Merci pour votre message\n\nNom: $nom\nPrénom: $prenom\nEmail: $to\nMessage: $message";

    if (isset($_FILES['image']) && !empty($_FILES['image']['name'][0])) {
      $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'application/pdf'];

      foreach ($_FILES['image']['tmp_name'] as $key => $tmpName) {
        $fileType = mime_content_type($tmpName);
        $fileName = $_FILES['image']['name'][$key];

        if (!in_array($fileType, $allowedTypes)) {
          $uploadErrors[] = "Type de fichier non autorisé : " . htmlspecialchars($fileName);
          continue;
        }

        // Ajouter l'attachement seulement si le fichier est autorisé
        $fileName = cleanFileName($fileName);
        $mail->addAttachment($tmpName, $fileName);
      }
    }
    if (!empty($uploadErrors)) {
      $_SESSION['error'] = implode('<br>', $uploadErrors);
      header("Location: " . $_SERVER['REQUEST_URI']);
      exit;
    }
    $mail->send();

    // Mail vers l'admin
    $mail->clearAddresses();
    $mail->addAddress($env['SMTP_USER']);
    $mail->Subject = mb_convert_encoding('Nouveau message via le formulaire', "ISO-8859-1", "UTF-8");
    $mail->Body = "<h2>Message reçu via le formulaire</h2>
                <p><strong>Nom :</strong> $nom<br>
                <strong>Prénom :</strong> $prenom<br>
                <strong>Email :</strong> $to<br>
                <strong>Message :</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>";
    $mail->AltBody = "Nom: $nom\nPrénom: $prenom\nEmail: $to\nMessage: $message";

    if (isset($_FILES['image']) && !empty($_FILES['image']['name'][0])) {
      $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'application/pdf'];

      foreach ($_FILES['image']['tmp_name'] as $key => $tmpName) {
        $fileType = mime_content_type($tmpName);
        $fileName = $_FILES['image']['name'][$key];

        if (!in_array($fileType, $allowedTypes)) {
          $uploadErrors[] = "Type de fichier non autorisé : " . htmlspecialchars($fileName);
          continue;
        }

        // Ajouter l'attachement seulement si le fichier est autorisé
        $fileName = cleanFileName($fileName);
        $mail->addAttachment($tmpName, $fileName);
      }
    }
    if (!empty($uploadErrors)) {
      $_SESSION['error'] = implode('<br>', $uploadErrors);
      header("Location: " . $_SERVER['REQUEST_URI']);
      exit;
    }
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Besoin d'aide ?</title>
  <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/formulaire.css">
  <link rel="stylesheet" href="../../styles/transition.css">
  <link rel="stylesheet" href="../../styles/buttons.css">
  <link rel="stylesheet" href="../../styles/message.css">
  <script type="module" src="../../script/transition.js"></script>
  <script type="module" src="../../script/upload_img.js"></script>


</head>

<body>
  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>
  <main>

    <div class="container">
      <div class="left-column transition-all">
        <h2 class="h2">Besoin d'aide ?</h2>
        <p>Si tu as besoin d’un renseignement ou de l’aide tu peux appeler un vendeur : </p>
        <p>Tél : 01 48 22 98 05</p>
        <p>ou remplir ce formulaire :</p><br>
        <?php
        if (isset($_SESSION['success'])) {
          echo '<div class="message success">' . $_SESSION['success'] . '</div>';
          unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
          echo '<div class="message error">' . $_SESSION['error'] . '</div>';
          unset($_SESSION['error']);
        }
        ?>
        <form action="" method="POST" enctype="multipart/form-data" class="formulaire-creation-compte">

          <div class="form-row">
            <div class="form-group">
              <label for="nom">Nom <span class="required">*</span></label>
              <input type="text" id="nom" name="nom" class="input-field">

            </div>
            <div class="form-group">
              <label for="prenom">Prénom <span class="required">*</span></label>
              <input type="text" id="prenom" name="prenom" class="input-field">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="mail">Adresse mail <span class="required">*</span></label>
              <input type="email" id="mail" name="mail" class="input-field">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="message">Message <span class="required">*</span></label>
              <textarea id="message" class="input-field" name="message"></textarea>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group dropzone" id="dropzone">
              <input type="file" id="image" name="image[]" accept="image/*, application/pdf" multiple hidden>
              <p><strong>Ajoutez un fichier</strong> ou faites glisser les fichiers ici</p>
              <ul id="file-list"></ul>
            </div>
          </div>
          <div class="footer">
            <div class="buttons">
              <input type="submit" name="envoyer" class="btn-noir" value="Envoyer">
            </div>
          </div>
        </form>
      </div>
      <div class="right-column transition-boom">
        <section class="main-display">
          <img src="../../medias/apropos-galerie8.jpg" alt="canape-marocain">
        </section>
      </div>
    </div>
  </main>
  <?php require_once '../../squelette/footer.php' ?>

</body>

</html>