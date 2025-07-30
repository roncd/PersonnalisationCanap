<?php
require '../config.php';
session_start();
require '../include/session_expiration.php';


if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slug = trim($_POST['slug']);
    $titre = trim($_POST['titre']);
    $description = $_POST['description'];
    $btn_text = trim($_POST['btn-text']);
    $btn_link = trim($_POST['btn-link']);
    $img = $_FILES['img'];
    $existing_img = isset($_POST['existing_img']) ? trim($_POST['existing_img']) : '';
    $visible = isset($_POST['visible']) ? 1 : 0;


    if (empty($slug) || empty($titre)) {
        $_SESSION['message'] = 'Tous les champs sont requis !';
        $_SESSION['message_type'] = 'error';
    } else {
        $uploadDir = '../../medias/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        require '../include/cleanFileName.php';

        if (!empty($img['name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
            if (!in_array($img['type'], $allowedTypes)) {
                $_SESSION['message'] = 'Seuls les fichiers JPEG, JPG, PNG et GIF sont autorisés.';
                $_SESSION['message_type'] = 'error';
            }

            $fileName = basename($img['name']);
            $fileName = cleanFileName($fileName);
            $uploadPath = $uploadDir . $fileName;

            if (!move_uploaded_file($img['tmp_name'], $uploadPath)) {
                $_SESSION['message'] = 'Erreur lors de l\'upload de l\'image.';
                $_SESSION['message_type'] = 'error';
                header("Location: add-site.php");
                exit();
            }
        } elseif (!empty($existing_img)) {
            $fileName = basename($existing_img);
        } else {
            $_SESSION['message'] = 'Vous devez uploader ou choisir une image existante.';
            $_SESSION['message_type'] = 'error';
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO sections_apropos (slug, titre, description, bouton_texte, bouton_lien, img, visible) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$slug, $titre, $description, $btn_text, $btn_link, $fileName, $visible]);

            $_SESSION['message'] = 'La section a été ajoutée avec succès !';
            $_SESSION['message_type'] = 'success';
            header("Location: visualiser-site.php");
            exit();
        } catch (Exception $e) {
            if ($e->getCode() == 23000) {
                $_SESSION['message'] = 'Erreur : Le nom de l\'image est déjà utilisé.';
            } else {
                $_SESSION['message'] = 'Erreur lors de l\'ajout : ' . $e->getMessage();
            }
            $_SESSION['message_type'] = 'error';
        }
    }

    header("Location: add-site.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajoute une section - À propos</title>
    <link rel="icon" type="image/png" href="https://www.decorient.fr/medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/admin/form.css">
    <link rel="stylesheet" href="../../styles/message.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <script src="../../script/previewImage.js"></script>
</head>

<body>

    <header>
        <?php require '../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
            <h2>Ajoute une section - À propos</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form method="POST" enctype="multipart/form-data" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="slug">Slug <span class="required">*</span> (ex: hero)</label>
                            <input type="text" id="slug" name="slug" class="input-field" required>
                        </div>
                        <div class="form-group">
                            <label for="titre">Titre <span class="required">*</span></label>
                            <input type="text" id="titre" name="titre" class="input-field" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="input-field" ></textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="btn-text">Texte du bouton</label>
                            <input type="text" id="btn-text" name="btn-text" class="input-field">
                        </div>
                        <div class="form-group">
                            <label for="btn-link">Lien du bouton </label>
                            <input type="text" id="btn-link" name="btn-link" class="input-field">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="img">Téléverser une image </label>
                            <div class="input-wrapper">
                                <input type="file" id="img" name="img" class="input-field" accept="image/*" onchange="handleBothImageEvents(event)">
                                <button type="button" class="clear-btn" onclick="clearFileInput('img')" title="Supprimer l'image sélectionnée">
                                    &times;
                                </button>
                            </div>
                            <img class="preview-img" id="output" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="existing_img">Ou choisir une image existante</label>
                        <select id="existing_img" name="existing_img" class="input-field" onchange="previewExistingImage(this.value)">
                            <option value="">-- Sélectionner une image --</option>
                            <?php
                            $images = glob('../../medias/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
                            foreach ($images as $img) {
                                $basename = basename($img);
                                echo "<option value=\"$basename\">$basename</option>";
                            }
                            ?>
                        </select>
                        <img class="preview-img" id="existing_output" />
                    </div>
                    <div class="form-row">
                        <div class="form-group btn-slider">
                            <label for="visible">Afficher sur le site</label>
                            <label class="switch">
                                <input type="checkbox" id="visible" name="visible" checked>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="button-section">
                        <div class="buttons">
                            <button type="button" id="btn-retour" class="btn-beige" onclick="history.go(-1)">Retour</button>
                            <input type="submit" class="btn-noir" value="Ajouter"></input>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <footer>
        <?php require '../squelette/footer.php'; ?>
    </footer>
</body>

</html>