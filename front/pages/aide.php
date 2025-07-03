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
// Fonction pour nettoyer le nom du fichier
function cleanFileName($filename) {
    // Supprimer les accents
    $filename = iconv('UTF-8', 'ASCII//TRANSLIT', $filename);
    // Remplacer les caractères non autorisés par des underscores
    $filename = preg_replace('/[^A-Za-z0-9.\-_]/', '_', $filename);
    return $filename;
}

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

            $mail->send();

// Gestion des fichiers uploadés (plusieurs fichiers)
if (isset($_FILES['image']) && !empty($_FILES['image']['name'][0])) {
    foreach ($_FILES['image']['tmp_name'] as $index => $tmpName) {
        if ($_FILES['image']['error'][$index] === 0) {
            $originalName = $_FILES['image']['name'][$index];

            // Nettoyer le nom du fichier
            $cleanName = cleanFileName($originalName);

            // Ajouter l'attachement avec le nom nettoyé
            $mail->addAttachment($tmpName, $cleanName);
        }
    }
}



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
<style>
.dropzone {
  border: 2px dashed #f4c2c2; /* bordure rose par défaut */
  padding: 20px;
  text-align: center;
  cursor: pointer;
  margin-top: 10px;
}
.dropzone.dragover {
  background-color: #f4c2c2; /* fond rose au drag */
  border-color: #e29aaa;    /* bordure un peu plus foncée pour le contraste */
}
#file-list {
  list-style: none;
  padding: 0;
}
#file-list li {
  margin: 5px 0;
  display: flex;
  justify-content: space-between;
background: rgba(227, 209, 200, 0.71);
  padding: 5px 10px;
  border-radius: 4px;
}
</style>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Besoin d'aide ?</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/formulaire.css">
    <link rel="stylesheet" href="../../styles/transition.css">
    <script type="module" src="../../script/transition.js"></script>
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/message.css">
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
      <input type="file" id="image" name="image[]" accept="image/*,video/mp4" multiple hidden>
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
                    <img src="../../medias/meknes.png" alt="Armoire">
                </section>
            </div>
        </div>
    </main>
    <?php require_once '../../squelette/footer.php' ?>

<script>
const dropzone = document.getElementById('dropzone');
const input = document.getElementById('image');
const fileList = document.getElementById('file-list');
let files = [];

// Nettoyer le nom du fichier côté JS (retirer accents et caractères spéciaux)
function cleanFileNameJS(filename) {
  filename = filename.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
  filename = filename.replace(/[^A-Za-z0-9.\-_]/g, "_");
  return filename;
}

dropzone.addEventListener('click', () => input.click());

dropzone.addEventListener('dragover', (e) => {
  e.preventDefault();
  dropzone.classList.add('dragover');
});

dropzone.addEventListener('dragleave', () => {
  dropzone.classList.remove('dragover');
});

dropzone.addEventListener('drop', (e) => {
  e.preventDefault();
  dropzone.classList.remove('dragover');
  handleFiles(e.dataTransfer.files);
});

input.addEventListener('change', () => {
  handleFiles(input.files);
});

function handleFiles(selectedFiles) {
  for (let file of selectedFiles) {
    files.push(file);
  }
  input.files = createFileList(files);
  updateFileList();
}

function updateFileList() {
  fileList.innerHTML = '';
  files.forEach((file, index) => {
    const cleanName = cleanFileNameJS(file.name);
    const li = document.createElement('li');
   li.innerHTML = `<span>${cleanName}</span><button type="button" onclick="removeFile(event, ${index})">×</button>`;

    fileList.appendChild(li);
  });
}

function removeFile(e, index) {
  e.stopPropagation(); // <-- ça empêche le clic de se propager et d’ouvrir le file picker
  files.splice(index, 1);
  input.files = createFileList(files);
  updateFileList();
}



function createFileList(filesArray) {
  const dataTransfer = new DataTransfer();
  filesArray.forEach(file => dataTransfer.items.add(file));
  return dataTransfer.files;
}
</script>

</body>

</html>  