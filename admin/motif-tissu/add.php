<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajoute un motif de coussin - tissu</title>
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
        <?php
        require '../config.php';
        session_start();

        if (!isset($_SESSION['id'])) {
            header("Location: ../index.php");
            exit();
        }

        $stmt = $pdo->prepare("SELECT id, nom FROM couleur_tissu");
        $stmt->execute();
        $couleurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['name']);
            $prix = trim($_POST['price']);
            $img = $_FILES['img'];
            $id_couleur = ($_POST['id_couleur']);

            if (empty($nom) || empty($prix) || empty($img['name']) || empty($id_couleur)) {
                $_SESSION['message'] = 'Tous les champs sont requis !';
                $_SESSION['message_type'] = 'error';
            } else {
                $uploadDir = '../uploads/motif-tissu/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
                if (!in_array($img['type'], $allowedTypes)) {
                    $_SESSION['message'] = 'Seuls les fichiers JPEG, PNG et GIF sont autorisés.';
                    $_SESSION['message_type'] = 'error';
                } else {
                    $fileName = basename($img['name']);
                    $uploadPath = $uploadDir . $fileName;

                    if (move_uploaded_file($img['tmp_name'], $uploadPath)) {
                        try {
                            $stmt = $pdo->prepare("INSERT INTO motif_tissu (nom, prix, img, id_couleur_tissu) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$nom, $prix, $fileName, $id_couleur]);

                            $_SESSION['message'] = 'Le motif du coussin a été ajouté avec succès !';
                            $_SESSION['message_type'] = 'success';
                        } catch (Exception $e) {
                            $_SESSION['message'] = 'Erreur lors de l\'ajout du motif : ' . $e->getMessage();
                            $_SESSION['message_type'] = 'error';
                        }
                    } else {
                        $_SESSION['message'] = 'Erreur lors de l\'upload de l\'image.';
                        $_SESSION['message_type'] = 'error';
                    }
                }
            }
        }
        ?>

        <div class="container">
            <h2>Ajouter un motif de coussin - tissu</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form method="POST" enctype="multipart/form-data" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nom</label>
                            <input type="text" id="name" name="name" class="input-field" required>
                        </div>
                        <div class="form-group">
                            <label for="price">Prix (en €)</label>
                            <input type="number" id="price" name="price" class="input-field" step="0.01" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="img">Image</label>
                            <input type="file" id="img" name="img" class="input-field" accept="image/*" onchange="loadFile(event)" required>
                            <img class="preview-img" id="output" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="id_couleur">Couleur du tissu associé</label>
                            <select class="input-field" id="id_couleur" name="id_couleur">
                                <option value="">-- Sélectionnez une couleur --</option>
                                <?php foreach ($couleurs as $couleur): ?>
                                    <option value="<?= htmlspecialchars($couleur['id']) ?>">
                                        <?= htmlspecialchars($couleur['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="button-section">
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