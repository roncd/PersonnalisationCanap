<?php
require '../config.php';
session_start();

if (!isset($_SESSION['id'])) {
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
    $prix = trim($_POST['prix']);
    $longueurA = trim($_POST['longueurA']);
    $longueurB = trim($_POST['longueurB']) ?: null;
    $longueurC = trim($_POST['longueurC']) ?: null;
    $idStructure = trim($_POST['structure']);
    $idBanquette = trim($_POST['banquette']) ?: null;
    $idMousse = trim($_POST['mousse']) ?: null;
    $idCouleurBois = trim($_POST['couleurbois']) ?: null;
    $idDecoration = trim($_POST['decoration']) ?: null;
    $idAccoudoirBois = trim($_POST['accoudoirbois']) ?: null;
    $idDossierBois = trim($_POST['dossierbois']) ?: null;
    $idTissuBois = trim($_POST['couleurtissubois']) ?: null;
    $idMotifBois = trim($_POST['motifbois']) ?: null;
    $idModele = trim($_POST['modele']) ?: null;
    $idCouleurTissu = trim($_POST['couleurtissu']) ?: null;
    $idMotifTissu = trim($_POST['motiftissu']) ?: null;
    $idAccoudoirTissu = trim($_POST['accoudoirtissu']) ?: null;
    $idDossierTissu = trim($_POST['dossiertissu']) ?: null;
    $nbAccoudoir = trim($_POST['nb_accoudoir']) ?: null;
    $nom = trim($_POST['nom']) ?: null;
    $imagePath = isset($commande['img']) ? $commande['img'] : null; // Vérifie que l'image existe

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
    }


    if (empty($prix)) {
        $_SESSION['message'] = 'Les champs obligatoires doivent être remplis.';
        $_SESSION['message_type'] = 'error';
    } else {
        $stmt = $pdo->prepare("UPDATE commande_prefait SET prix = ?, prix_dimensions = ?, id_structure = ?, longueurA = ?, longueurB = ?, longueurC = ?, id_banquette = ?, id_mousse = ?, id_couleur_bois = ?, id_decoration = ?, id_accoudoir_bois = ?, id_dossier_bois = ?, id_couleur_tissu_bois = ?, id_motif_bois = ?, id_modele = ?, id_couleur_tissu = ?,  id_motif_tissu = ?, id_dossier_tissu = ?, id_accoudoir_tissu = ?, id_nb_accoudoir = ?, nom = ?, img = ?
         WHERE id = ?");
        if ($stmt->execute([
            $prix,
            $prixDimensions,
            $idStructure,
            $longueurA,
            $longueurB,
            $longueurC,
            $idBanquette,
            $idMousse,
            $idCouleurBois,
            $idDecoration,
            $idAccoudoirBois,
            $idDossierBois,
            $idTissuBois,
            $idMotifBois,
            $idModele,
            $idCouleurTissu,
            $idMotifTissu,
            $idDossierTissu,
            $idAccoudoirTissu,
            $nbAccoudoir,
            $nom,
            $imagePath,
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
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une commande préfaites</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/admin/ajout.css">
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
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
            <h2>Modifie une commande préfaite</h2>
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
                            <label for="prix">Prix (€)</label>
                            <input type="number" step="0.01" id="prix" name="prix" class="input-field" required value="<?= htmlspecialchars($commande['prix']) ?>">
                        </div>

                        <div class="form-group">
                            <label for="prix_dimensions">Prix dimensions (€)</label>
                            <input type="number" step="0.01" id="prix_dimensions" name="prix_dimensions" class="input-field" required value="<?= htmlspecialchars($commande['prix_dimensions']) ?>">
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
                        <select id="mousse" name="mousse" class="input-field"required>
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
                        <label for="accoudoirbois">Référence Accoudoir - bois <span class="required">*</span></label>
                        <select id="accoudoirbois" name="accoudoirbois" class="input-field">
                            <option value="">-- Sélectionnez une option --</option>
                            <?php foreach ($accoudoirsbois as $item): ?>
                                <option value="<?= htmlspecialchars($item['id']) ?>"
                                    <?= (isset($commande['id_accoudoir_bois']) && (string)$commande['id_accoudoir_bois'] === (string)$item['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($item['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

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
                </div>

                <div class="form-row">
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
                        <label for="accoudoirtissu">Référence Accoudoir - tissu <span class="required">*</span></label>
                        <select id="accoudoirtissu" name="accoudoirtissu" class="input-field">
                            <option value="">-- Sélectionnez une option --</option>
                            <?php foreach ($accoudoirstissu as $item): ?>
                                <option value="<?= htmlspecialchars($item['id']) ?>"
                                    <?= (isset($commande['id_accoudoir_tissu']) && (string)$commande['id_accoudoir_tissu'] === (string)$item['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($item['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
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

                    <div class="form-group">
                        <label for="nb_accoudoir">Nombre d'accoudoirs <span class="required">*</span></label>
                        <input type="number" id="nb_accoudoir" name="nb_accoudoir" class="input-field"
                            value="<?= isset($commande['id_nb_accoudoir']) ? htmlspecialchars($commande['id_nb_accoudoir']) : '' ?>">
                    </div>
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