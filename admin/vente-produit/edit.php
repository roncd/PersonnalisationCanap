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
    $_SESSION['message'] = 'ID du produit manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}

$stmt = $pdo->prepare("SELECT id, nom FROM categorie");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les données actuelles du produit
$stmt = $pdo->prepare("SELECT * FROM vente_produit WHERE  id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    $_SESSION['message'] = 'Produit introuvable.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['name']);
    $prix = trim($_POST['prix']);
    $img = $_FILES['img'];
    $id_categorie = trim($_POST['id_categorie']);
    $visible = isset($_POST['visible']) ? 1 : 0;
    $en_rupture = isset($_POST['en_rupture']) ? 1 : 0;



    if (empty($nom) || !isset($prix)  || empty($id_categorie)) {
        $_SESSION['message'] = 'Tous les champs sont requis !';
        $_SESSION['message_type'] = 'error';
    } else {
        // Garder l'image actuelle si aucune nouvelle image n'est téléchargée
        $fileName = $produit['img'];
                $newImageUploaded = false;

        if (!empty($img['name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
            if (!in_array($img['type'], $allowedTypes)) {
                $_SESSION['message'] = 'Seuls les fichiers JPEG, PNG et GIF sont autorisés.';
                $_SESSION['message_type'] = 'error';
            } else {
                $uploadDir = '../uploads/produit/'; // Dossier pour les prdouits 
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                require '../include/cleanFileName.php';
                $fileName = basename($img['name']);
                $fileName = cleanFileName($fileName);
                $uploadPath = $uploadDir . $fileName;
                if (!move_uploaded_file($img['tmp_name'], $uploadPath)) {
                    $_SESSION['message'] = 'Échec du téléchargement de l\'image.';
                    $_SESSION['message_type'] = 'error';
                } else {
                    $newImageUploaded = true;
                }
            }
        }

        if (!isset($_SESSION['message'])) {
            try {
                   // Récupérer le nom du fichier image associé
                $stmt = $pdo->prepare("SELECT img FROM vente_produit WHERE id = :id");
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $ancienneImage = $stmt->fetchColumn();

                $stmt = $pdo->prepare("UPDATE vente_produit SET nom = ?, prix = ?, img = ?, id_categorie = ?, visible = ?, en_rupture = ? WHERE id = ?");
                $stmt->execute([$nom, $prix, $fileName, $id_categorie, $visible, $en_rupture, $id]);

              
               if ($stmt->rowCount() > 0) {
                    // Supprimer le fichier image du serveur
                    if ($newImageUploaded) {
                        $uploadPath = $uploadDir . $fileName;
                        $ancienneImagePath = '../uploads/produit/' . $ancienneImage;

                        if (file_exists($ancienneImagePath)) {
                            $newHash = hash_file('md5', $uploadPath);
                            $oldHash = hash_file('md5', $ancienneImagePath);

                            if ($newHash !== $oldHash) {
                                unlink($ancienneImagePath);
                            } else {
                                $_SESSION['message'] = 'Erreur lors de la suppression de l\'ancienne image';
                                $_SESSION['message_type'] = 'error';
                            }
                        }
                    }
                }
                $_SESSION['message'] = 'Produit mis à jour avec succès!';
                $_SESSION['message_type'] = 'success';
                header("Location: visualiser.php");
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
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le produit </title>
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
            <h2>Modifier le produit</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form action="edit.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nom <span class="required">*</span></label>
                            <input type="text" id="name" name="name" class="input-field" value="<?= htmlspecialchars($produit['nom']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="prix">Prix <span class="required">*</span></label>
                            <input type="text" id="prix" name="prix" class="input-field" value="<?= htmlspecialchars($produit['prix']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="img">Image (Laissez vide pour conserver l'image actuelle) <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <input type="file" id="img" name="img" class="input-field" accept="image/*" onchange="loadFile(event)">
                                <button type="button" class="clear-btn" onclick="clearFileInput('img')" title="Supprimer l'image sélectionnée">
                                    &times;
                                </button>
                            </div>
                            <img class="preview-img" src="../uploads/produit/<?php echo htmlspecialchars($produit['img']); ?>" id="output" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="id_categorie">Catégorie du produit <span class="required">*</span></label>
                            <select class="input-field" id="id_categorie" name="id_categorie">
                                <?php foreach ($categories as $categorie): ?>
                                    <option value="<?= htmlspecialchars($categorie['id']) ?>"
                                        <?= ($categorie['id'] == $produit['id_categorie']) ? 'selected' : '' ?>>
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
                                <input type="checkbox" id="visible" name="visible" <?php if ($produit['visible']) echo 'checked'; ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-row">
    <div class="form-group btn-slider">
        <label for="en_rupture">Produit en rupture</label>
        <label class="switch">
            <input type="checkbox" id="en_rupture" name="en_rupture" <?= ($produit['en_rupture']) ? 'checked' : '' ?>>
            <span class="slider round"></span>
        </label>
    </div>
</div>

                    <div class="button-section">
                        <div class="buttons">
                            <button type="button" id="btn-retour" class="btn-beige" onclick="history.go(-1)">Retour</button>
                            <input type="submit" class="btn-noir" value="Mettre à jour">
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