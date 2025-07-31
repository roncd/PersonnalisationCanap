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
$stmt = $pdo->prepare("SELECT * FROM stats_accueil WHERE id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$stats) {
    $_SESSION['message'] = 'Statistique introuvable.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser-site.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $label = trim($_POST['label']);
    $valeur = trim($_POST['valeur']);
    $plus = trim($_POST['plus']);
    $decimal = trim($_POST['decimal']);
    $note = trim($_POST['note']);
    $pourcent = trim($_POST['pourcent']);
    $visible = isset($_POST['visible']) ? 1 : 0;


    if (empty($label) || !isset($valeur) || !isset($plus) || !isset($decimal)|| !isset($note)|| !isset($pourcent)) {
        $_SESSION['message'] = 'Veillez remplir les champs requis.';
        $_SESSION['message_type'] = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE stats_accueil SET label = ?, valeur = ?, plus = ?, `decimal` = ?, notation = ?, pourcentage = ?,  visible = ? WHERE id = ?");
            $stmt->execute([$label, $valeur, $plus, $decimal, $note, $pourcent, $visible, $id]);

            $_SESSION['message'] = 'Statistique mise à jour avec succès.';
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
    <title>Modifie une statistique - Accueil</title>
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
            <h2>Modifie une statistique - Accueil</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form class="formulaire-creation-compte" enctype="multipart/form-data" action="" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="label">Label <span class="required">*</span></label>
                            <input type="text" id="label" name="label" class="input-field" value="<?= htmlspecialchars($stats['label']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="valeur">Valeur <span class="required">*</span></label>
                            <input type="number" id="valeur" name="valeur" step="0.1" class="input-field" value="<?= htmlspecialchars($stats['valeur']) ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="plus">Afficher un "+" <span class="required">*</span></label>
                            <select id="plus" name="plus" class="input-field" required>
                                <?php $options = ["0" => "Non", "1" => "Oui"]; ?>
                                <option value="<?php echo htmlspecialchars($stats['plus']); ?>"><?php echo htmlspecialchars($options[$stats['plus']]); ?></option>
                                <?php
                                // Boucle pour afficher uniquement les options différentes
                                foreach ($options as $value => $label) {
                                    if ($value !== $stats['plus']) {
                                        echo "<option value=\"$value\">$label</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="pourcent">Afficher un "%" <span class="required">*</span> </label>
                            <select id="pourcent" name="pourcent" class="input-field" required>
                                <?php $options = ["0" => "Non", "1" => "Oui"]; ?>
                                <option value="<?php echo htmlspecialchars($stats['pourcentage']); ?>"><?php echo htmlspecialchars($options[$stats['pourcentage']]); ?></option>
                                <?php
                                // Boucle pour afficher uniquement les options différentes
                                foreach ($options as $value => $label) {
                                    if ($value !== $stats['pourcentage']) {
                                        echo "<option value=\"$value\">$label</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="note">Afficher une note /5 <span class="required">*</span> </label>
                            <select id="note" name="note" class="input-field" required>
                                <?php $options = ["0" => "Non", "1" => "Oui"]; ?>
                                <option value="<?php echo htmlspecialchars($stats['notation']); ?>"><?php echo htmlspecialchars($options[$stats['notation']]); ?></option>
                                <?php
                                // Boucle pour afficher uniquement les options différentes
                                foreach ($options as $value => $label) {
                                    if ($value !== $stats['notation']) {
                                        echo "<option value=\"$value\">$label</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="decimal">Afficher les decimals <span class="required">*</span> </label>
                            <select id="decimal" name="decimal" class="input-field" required>
                                <?php $options = ["0" => "Non", "1" => "Oui"]; ?>
                                <option value="<?php echo htmlspecialchars($stats['decimal']); ?>"><?php echo htmlspecialchars($options[$stats['decimal']]); ?></option>
                                <?php
                                // Boucle pour afficher uniquement les options différentes
                                foreach ($options as $value => $label) {
                                    if ($value !== $stats['decimal']) {
                                        echo "<option value=\"$value\">$label</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group btn-slider">
                            <label for="visible">Afficher sur le site</label>
                            <label class="switch">
                                <input type="checkbox" id="visible" name="visible" <?php if ($stats['visible']) echo 'checked'; ?>>
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