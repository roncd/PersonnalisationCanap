<?php
require '../config.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['message'] = 'ID de l\'accoudoir en bois manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: index.php");
    exit();
}

// Récupérer les données actuelles de l'accoudoir en bois
$stmt = $pdo->prepare("SELECT * FROM accoudoir_bois WHERE id = ?");
$stmt->execute([$id]);
$accoudoirbois = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$accoudoirbois) {
    $_SESSION['message'] = 'Accoudoir en bois introuvable.';
    $_SESSION['message_type'] = 'error';
    header("Location: index.php");
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
        $fileName = $accoudoirbois['img'];
        if (!empty($img['name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
            if (!in_array($img['type'], $allowedTypes)) {
                $_SESSION['message'] = 'Seuls les fichiers JPEG, PNG et GIF sont autorisés.';
                $_SESSION['message_type'] = 'error';
            } else {
                $uploadDir = '../uploads/accoudoirs-bois/'; // Dossier pour les motifs de tissu
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fileName = basename($img['name']);
                $uploadPath = $uploadDir . $fileName;
                if (!move_uploaded_file($img['tmp_name'], $uploadPath)) {
                    $_SESSION['message'] = 'Échec du téléchargement de l\'image.';
                    $_SESSION['message_type'] = 'error';
                }
            }
        }
        if (!isset($_SESSION['message'])) {
            $stmt = $pdo->prepare("UPDATE accoudoir_bois SET nom = ?, prix = ?, img = ? WHERE id = ?");
            $stmt->execute([$nom, $price, $fileName, $id]);
            $_SESSION['message'] = 'L\'accoudoir en bois a été mise à jour avec succès !';
            $_SESSION['message_type'] = 'success';
            header("Location: index.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifie un accoudoir bois</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/ajout.css">
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
            <h2>Modifie un accoudoir bois</h2>
            <?php
            if (isset($_SESSION['message'])) {
                echo '<div class="message ' . htmlspecialchars($_SESSION['message_type']) . '">';
                echo htmlspecialchars($_SESSION['message']);
                echo '</div>';
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            }
            ?>
            <div class="form">
                <form action="edit.php?id=<?php echo $accoudoirbois['id']; ?>" method="POST" enctype="multipart/form-data" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nom</label>
                            <input type="text" id="name" name="name" class="input-field" value="<?php echo htmlspecialchars($accoudoirbois['nom']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Prix (en €)</label>
                            <input type="number" id="price" name="price" class="input-field" value="<?php echo htmlspecialchars($accoudoirbois['prix']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="img">Image (Laissez vide pour conserver l'image actuelle)</label>
                            <input type="file" id="img" name="img" class="input-field" accept="image/*">
                        </div>
                    </div>
                    <div class="footer">
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