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
    $_SESSION['message'] = 'ID de la couleur du bois manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}

// Récupérer les données actuelles de couleur du bois
$stmt = $pdo->prepare("SELECT * FROM couleur_bois WHERE  id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$couleurbois = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$couleurbois) {
    $_SESSION['message'] = 'Couleur du bois introuvable.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['name']);
    $price = ($_POST['price']);
    $img = $_FILES['img'];

    if (empty($nom)) {
        $_SESSION['message'] = 'Le nom est requis.';
        $_SESSION['message_type'] = 'error';
    } else {
        // Garder l'image actuelle si aucune nouvelle image n'est téléchargée
        $fileName = $couleurbois['img'];
        if (!empty($img['name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
            if (!in_array($img['type'], $allowedTypes)) {
                $_SESSION['message'] = 'Seuls les fichiers JPEG, PNG et GIF sont autorisés.';
                $_SESSION['message_type'] = 'error';
            } else {
                $uploadDir = '../uploads/couleur-banquette-bois/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                require '../include/cleanFileName.php';
                $fileName = basename($img['name']);
                $fileName = cleanFileName($fileName);
                $uploadPath = $uploadDir . $fileName;
                if (!move_uploaded_file($img['tmp_name'], $uploadPath)) {
                    $_SESSION['message'] = 'Erreur lors de l\'upload de l\'image.';
                    $_SESSION['message_type'] = 'error';
                }
            }
        }
        if (!isset($_SESSION['message'])) {
            $stmt = $pdo->prepare("UPDATE couleur_bois SET nom = ?, prix = ?, img = ? WHERE id = ?");
            $stmt->execute([$nom, $price, $fileName, $id]);
            $_SESSION['message'] = 'La couleur du bois a été mise à jour avec succès !';
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
    <title>Modifie une couleur de bois</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/admin/ajout.css">
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
            <h2>Modifie une couleur de bois</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form action="edit.php?id=<?php echo $couleurbois['id']; ?>" method="POST" enctype="multipart/form-data" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nom <span class="required">*</span></label>
                            <input type="text" id="name" name="name" class="input-field" value="<?php echo htmlspecialchars($couleurbois['nom']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="price">Prix (en €) <span class="required">*</span></label>
                            <input type="number" id="price" name="price" class="input-field" value="<?php echo htmlspecialchars($couleurbois['prix']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="img">Image (Laissez vide pour conserver l'image actuelle) <span class="required">*</span></label>
                            <input type="file" id="img" name="img" class="input-field" accept="image/*" onchange="loadFile(event)" >
                            <img class="preview-img" src="../uploads/couleur-banquette-bois/<?php echo htmlspecialchars($couleurbois['img']); ?>" id="output" />
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