<?php
require '../config.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['message'] = 'ID de la mousse manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}

// Récupérer les données actuelles de la mousse
$stmt = $pdo->prepare("SELECT * FROM mousse WHERE  id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$mousse = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mousse) {
    $_SESSION['message'] = 'Mousse introuvable.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['name']);
    $prix = trim($_POST['prix']);
    $img = $_FILES['img'];

    if (empty($nom)) {
        $_SESSION['message'] = 'Le nom est requis.';
        $_SESSION['message_type'] = 'error';
    } else {
        // Garder l'image actuelle si aucune nouvelle image n'est téléchargée
        $fileName = $mousse['img'];
        if (!empty($img['name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
            if (!in_array($img['type'], $allowedTypes)) {
                $_SESSION['message'] = 'Seuls les fichiers JPEG, PNG et GIF sont autorisés.';
                $_SESSION['message_type'] = 'error';
            } else {
                $uploadDir = '../uploads/mousse/'; // Ajuster le dossier pour type_mousse
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fileName = basename($img['name']);
                $uploadPath = $uploadDir . $fileName;
                if (!move_uploaded_file($img['tmp_name'], $uploadPath)) {
                    $_SESSION['message'] = 'Erreur de téléchargement de l\'image.';
                    $_SESSION['message_type'] = 'error';
                }
            }
        }

        if (!isset($_SESSION['message'])) {
            // Mettre à jour les informations dans la base de données
            $stmt = $pdo->prepare("UPDATE mousse SET nom = ?, prix = ?, img = ? WHERE id = ?");
            $stmt->execute([$nom, $prix, $fileName, $id]);

            $_SESSION['message'] = 'Mousse mise à jour avec succès.';
            $_SESSION['message_type'] = 'success';
            header("Location: visualiser.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un type de mousse</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/admin/ajout.css">
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link rel="stylesheet" href="../../styles/message.css">
    <script src="../../script/previewImage.js"></script>
</head>

<body>

    <header>
        <?php require '../squelette/header.php'; ?>
    </header>

    <main>
        <div class="container">
            <h2>Modifier le type de mousse</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form action="edit.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nom</label>
                            <input type="text" id="name" name="name" class="input-field" value="<?= htmlspecialchars($mousse['nom']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="prix">Prix</label>
                            <input type="text" id="prix" name="prix" class="input-field" value="<?= htmlspecialchars($mousse['prix']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="img">Image (Laissez vide pour conserver l'image actuelle)</label>
                            <input type="file" id="img" name="img" class="input-field" accept="image/*" onchange="loadFile(event)" >
                            <img class="preview-img" src="../uploads/mousse/<?php echo htmlspecialchars($mousse['img']); ?>" id="output" />
                         </div>
                    </div>
                    <div class="button-section">
                        <div class="buttons">
                            <button type="button" class="btn-retour" onclick="history.go(-1)">Retour</button>
                            <input type="submit" class="btn-valider" value="Mettre à jour">
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