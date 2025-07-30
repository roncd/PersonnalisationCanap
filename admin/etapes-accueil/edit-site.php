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
    $_SESSION['message'] = 'ID de la section manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser-site.php");
    exit();
}

// Récupération de la section à modifier
$stmt = $pdo->prepare("SELECT * FROM etapes_accueil WHERE id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$etapes = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$etapes) {
    $_SESSION['message'] = 'Section introuvable.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser-site.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = trim($_POST['numero']);
    $titre = trim($_POST['titre']);
    $description = $_POST['description'];
    $icon = trim($_POST['icon']);
    $visible = isset($_POST['visible']) ? 1 : 0;


    if (empty($numero) || empty($titre) || empty($description) || empty($icon) ) {
        $_SESSION['message'] = 'Le nom et l’icône sont requis.';
        $_SESSION['message_type'] = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE etapes_accueil SET numero_etape = ?, titre = ?, description = ?, icon = ?, visible = ? WHERE id = ?");
            $stmt->execute([$numero, $titre, $description, $icon, $visible, $id]);

            $_SESSION['message'] = 'Étape mise à jour avec succès.';
            $_SESSION['message_type'] = 'success';
            header("Location: visualiser-site.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['message'] = 'Erreur : ' . $e->getMessage();
            $_SESSION['message_type'] = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifie une étape - Accueil</title>
    <link rel="icon" type="image/png" href="https://www.decorient.fr/medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/admin/form.css">
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
            <h2>Modifie une étape - Accueil</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form class="formulaire-creation-compte" enctype="multipart/form-data" action="" method="POST">
                      <div class="form-row">
                        <div class="form-group">
                            <label for="numero">Numéro étape <span class="required">*</span></label>
                            <input type="number" id="numero" name="numero" class="input-field"  min="1" max="4" value="<?= htmlspecialchars($etapes['numero_etape']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="titre">Titre <span class="required">*</span></label>
                            <input type="text" id="titre" name="titre" class="input-field" value="<?= htmlspecialchars($etapes['titre']) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="description">Description <span class="required">*</span></label>
                            <textarea id="description" name="description" class="input-field" required><?= htmlspecialchars($etapes['description']) ?></textarea>
                        </div>
                    </div>
                   <div class="form-group">
                        <label for="icon">Icône <span class="required">*</span> (ex: fa-solid fa-truck |
                            <a href="https://fontawesome.com/search" target="_blank" class="icon-link">
                                lien vers la bibliothèque
                            </a>)
                            </label>
                        <input type="text" id="icon" name="icon" class="input-field" placeholder="fa-truck" value="<?= htmlspecialchars($etapes['icon']) ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group btn-slider">
                            <label for="visible">Afficher sur le site</label>
                            <label class="switch">
                                <input type="checkbox" id="visible" name="visible" <?php if ($etapes['visible']) echo 'checked'; ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="button-section">
                        <div class="buttons">
                            <button type="button" id="btn-retour" class="btn-beige" onclick="history.go(-1)">Retour</button>
                            <input type="submit" class="btn-noir" value="Modifier">
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