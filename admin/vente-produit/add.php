<?php
require '../config.php';
session_start();
require '../include/session_expiration.php';


if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}

$stmt = $pdo->prepare("SELECT id, nom FROM categorie");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['name']);
    $prix = trim($_POST['price']);
    $img = $_FILES['img'];
    $id_categorie = ($_POST['id_categorie']);
    $visible = isset($_POST['visible']) ? 1 : 0;
    $en_rupture = isset($_POST['en_rupture']) ? 1 : 0;



    if (empty($nom) || !isset($prix) || empty($img['name']) || empty($id_categorie)) {
        $_SESSION['message'] = 'Tous les champs sont requis !';
        $_SESSION['message_type'] = 'error';
    } else {
        $uploadDir = '../uploads/produit/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        if (!in_array($img['type'], $allowedTypes)) {
            $_SESSION['message'] = 'Seuls les fichiers JPEG, PNG et GIF sont autorisés.';
            $_SESSION['message_type'] = 'error';
        } else {
            require '../include/cleanFileName.php';
            $fileName = basename($img['name']);
            $fileName = cleanFileName($fileName);
            $uploadPath = $uploadDir . $fileName;

            if (move_uploaded_file($img['tmp_name'], $uploadPath)) {
                try {
                   $stmt = $pdo->prepare("INSERT INTO vente_produit (nom, prix, img, id_categorie, visible, en_rupture) VALUES (?, ?, ?, ?, ?, ?)");
                   $stmt->execute([$nom, $prix, $fileName, $id_categorie, $visible, $en_rupture]);


                    $_SESSION['message'] = 'Le produit a été ajouté avec succès !';
                    $_SESSION['message_type'] = 'success';
                    header("Location: visualiser.php");
                    exit();
                } catch (Exception $e) {
                    if ($e->getCode() == 23000) {
                        $_SESSION['message'] = 'Erreur : Le nom de l\'image est déjà utilisé.';
                    } else {
                        $_SESSION['message'] = 'Erreur lors de l\'ajout : ' . $e->getMessage();
                    }
                    $_SESSION['message_type'] = 'error';
                }
            } else {
                $_SESSION['message'] = 'Erreur lors de l\'upload de l\'image.';
                $_SESSION['message_type'] = 'error';
            }
        }
    }
    header("Location: add.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajoute un produit vendu à l'unité</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/admin/form.css">
    <link rel="icon" type="image/png" href="https://www.decorient.fr/medias/favicon.png">
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
            <h2>Ajoute un produit vendu à l'unité</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form method="POST" enctype="multipart/form-data" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nom <span class="required">*</span></label>
                            <input type="text" id="name" name="name" class="input-field" required>
                        </div>
                        <div class="form-group">
                            <label for="price">Prix (en €)</label>
                            <input type="number" id="price" name="price" class="input-field" step="0.01" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="img">Image <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <input type="file" id="img" name="img" class="input-field" accept="image/*" onchange="loadFile(event)">
                                <button type="button" class="clear-btn" onclick="clearFileInput('img')" title="Supprimer l'image sélectionnée">
                                    &times;
                                </button>
                            </div>
                            <img class="preview-img" id="output" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="id_categorie">Catégorie du produit <span class="required">*</span></label>
                            <select class="input-field" id="id_categorie" name="id_categorie">
                                <option value="">-- Sélectionnez une catégorie --</option>
                                <?php foreach ($categories as $categorie): ?>
                                    <option value="<?= htmlspecialchars($categorie['id']) ?>">
                                        <?= htmlspecialchars($categorie['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
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
                    <div class="form-row">
    <div class="form-group btn-slider">
        <label for="en_rupture">Produit en rupture</label>
        <label class="switch">
            <input type="checkbox" id="en_rupture" name="en_rupture">
            <span class="slider round"></span>
        </label>
    </div>
</div>
                    <div class="button-section">
                        <div class="buttons">
                            <button type="button" id="btn-retour" class="btn-beige" onclick="history.go(-1)">Retour</button>
                            <input type="submit" class="btn-noir" value="Ajouter">
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