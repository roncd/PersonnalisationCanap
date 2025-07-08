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
    $_SESSION['message'] = 'ID de la structure manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: edit.php?id=$id");
    exit();
}

// Récupérer les données actuelles de la structure
$stmt = $pdo->prepare("SELECT * FROM structure WHERE  id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$structure = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$structure) {
    $_SESSION['message'] = 'Structure introuvable.';
    $_SESSION['message_type'] = 'error';
    header("Location: edit.php?id=$id");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['name']);
    $price = ($_POST['price']);
    $nb_banquette = trim($_POST['nb_banquette']);
    $img = $_FILES['img'];

    if (empty($nom) || empty($nb_banquette)) {
        $_SESSION['message'] = 'Le nom est requis.';
        $_SESSION['message_type'] = 'error';
    } else {
        // Garder l'image actuelle si aucune nouvelle image n'est téléchargée
        $fileName = $structure['img'];
        if (!empty($img['name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
            if (!in_array($img['type'], $allowedTypes)) {
                $_SESSION['message'] = 'Seuls les fichiers JPEG, PNG et GIF sont autorisés.';
                $_SESSION['message_type'] = 'error';
            } else {
                $uploadDir = '../uploads/structure/';
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
            $stmt = $pdo->prepare("UPDATE structure SET nom = ?, img = ?, prix = ?, nb_longueurs = ? WHERE id = ?");
            $stmt->execute([$nom, $fileName, $price, $nb_banquette, $id]);
            $_SESSION['message'] = 'La structure a été mise à jour avec succès !';
            $_SESSION['message_type'] = 'success';
            header("Location: visualiser.php");
            exit();
        }
    }
}
$selected = (int) $structure['nb_longueurs'];
$valeurs = [1, 2, 3];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une structure</title>
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
            <h2>Modifier une structure</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form action="edit.php?id=<?php echo $structure['id']; ?>" method="POST" enctype="multipart/form-data" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nom</label>
                            <input type="text" id="name" name="name" class="input-field" value="<?php echo htmlspecialchars($structure['nom']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="price">Prix (en €) <span class="required">*</span></label>
                            <input type="number" id="price" name="price" class="input-field" value="<?php echo htmlspecialchars($structure['prix']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nb_banquette">Nombre de banquettes <span class="required">*</span></label>
                            <select id="nb_banquette" name="nb_banquette" class="input-field" required>
                                <option value="<?php echo $selected; ?>"><?php echo $selected; ?></option>
                                <?php foreach ($valeurs as $valeur): ?>
                                    <?php if ($valeur != $selected): ?>
                                        <option value="<?php echo $valeur; ?>"><?php echo $valeur; ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="img">Image (Laissez vide pour conserver l'image actuelle)</label>
                            <input type="file" id="img" name="img" class="input-field" accept="image/*" onchange="loadFile(event)">
                            <img class="preview-img" src="../uploads/structure/<?php echo htmlspecialchars($structure['img']); ?>" id="output" />
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