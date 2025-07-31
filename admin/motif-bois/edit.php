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
    $_SESSION['message'] = 'ID du motif de coussin manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}

$stmt = $pdo->prepare("SELECT id, nom FROM couleur_tissu_bois");
$stmt->execute();
$couleurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les données actuelles du motif de tissu
$stmt = $pdo->prepare("SELECT * FROM motif_bois WHERE  id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$motif = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$motif) {
    $_SESSION['message'] = 'Motif du coussin introuvable.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['name']);
    $prix = trim($_POST['prix']);
    $img = $_FILES['img'];
    $id_couleur = trim($_POST['id_couleur']);
    $visible = isset($_POST['visible']) ? 1 : 0;


    if (empty($nom) || !isset($prix)) {
        $_SESSION['message'] = 'Tous les champs sont requis !';
        $_SESSION['message_type'] = 'error';
    } else {
        // Garder l'image actuelle si aucune nouvelle image n'est téléchargée
        $fileName = $motif['img'];
        $newImageUploaded = false;

        if (!empty($img['name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
            if (!in_array($img['type'], $allowedTypes)) {
                $_SESSION['message'] = 'Seuls les fichiers JPEG, PNG et GIF sont autorisés.';
                $_SESSION['message_type'] = 'error';
            } else {
                $uploadDir = '../uploads/motif-bois/'; // Dossier pour les motifs de tissu
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
                $stmt = $pdo->prepare("SELECT img FROM motif_bois WHERE id = :id");
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $ancienneImage = $stmt->fetchColumn();

                $stmt = $pdo->prepare("UPDATE motif_bois SET nom = ?, prix = ?, img = ?, id_couleur_tissu = ?, visible = ? WHERE id = ?");
                $stmt->execute([$nom, $prix, $fileName, $id_couleur, $visible, $id]);
               
                if ($stmt->rowCount() > 0) {
                    // Supprimer le fichier image du serveur
                    if ($newImageUploaded) {
                        $uploadPath = $uploadDir . $fileName;
                        $ancienneImagePath = '../uploads/motif-bois/' . $ancienneImage;

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
                $_SESSION['message'] = 'Motif mis à jour avec succès!';
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
    <title>Kit de coussins - bois </title>
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
            <h2>Modifier le Kit de coussins - bois</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form action="edit.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nom <span class="required">*</span></label>
                            <input type="text" id="name" name="name" class="input-field" value="<?= htmlspecialchars($motif['nom']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="prix">Prix <span class="required">*</span></label>
                            <input type="text" id="prix" name="prix" class="input-field" value="<?= htmlspecialchars($motif['prix']); ?>" required>
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
                            <img class="preview-img" src="../uploads/motif-bois/<?php echo htmlspecialchars($motif['img']); ?>" id="output" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="id_couleur">Motif de tissu associé <span class="required">*</span></label>
                            <select class="input-field" id="id_couleur" name="id_couleur">
                                <?php foreach ($couleurs as $couleur): ?>
                                    <option value="<?= htmlspecialchars($couleur['id']) ?>"
                                        <?= ($couleur['id'] == $motif['id_couleur_tissu']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($couleur['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group btn-slider">
                            <label for="visible">Afficher sur le site</label>
                            <label class="switch">
                                <input type="checkbox" id="visible" name="visible" <?php if ($motif['visible']) echo 'checked'; ?>>
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