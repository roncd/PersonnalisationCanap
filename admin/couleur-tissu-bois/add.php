<?php
require '../config.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['name']);
    $price = ($_POST['price']);
    $img = $_FILES['img'];

    if (empty($nom) || empty($price) || empty($img['name'])) {
        $_SESSION['message'] = 'Tous les champs sont requis !';
        $_SESSION['message_type'] = 'error';
    }

    // Dossier d'upload
    $uploadDir = '../uploads/motif-banquette-bois/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Crée le dossier s'il n'existe pas
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    if (!in_array($img['type'], $allowedTypes)) {
        $_SESSION['message'] = 'Seuls les fichiers JPEG, PNG et GIF sont autorisés.';
        $_SESSION['message_type'] = 'error';
    }

    // Garder le nom original de l'image
    $fileName = basename($img['name']);
    $uploadPath = $uploadDir . $fileName;

    if (move_uploaded_file($img['tmp_name'], $uploadPath)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO couleur_tissu_bois (nom, img) VALUES (?, ?)");
            $stmt->execute([$nom, $fileName]);

            $_SESSION['message'] = 'Le motif du tissu a été ajouté avec succès !';
            $_SESSION['message_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['message'] = 'Erreur lors de l\'ajout du motif du tissu : ' . $e->getMessage();
            $_SESSION['message_type'] = 'error';
        }
    } else {
        $_SESSION['message'] = 'Erreur lors de l\'upload de l\'image.';
        $_SESSION['message_type'] = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajoute un motif de banquette - bois</title>
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
            <h2>Ajoute un motif de banquette - bois</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form class="formulaire-creation-compte" action="" method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nom</label>
                            <input type="text" id="name" name="name" class="input-field" required>
                        </div>
                        <div class="form-group">
                            <label for="price">Prix (en €)</label>
                            <input type="number" id="price" name="price" class="input-field" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="img">Image</label>
                            <input type="file" id="img" name="img" class="input-field" accept="image/*" onchange="loadFile(event)" required>
                            <img class="preview-img" id="output" />
                        </div>
                    </div>
                    <div class="button-section">
                        <div class="buttons">
                            <button type="button" class="btn-retour" onclick="history.go(-1)">Retour</button>
                            <input type="submit" class="btn-valider" value="Ajouter"></input>
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