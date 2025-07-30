<?php
require '../config.php';
session_start();
require '../include/session_expiration.php';


if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['message'] = 'ID de la section manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser-site.php");
    exit();
}

// Récupération de la section à modifier
$stmt = $pdo->prepare("SELECT * FROM sections_apropos WHERE id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$sections = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sections) {
    $_SESSION['message'] = 'Section introuvable.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser-site.php");
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
                header("Location: edit-site.php?id={$id}");
                exit();
            }

            $fileName = basename($img['name']);
            $fileName = cleanFileName($fileName);
            $uploadPath = $uploadDir . $fileName;

            if (!move_uploaded_file($img['tmp_name'], $uploadPath)) {
                $_SESSION['message'] = 'Erreur lors de l\'upload de l\'image.';
                $_SESSION['message_type'] = 'error';
                header("Location: edit-site.php?id={$id}");
                exit();
            }
        } elseif (!empty($existing_img)) {
            $fileName = basename($existing_img);
        } else {
            $_SESSION['message'] = 'Vous devez uploader ou choisir une image existante.';
            $_SESSION['message_type'] = 'error';
            header("Location: edit-site.php?id={$id}");
            exit();
        }

        try {
            $stmt = $pdo->prepare("UPDATE sections_apropos SET slug = ?, titre = ?, description = ?, bouton_texte = ?, bouton_lien = ?, img = ?, visible = ? WHERE id = ?");
            $stmt->execute([$slug, $titre, $description, $btn_text, $btn_link, $fileName, $visible, $id]);
            $_SESSION['message'] = 'La section a été modifiée avec succès !';
            $_SESSION['message_type'] = 'success';
            header("Location: visualiser-site.php");
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $_SESSION['message'] = 'Erreur : Ce nom d\'image est déjà utilisé.';
            } else {
                $_SESSION['message'] = 'Erreur lors de la mise à jour : ' . $e->getMessage();
            }
            $_SESSION['message_type'] = 'error';
        }
    }
    header("Location: edit-site.php?id={$id}");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifie une section - À propos</title>
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
            <h2>Modifie une section - À propos</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form class="formulaire-creation-compte" enctype="multipart/form-data" action="" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="slug">Slug <span class="required">*</span> (ex: hero)</label>
                            <input type="text" id="slug" name="slug" class="input-field" value="<?= htmlspecialchars($sections['slug']) ?>" readonly required>
                        </div>
                        <div class="form-group">
                            <label for="titre">Titre <span class="required">*</span></label>
                            <input type="text" id="titre" name="titre" class="input-field" value="<?= htmlspecialchars($sections['titre']) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="input-field" ><?= htmlspecialchars($sections['description']) ?></textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="btn-text">Texte du bouton </label>
                            <input type="text" id="btn-text" name="btn-text" class="input-field" value="<?= htmlspecialchars($sections['bouton_texte']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="btn-link">Lien du bouton </label>
                            <input type="text" id="btn-link" name="btn-link" class="input-field" value="<?= htmlspecialchars($sections['bouton_lien']) ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="img">Téléverser une image</label>
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
                                $selected = ($basename === $sections['img']) ? 'selected' : '';
                                echo "<option value=\"$basename\" $selected>$basename</option>";
                            }
                            ?>
                        </select>
                        <img class="preview-img" id="existing_output"
                            src="<?= !empty($sections['img']) ? '../../medias/' . htmlspecialchars($sections['img']) : '' ?>" />
                    </div>
                    <div class="form-row">
                        <div class="form-group btn-slider">
                            <label for="visible">Afficher sur le site</label>
                            <label class="switch">
                                <input type="checkbox" id="visible" name="visible" <?php if ($sections['visible']) echo 'checked'; ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="button-section">
                        <div class="buttons">
                            <button type="button" id="btn-retour" class="btn-beige" onclick="history.go(-1)">Retour</button>
                            <input type="submit" class="btn-noir" value="Modifier">
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