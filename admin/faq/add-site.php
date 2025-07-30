<?php
require '../config.php';
session_start();
require '../include/session_expiration.php';


if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}

$stmt = $pdo->prepare("SELECT id, nom FROM faq_categorie");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question']);
    $reponse = trim($_POST['reponse']);
    $categorie_id = ($_POST['categorie_id']);
    $visible = isset($_POST['visible']) ? 1 : 0;


    // Validation des champs requis
    if (empty($question) || empty($reponse) || empty($categorie_id)) {
        $_SESSION['message'] = 'Tous les champs sont requis !';
        $_SESSION['message_type'] = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO faq (question, reponse, categorie_id, visible) VALUES (?, ?, ?, ?)");
            $stmt->execute([$question, $reponse, $categorie_id, $visible]);

            $_SESSION['message'] = 'La question a été ajoutée avec succès !';
            $_SESSION['message_type'] = 'success';
            header("Location: visualiser-site.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['message'] = 'Erreur lors de l\'ajout de la question : ' . $e->getMessage();
            $_SESSION['message_type'] = 'error';
        }
    }
    header("Location: add-site.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajoute à la FAQ</title>
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
            <h2>Ajoute à la FAQ</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form class="formulaire-creation-compte" action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="question">Question<span class="required">*</span></label>
                        <input type="text" id="question" name="question" class="input-field" required>
                    </div>
                    <div class="form-group">
                        <label for="reponse">Réponse<span class="required">*</span></label>
                        <input type="text" id="reponse" name="reponse" class="input-field" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="categorie_id">Catégorie <span class="required">*</span></label>
                            <select class="input-field" id="categorie_id" name="categorie_id">
                                <option value="">-- Sélectionnez une catégorie --</option>
                                <?php foreach ($categories as $categorie): ?>
                                    <option value="<?= htmlspecialchars($categorie['id']) ?>">
                                        <?= htmlspecialchars($categorie['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group btn-slider">
                            <label for="visible">Afficher sur le site</label>
                            <label class="switch">
                                <input type="checkbox" id="visible" name="visible" checked >
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