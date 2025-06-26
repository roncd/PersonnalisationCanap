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
    $_SESSION['message'] = 'ID de la question FAQ manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}

// Récupération de la liste des catégories pour le <select>
$stmt = $pdo->prepare("SELECT id, nom FROM faq_categorie");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération de la question FAQ à modifier
$stmt = $pdo->prepare("SELECT * FROM faq WHERE id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$faq = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$faq) {
    $_SESSION['message'] = 'Question FAQ introuvable.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question']);
    $reponse = trim($_POST['reponse']);
    $categorie_id = $_POST['categorie_id'];

    if (empty($question) || empty($reponse) || empty($categorie_id)) {
        $_SESSION['message'] = 'Tous les champs sont requis.';
        $_SESSION['message_type'] = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE faq SET question = ?, reponse = ?, categorie_id = ? WHERE id = ?");
            $stmt->execute([$question, $reponse, $categorie_id, $id]);
            $_SESSION['message'] = 'Question mise à jour avec succès.';
            $_SESSION['message_type'] = 'success';
            header("Location: visualiser.php");
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
    <title>Modifie un motif de tissu - bois</title>
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
            <h2>Modifie un motif de tissu - bois</h2>
            <?php require '../include/message.php'; ?>
 <div class="form">
    <form class="formulaire-creation-compte" action="" method="POST">
        <div class="form-group">
            <label for="question">Question<span class="required">*</span></label>
            <input type="text" id="question" name="question" class="input-field" 
                   value="<?= htmlspecialchars($faq['question']) ?>" required>
        </div>
        <div class="form-group">
            <label for="reponse">Réponse<span class="required">*</span></label>
            <input type="text" id="reponse" name="reponse" class="input-field" 
                   value="<?= htmlspecialchars($faq['reponse']) ?>" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="categorie_id">Catégorie <span class="required">*</span></label>
                <select class="input-field" id="categorie_id" name="categorie_id" required>
                    <option value="">-- Sélectionnez une catégorie --</option>
                    <?php foreach ($categories as $categorie): ?>
                        <option value="<?= htmlspecialchars($categorie['id']) ?>"
                            <?= ($categorie['id'] == $faq['categorie_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($categorie['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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