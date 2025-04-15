<?php
require '../config.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['name']);
    $img = $_FILES['img'];

    if (empty($nom) || empty($img['name'])) {
        $_SESSION['message'] = 'Tous les champs sont requis !';
        $_SESSION['message_type'] = 'error';
    }

    // Dossier d'upload
    $uploadDir = '../uploads/structure/';
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
            $stmt = $pdo->prepare("INSERT INTO structure (nom, img) VALUES (?, ?)");
            $stmt->execute([$nom, $fileName]);

            $_SESSION['message'] = 'La structure a été ajoutée avec succès !';
            $_SESSION['message_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['message'] = 'Erreur lors de l\'ajout de la structure: ' . $e->getMessage();
            $_SESSION['message_type'] = 'error';
        }
    } else {
        $_SESSION['message'] = 'Erreur lors de l\'upload de l\'image.';
        $_SESSION['message_type'] = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajoute une structure</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/ajout.css">
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <style>
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <header>
        <?php require '../squelette/header.php'; ?>
    </header>

    <main>
        <div class="container">
            <h2>Ajouter une structure</h2>

            <?php
            if (isset($_SESSION['message'])) {
                echo '<div class="message ' . htmlspecialchars($_SESSION['message_type']) . '">';
                echo htmlspecialchars($_SESSION['message']);
                echo '</div>';
                // Unset the message after displaying it
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            }
            ?>

            <div class="form">
                <form action="" method="POST" enctype="multipart/form-data" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nom</label>
                            <input type="text" id="name" name="name" class="input-field" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="img">Image</label>
                            <input type="file" id="img" name="img" class="input-field" accept="image/*" required>
                        </div>
                    </div>
                    <div class="footer">
                        <div class="buttons">
                            <button type="button" class="btn-retour" onclick="history.go(-1)">Retour</button>
                            <input type="submit" class="btn-valider" value="Ajouter">
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