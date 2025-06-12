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
    $_SESSION['message'] = 'ID du motif du tissu manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}
$stmt = $pdo->prepare("SELECT id, nom FROM couleur");
$stmt->execute();
$couleurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM couleur_tissu_bois WHERE  id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$motifTissuBois = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$motifTissuBois) {
    $_SESSION['message'] = 'Motif du tissu introuvable.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['name']);
    $price = ($_POST['price']);
    $img = $_FILES['img'];
    $id_couleur = trim($_POST['id_couleur']);


    if (empty($nom)) {
        $_SESSION['message'] = 'Le nom est requis.';
        $_SESSION['message_type'] = 'error';
    } else {
        // Garder l'image actuelle si aucune nouvelle image n'est téléchargée
        $fileName = $motifTissuBois['img'];
        if (!empty($img['name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
            if (!in_array($img['type'], $allowedTypes)) {
                $_SESSION['message'] = 'Seuls les fichiers JPEG, PNG et GIF sont autorisés.';
                $_SESSION['message_type'] = 'error';
            } else {
                $uploadDir = '../uploads/couleur-tissu-bois/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fileName = basename($img['name']);
                $uploadPath = $uploadDir . $fileName;
                if (!move_uploaded_file($img['tmp_name'], $uploadPath)) {
                    $_SESSION['message'] = 'Erreur lors de l\'upload de l\'image.';
                    $_SESSION['message_type'] = 'error';
                }
            }
        }
        if (!isset($_SESSION['message'])) {
            $stmt = $pdo->prepare("UPDATE couleur_tissu_bois SET nom = ?, prix = ?, img = ?, couleur_id = ? WHERE id = ?");
            $stmt->execute([$nom, $price, $fileName, $id_couleur, $id]);
            $_SESSION['message'] = 'Le motif a été mis à jour avec succès !';
            $_SESSION['message_type'] = 'success';
            header("Location: visualiser.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifie un motif de banquette - bois</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/admin/ajout.css">
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
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
            <h2>Modifie un motif de banquette - bois</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form action="edit.php?id=<?php echo $motifTissuBois['id']; ?>" method="POST" enctype="multipart/form-data" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nom <span class="required">*</span></label>
                            <input type="text" id="name" name="name" class="input-field" value="<?php echo htmlspecialchars($motifTissuBois['nom']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="price">Prix (en €) <span class="required">*</span></label>
                            <input type="number" id="price" name="price" class="input-field" value="<?php echo htmlspecialchars($motifTissuBois['prix']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="img">Image (Laissez vide pour conserver l'image actuelle) <span class="required">*</span></label>
                            <input type="file" id="img" name="img" class="input-field" accept="image/*" onchange="loadFile(event)" >
                            <img class="preview-img" src="../uploads/couleur-tissu-bois/<?php echo htmlspecialchars($motifTissuBois['img']); ?>" id="output" />
                         </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="id_couleur">Couleur associé au motif <span class="required">*</span></label>
                            <select class="input-field" id="id_couleur" name="id_couleur">
                                <?php foreach ($couleurs as $couleur): ?>
                                    <option value="<?= htmlspecialchars($couleur['id']) ?>"
                                        <?= ($couleur['id'] == $motifTissuBois['couleur_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($couleur['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="button-section">
                        <div class="buttons">
                            <button type="button" id="btn-retour" class="btn-beige" onclick="history.go(-1)">Retour</button>
                            <input type="submit"  class="btn-noir" value="Mettre à jour">
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