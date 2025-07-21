<?php
require '../config.php';
session_start();
require '../include/session_expiration.php';


if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $icon = trim($_POST['icon']);
    $visible = isset($_POST['visible']) ? 1 : 0;


    if (empty($nom) || empty($icon)) {
        $_SESSION['message'] = 'Le nom et l’icône sont requis.';
        $_SESSION['message_type'] = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO faq_categorie (nom, icon, visible) VALUES (?, ?, ?)");
            $stmt->execute([$nom, $icon, $visible]);
            $_SESSION['message'] = 'Catégorie ajoutée avec succès.';
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
    <title>FAQ - Catégorie</title>
    <link rel="icon" type="image/png" href="https://www.decorient.fr/medias/favicon.png">
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
            <h2>Ajoute une catégorie à la FAQ</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form class="formulaire-creation-compte" action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nom">Nom de la catégorie<span class="required">*</span></label>
                        <input type="text" id="nom" name="nom" class="input-field" required>
                    </div>
                    <div class="form-group">
                        <label for="icon">Icône Font Awesome <label for="icon">
                                Icône Font Awesome
                                (ex: fa-solid fa-truck |
                                <a href="https://fontawesome.com/search" target="_blank" style="color: #a4745a; text-decoration: underline;">
                                    lien vers la bibliothèque
                                </a>)
                            </label><span class="required">*</span></label>
                        <input type="text" id="icon" name="icon" class="input-field" placeholder="fa-truck" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group btn-slider">
                            <label for="visible">Afficher sur le site</label>
                            <label class="switch">
                                <input type="checkbox" id="visible" name="visible" checked>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="button-section">
                        <div class="buttons">
                            <button type="button" id="btn-retour" class="btn-beige" onclick="history.go(-1)">Retour</button>
                            <input type="submit" class="btn-noir" value="Ajouter"></input>
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