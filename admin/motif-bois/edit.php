<?php
require '../config.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['message'] = 'ID du motif manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: index.php");
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
    $_SESSION['message'] = 'Motif introuvable.';
    $_SESSION['message_type'] = 'error';
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['name']);
    $prix = trim($_POST['prix']);
    $img = $_FILES['img'];
    $id_couleur = trim($_POST['id_couleur']);

    if (empty($nom)) {
        $_SESSION['message'] = 'Le nom est requis.';
        $_SESSION['message_type'] = 'error';
    } else {
        // Garder l'image actuelle si aucune nouvelle image n'est téléchargée
        $fileName = $motif['img'];
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
                $fileName = basename($img['name']);
                $uploadPath = $uploadDir . $fileName;
                if (!move_uploaded_file($img['tmp_name'], $uploadPath)) {
                    $_SESSION['message'] = 'Échec du téléchargement de l\'image.';
                    $_SESSION['message_type'] = 'error';
                }
            }
        }

        if (!isset($_SESSION['message'])) {
            $stmt = $pdo->prepare("UPDATE motif_bois SET nom = ?, prix = ?, img = ?, id_couleur_tissu = ? WHERE id = ?");
            $stmt->execute([$nom, $prix, $fileName, $id_couleur, $id]);
            $_SESSION['message'] = 'Motif mis à jour avec succès!';
            $_SESSION['message_type'] = 'success';
            header("Location: index.php");
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
    <title>Modifier le Motif des coussins bois </title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/ajout.css">
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link rel="stylesheet" href="../../styles/message.css">
</head>

<body>

    <header>
        <?php require '../squelette/header.php'; ?>
    </header>

    <main>
        <div class="container">
            <h2>Modifier le Motif de Tissu - bois</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form action="edit.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nom</label>
                            <input type="text" id="name" name="name" class="input-field" value="<?= htmlspecialchars($motif['nom']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="prix">Prix</label>
                            <input type="text" id="prix" name="prix" class="input-field" value="<?= htmlspecialchars($motif['prix']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="img">Image (Laissez vide pour conserver l'image actuelle)</label>
                            <input type="file" id="img" name="img" class="input-field" accept="image/*">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="id_couleur">Id couleur tissu bois</label>
                            <select class="input-field" id="id_couleur" name="id_couleur">
                                <option value="">-- Sélectionnez une couleur --</option>
                                <?php foreach ($couleurs as $couleur): ?>
                                    <option value="<?= htmlspecialchars($couleur['id']) ?>"
                                        <?= ($couleur['id'] == $motif['id_couleur_tissu']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($couleur['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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