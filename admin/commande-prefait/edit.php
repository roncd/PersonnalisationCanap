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
    $_SESSION['message'] = 'ID de la commande manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}

// Récupérer les données actuelles de la commande
$stmt = $pdo->prepare("SELECT * FROM commande_prefait WHERE  id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$commande) {
    $_SESSION['message'] = 'Commande introuvable.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}


// Récupérer les accoudoirs déjà liés à cette commande
$stmtAcc = $pdo->prepare("SELECT id_accoudoir_bois FROM commande_prefait_accoudoir WHERE id_commande_prefait = ?");
$stmtAcc->execute([$commande['id']]);
$accoudoirsExistants = $stmtAcc->fetchAll(PDO::FETCH_COLUMN);

$accoudoir1 = $accoudoirsExistants[0] ?? null;
$accoudoir2 = $accoudoirsExistants[1] ?? null;



function fetchData($pdo, $table, $columns = ['id', 'nom'])
{
    $cols = implode(', ', $columns);
    $stmt = $pdo->prepare("SELECT $cols FROM $table");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Chargement des données
$banquettes = fetchData($pdo, 'type_banquette');
$mousses = fetchData($pdo, 'mousse');
$couleursbois = fetchData($pdo, 'couleur_bois');
$accoudoirsbois = fetchData($pdo, 'accoudoir_bois');
$dossiersbois = fetchData($pdo, 'dossier_bois');
$couleurstissubois = fetchData($pdo, 'couleur_tissu_bois');
$motifsbois = fetchData($pdo, 'motif_bois');
$modeles = fetchData($pdo, 'modele');
$couleurstissu = fetchData($pdo, 'couleur_tissu');
$motifstissu = fetchData($pdo, 'motif_tissu');
$accoudoirstissu = fetchData($pdo, 'accoudoir_tissu');
$dossierstissu = fetchData($pdo, 'dossier_tissu');
$decorations = fetchData($pdo, 'decoration');

$stmt = $pdo->prepare("SELECT id, nom, nb_longueurs FROM structure");
$stmt->execute();

$structures = fetchData($pdo, 'structure', ['id', 'nom', 'nb_longueurs']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $longueurA = trim($_POST['longueurA']);
    $longueurB = trim($_POST['longueurB']) ?: null;
    $longueurC = trim($_POST['longueurC']) ?: null;
    $idStructure = trim($_POST['structure']);
    $idBanquette = trim($_POST['banquette']);
    $idMousse = trim($_POST['mousse']);
    $idCouleurBois = trim($_POST['couleurbois']) ?: null;
    $idDecoration = trim($_POST['decoration']) ?: null;
    $idDossierBois = trim($_POST['dossierbois']) ?: null;
    $idTissuBois = trim($_POST['couleurtissubois']) ?: null;
    $idMotifBois = trim($_POST['motifbois']) ?: null;
    $idModele = trim($_POST['modele']) ?: null;
    $idCouleurTissu = trim($_POST['couleurtissu']) ?: null;
    $idMotifTissu = trim($_POST['motiftissu']) ?: null;
    $idAccoudoirTissu = trim($_POST['accoudoirtissu']) ?: null;
    $idDossierTissu = trim($_POST['dossiertissu']) ?: null;
    $nbAccoudoir = trim($_POST['nb_accoudoir']) ?: null;
    $nbAccoudoirBois = $_POST['nb_accoudoir_bois'] !== '' ? intval($_POST['nb_accoudoir_bois']) : null;
    $nom = trim($_POST['nom']);
    $imagePath = isset($commande['img']) ? $commande['img'] : null;
    $visible = isset($_POST['visible']) ? 1 : 0;


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_FILES['img']) && $_FILES['img']['error'] === 0) {
            $uploadDir = '../uploads/canape-prefait/';

            // Vérifier si le dossier d'upload existe, sinon le créer
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $tmpName = $_FILES['img']['tmp_name'];
            $originalName = basename($_FILES['img']['name']);
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($extension, $allowedExtensions)) {
                // Générer un nom unique pour éviter les conflits
                $baseName = pathinfo($originalName, PATHINFO_FILENAME);
                $imageName = $baseName . '_' . time() . '.' . $extension;
                $destination = $uploadDir . $imageName;

                if (move_uploaded_file($tmpName, $destination)) {
                    // Optionnel : Supprimer l'ancienne image si elle existe
                    if (!empty($commande['img']) && file_exists($uploadDir . $commande['img'])) {
                        unlink($uploadDir . $commande['img']);
                    }

                    $imagePath = $imageName; // Mettre à jour l'image
                } else {
                    $_SESSION['message'] = "Erreur lors du téléchargement de l'image.";
                    $_SESSION['message_type'] = "error";
                }
            } else {
                $_SESSION['message'] = "Format de fichier non autorisé.";
                $_SESSION['message_type'] = "error";
            }
        }

        // Si aucun fichier n'est uploadé, conserver l'image actuelle
        if (empty($imagePath)) {
            $imagePath = isset($commande['img']) ? $commande['img'] : null;
        }

        // Mettre à jour l'image dans la base de données
        $stmt = $pdo->prepare("UPDATE commande_prefait SET img = ? WHERE id = ?");
        $stmt->execute([$imagePath, $commande['id']]);

        // Supprimer les anciens accoudoirs
        $pdo->prepare("DELETE FROM commande_prefait_accoudoir WHERE id_commande_prefait = ?")->execute([$commande['id']]);

        // Réinsérer les nouveaux accoudoirs
        $acc1 = $_POST['accoudoir1'] ?? null;
        $acc2 = $_POST['accoudoir2'] ?? null;

        if (!empty($acc1)) {
            $stmtAcc1 = $pdo->prepare("INSERT INTO commande_prefait_accoudoir (id_commande_prefait, id_accoudoir_bois, nb_accoudoir) VALUES (?, ?, 1)");
            $stmtAcc1->execute([$commande['id'], intval($acc1)]);
        }

        if (!empty($acc2)) {
            $stmtAcc2 = $pdo->prepare("INSERT INTO commande_prefait_accoudoir (id_commande_prefait, id_accoudoir_bois, nb_accoudoir) VALUES (?, ?, 1)");
            $stmtAcc2->execute([$commande['id'], intval($acc2)]);
        }
    }


    $stmt = $pdo->prepare("UPDATE commande_prefait SET 
    id_structure = ?, 
    longueurA = ?, 
    longueurB = ?, 
    longueurC = ?, 
    id_banquette = ?, 
    id_mousse = ?, 
    id_couleur_bois = ?, 
    id_decoration = ?, 
    id_dossier_bois = ?, 
    id_couleur_tissu_bois = ?, 
    id_motif_bois = ?, 
    id_modele = ?, 
    id_couleur_tissu = ?,  
    id_motif_tissu = ?, 
    id_dossier_tissu = ?, 
    id_accoudoir_tissu = ?, 
    id_nb_accoudoir = ?, 
    nb_accoudoir_bois = ?,
    nom = ?, 
    img = ?,
    visible = ?
    WHERE id = ?");

    if ($stmt->execute([
        $idStructure,
        $longueurA,
        $longueurB,
        $longueurC,
        $idBanquette,
        $idMousse,
        $idCouleurBois,
        $idDecoration,
        $idDossierBois,
        $idTissuBois,
        $idMotifBois,
        $idModele,
        $idCouleurTissu,
        $idMotifTissu,
        $idDossierTissu,
        $idAccoudoirTissu,
        $nbAccoudoir,
        $nbAccoudoirBois,
        $nom,
        $imagePath,
        $visible,
        $id
    ])) {
        $_SESSION['message'] = 'La commande a été mise à jour avec succès !';
        $_SESSION['message_type'] = 'success';
        header("Location: visualiser.php");
        exit();
    } else {
        $_SESSION['message'] = 'Erreur lors de la mise à jour de la commande.';
        $_SESSION['message_type'] = 'error';
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un canapé pré-fait</title>
    <link rel="icon" type="image/png" href="https://www.decorient.fr/medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/admin/form.css">
    <link rel="stylesheet" href="../../styles/message.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <script src="../../script/previewImage.js"></script>
    <script src="../../script/displayInput.js"></script>
</head>

<body>

    <header>
        <?php require '../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
            <h2>Modifie une commande pré-faite</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form action="edit.php?id=<?php echo $commande['id']; ?>" method="POST" enctype="multipart/form-data" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom de la commande <span class="required">*</span></label>
                            <input type="text" id="nom" name="nom" class="input-field" value="<?= htmlspecialchars($commande['nom']) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="img">Image (Laissez vide pour conserver l'image actuelle) <span class="required">*</span></label>
                            <input type="file" id="img" name="img" class="input-field" accept="image/*" onchange="loadFile(event)">
                            <img class="preview-img" src="../uploads/canape-prefait/<?php echo htmlspecialchars($commande['img']); ?>" id="output" />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="structure">Référence Structure <span class="required">*</span></label>
                            <select id="structure" name="structure" class="input-field" required>
                                <option value="">-- Sélectionnez une option --</option>
                                <?php foreach ($structures as $s): ?>
                                    <option value="<?= htmlspecialchars($s['id']) ?>"
                                        <?= ($s['id'] == $commande['id_structure']) ? 'selected' : '' ?>
                                        data-nb-longueurs="<?= ($s['nb_longueurs']) ?>">
                                        <?= htmlspecialchars($s['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="longueurA">Longueur A <span class="required">*</span></label>
                            <input type="number" id="longueurA" name="longueurA" class="input-field" required value="<?= htmlspecialchars($commande['longueurA']) ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="longueurB">Longueur B <span class="required">*</span></label>
                            <input type="number" id="longueurB" name="longueurB" class="input-field" value="<?= htmlspecialchars($commande['longueurB']) ?>">
                        </div>

                        <div class="form-group">
                            <label for="longueurC">Longueur C <span class="required">*</span></label>
                            <input type="number" id="longueurC" name="longueurC" class="input-field" value="<?= htmlspecialchars($commande['longueurC']) ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mousse">Référence Mousse <span class="required">*</span></label>
                            <select id="mousse" name="mousse" class="input-field" required>
                                <option value="">-- Sélectionnez une option --</option>
                                <?php foreach ($mousses as $item): ?>
                                    <option value="<?= htmlspecialchars($item['id']) ?>"
                                        <?= (isset($commande['id_mousse']) && (string)$commande['id_mousse'] === (string)$item['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($item['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="banquette">Référence Type de banquette <span class="required">*</span></label>
                            <select id="banquette" name="banquette" class="input-field" required>
                                <option value="">-- Sélectionnez une option --</option>
                                <?php foreach ($banquettes as $b): ?>
                                    <option value="<?= htmlspecialchars($b['id']) ?>"
                                        <?= (isset($commande['id_banquette']) && (string)$commande['id_banquette'] === (string)$b['id']) ? 'selected' : '' ?>
                                        data-type="<?= htmlspecialchars($b['nom']) ?>">
                                        <?= htmlspecialchars($b['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="bois">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="couleurbois">Référence Couleur banquette - bois <span class="required">*</span></label>
                                <select id="couleurbois" name="couleurbois" class="input-field">
                                    <option value="">-- Sélectionnez une option --</option>
                                    <?php foreach ($couleursbois as $item): ?>
                                        <option value="<?= htmlspecialchars($item['id']) ?>"
                                            <?= (isset($commande['id_couleur_bois']) && (string)$commande['id_couleur_bois'] === (string)$item['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($item['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="decoration">Référence Décoration - bois <span class="required">*</span></label>
                                <select id="decoration" name="decoration" class="input-field">
                                    <option value="">-- Sélectionnez une option --</option>
                                    <?php foreach ($decorations as $item): ?>
                                        <option value="<?= htmlspecialchars($item['id']) ?>"
                                            <?= (isset($commande['id_decoration']) && (string)$commande['id_decoration'] === (string)$item['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($item['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>


                        <div class="form-row">
                            <div class="form-group">
                                <label for="dossierbois">Référence Dossier - bois <span class="required">*</span></label>
                                <select id="dossierbois" name="dossierbois" class="input-field">
                                    <option value="">-- Sélectionnez une option --</option>
                                    <?php foreach ($dossiersbois as $item): ?>
                                        <option value="<?= htmlspecialchars($item['id']) ?>"
                                            <?= (isset($commande['id_dossier_bois']) && (string)$commande['id_dossier_bois'] === (string)$item['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($item['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="couleurtissubois">Référence Motif tissu - bois <span class="required">*</span></label>
                                <select id="couleurtissubois" name="couleurtissubois" class="input-field">
                                    <option value="">-- Sélectionnez une option --</option>
                                    <?php foreach ($couleurstissubois as $item): ?>
                                        <option value="<?= htmlspecialchars($item['id']) ?>"
                                            <?= (isset($commande['id_couleur_tissu_bois']) && (string)$commande['id_couleur_tissu_bois'] === (string)$item['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($item['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="motifbois">Référence Motif coussin - bois <span class="required">*</span></label>
                                <select id="motifbois" name="motifbois" class="input-field">
                                    <option value="">-- Sélectionnez une option --</option>
                                    <?php foreach ($motifsbois as $item): ?>
                                        <option value="<?= htmlspecialchars($item['id']) ?>"
                                            <?= (isset($commande['id_motif_bois']) && (string)$commande['id_motif_bois'] === (string)$item['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($item['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>



                            <div class="form-group">
                                <label for="nb_accoudoir_bois">Nombre d'accoudoirs <span class="required">*</span></label>
                                <select id="nb_accoudoir_bois" name="nb_accoudoir_bois" class="input-field">
                                    <option value="">-- Sélectionnez une option --</option>
                                    <option value="0" <?= (isset($commande['nb_accoudoir_bois']) && $commande['nb_accoudoir_bois'] == 0) ? 'selected' : '' ?>>0</option>
                                    <option value="1" <?= (isset($commande['nb_accoudoir_bois']) && $commande['nb_accoudoir_bois'] == 1) ? 'selected' : '' ?>>1</option>
                                    <option value="2" <?= (isset($commande['nb_accoudoir_bois']) && $commande['nb_accoudoir_bois'] == 2) ? 'selected' : '' ?>>2</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="accoudoir1">Accoudoir Bois 1 <span class="required">*</span></label>
                                <select id="accoudoir1" name="accoudoir1" class="input-field">
                                    <option value="">-- Sélectionnez une option --</option>
                                    <?php foreach ($accoudoirsbois as $item): ?>
                                        <option value="<?= $item['id'] ?>"
                                            <?= (isset($accoudoir1) && (string)$accoudoir1 === (string)$item['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($item['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group" id="accoudoir2-group">
                                <label for="accoudoir2">Accoudoir Bois 2 <span class="required">*</span></label>
                                <select id="accoudoir2" name="accoudoir2" class="input-field">
                                    <option value="">-- Sélectionnez une option --</option>
                                    <?php foreach ($accoudoirsbois as $item): ?>
                                        <option value="<?= $item['id'] ?>"
                                            <?= (isset($accoudoir2) && (string)$accoudoir2 === (string)$item['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($item['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="tissu">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="modele">Référence Modèle banquette - tissu <span class="required">*</span></label>
                                <select id="modele" name="modele" class="input-field">
                                    <option value="">-- Sélectionnez une option --</option>
                                    <?php foreach ($modeles as $item): ?>
                                        <option value="<?= htmlspecialchars($item['id']) ?>"
                                            <?= (isset($commande['id_modele']) && (string)$commande['id_modele'] === (string)$item['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($item['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="couleurtissu">Référence Couleur banquette - tissu <span class="required">*</span></label>
                                <select id="couleurtissu" name="couleurtissu" class="input-field">
                                    <option value="">-- Sélectionnez une option --</option>
                                    <?php foreach ($couleurstissu as $item): ?>
                                        <option value="<?= htmlspecialchars($item['id']) ?>"
                                            <?= (isset($commande['id_couleur_tissu']) && (string)$commande['id_couleur_tissu'] === (string)$item['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($item['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="motiftissu">Référence Motif coussin - tissu <span class="required">*</span></label>
                                <select id="motiftissu" name="motiftissu" class="input-field">
                                    <option value="">-- Sélectionnez une option --</option>
                                    <?php foreach ($motifstissu as $item): ?>
                                        <option value="<?= htmlspecialchars($item['id']) ?>"
                                            <?= (isset($commande['id_motif_tissu']) && (string)$commande['id_motif_tissu'] === (string)$item['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($item['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="dossiertissu">Référence Dossier - tissu <span class="required">*</span></label>
                                <select id="dossiertissu" name="dossiertissu" class="input-field">
                                    <option value="">-- Sélectionnez une option --</option>
                                    <?php foreach ($dossierstissu as $item): ?>
                                        <option value="<?= htmlspecialchars($item['id']) ?>"
                                            <?= (isset($commande['id_dossier_tissu']) && (string)$commande['id_dossier_tissu'] === (string)$item['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($item['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="nb_accoudoir_tissu">Nombre d'accoudoirs <span class="required">*</span></label>
                                <select id="nb_accoudoir_tissu" name="nb_accoudoir" class="input-field">
                                    <option value="">-- Sélectionnez une option --</option>
                                    <option value="2" <?= (isset($commande['id_nb_accoudoir']) && $commande['id_nb_accoudoir'] == '2') ? 'selected' : '' ?>>2</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="accoudoirtissu">Référence Accoudoir - tissu <span class="required">*</span></label>
                                <select id="accoudoirtissu" name="accoudoirtissu" class="input-field">
                                    <option value="">-- Sélectionnez une option --</option>
                                    <?php foreach ($accoudoirstissu as $item): ?>
                                        <option value="<?= $item['id'] ?>" <?= (isset($commande['id_accoudoir_tissu']) && $commande['id_accoudoir_tissu'] == $item['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($item['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group btn-slider">
                            <label for="visible">Afficher sur le site</label>
                            <label class="switch">
                                <input type="checkbox" id="visible" name="visible" <?php if ($commande['visible']) echo 'checked'; ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="button-section">
                        <div class="buttons">
                            <button type="button" id="btn-retour" class="btn-beige" onclick="history.go(-1)">Retour</button>
                            <input type="submit" class="btn-noir" value="Modifier"></input>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
<footer>
    <?php require '../squelette/footer.php'; ?>
</footer>

</html>