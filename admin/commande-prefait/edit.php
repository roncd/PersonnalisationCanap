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

function fetchData($pdo, $table)
{
    $stmt = $pdo->prepare("SELECT id, nom FROM $table");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Chargement des données
$structures = fetchData($pdo, 'structure');
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prix = trim($_POST['prix']);
    $prixDimensions = trim($_POST['prix_dimensions']);
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
   $imagePath = $commande['image']; // Valeur par défaut : garder l’ancienne image

if (isset($_FILES['img']) && $_FILES['img']['error'] === 0) {
    $uploadDir = '../uploads/canape-prefait/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $tmpName = $_FILES['img']['tmp_name'];
    $originalName = basename($_FILES['img']['name']);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($extension, $allowedExtensions)) {
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $imageName = $baseName . '_' . time() . '.' . $extension;
        $destination = $uploadDir . $imageName;

        if (move_uploaded_file($tmpName, $destination)) {
            // Optionnel : supprimer l’ancienne image
            // if ($commande['image'] && file_exists($uploadDir . $commande['image'])) {
            //     unlink($uploadDir . $commande['image']);
            // }

            $imagePath = $imageName;
        } else {
            $_SESSION['message'] = 'Erreur lors du téléchargement de l\'image.';
            $_SESSION['message_type'] = 'error';
        }
    } else {
        $_SESSION['message'] = 'Format de fichier non autorisé.';
        $_SESSION['message_type'] = 'error';
    }
}
// Sinon on garde simplement $imagePath = $commande['image']



    if (empty($prix) || empty($prixDimensions) || empty($longueurA)) {
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

</head>

<body>

    <header>
        <?php require '../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
            <h2>Modifie  une commande préfaite</h2>
<?php require '../include/message.php'; ?>
<div class="form">
    <form action="edit.php?id=<?php echo $commande['id']; ?>" method="POST" enctype="multipart/form-data" class="formulaire-creation-compte">
         <div class="form-row">
            <div class="form-group">
                <label for="nom">Nom de la commande</label>
                <input type="text" id="nom" name="nom" class="input-field" value="<?= htmlspecialchars($commande['nom']) ?>">
            </div>
        </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="img">Image (Laissez vide pour conserver l'image actuelle)</label>
                            <input type="file" id="img" name="img" class="input-field" accept="image/*" onchange="loadFile(event)" >
                            <img class="preview-img" src="../uploads/canape-prefait/<?php echo htmlspecialchars($commande['img']); ?>" id="output" />
                         </div>
                    </div>

        <div class="form-row">
            <div class="form-group">
                <label for="prix">Prix (€)</label>
                <input type="number" step="0.01" id="prix" name="prix" class="input-field" required value="<?= htmlspecialchars($commande['prix']) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="prix_dimensions">Prix dimensions (€)</label>
                <input type="number" step="0.01" id="prix_dimensions" name="prix_dimensions" class="input-field" required value="<?= htmlspecialchars($commande['prix_dimensions']) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="longueurA">Longueur A</label>
                <input type="number" id="longueurA" name="longueurA" class="input-field" required value="<?= htmlspecialchars($commande['longueurA']) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="longueurB">Longueur B</label>
                <input type="number" id="longueurB" name="longueurB" class="input-field" value="<?= htmlspecialchars($commande['longueurB']) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="longueurC">Longueur C</label>
                <input type="number" id="longueurC" name="longueurC" class="input-field" value="<?= htmlspecialchars($commande['longueurC']) ?>">
            </div>
        </div>

    <!-- Sélections dynamiques (exemple pour structure) -->
<div class="form-row">
    <div class="form-group">
        <label for="structure">Structure</label>
        <select id="structure" name="structure" class="input-field" required>
            <option value="">-- Choisir une structure --</option>
            <?php foreach ($structures as $s): ?>
                <option value="<?= $s['id'] ?>" <?= ($commande['id_structure'] == $s['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label for="banquette">Type de banquette</label>
        <select id="banquette" name="banquette" class="input-field">
            <option value="">-- Choisir --</option>
            <?php foreach ($banquettes as $b): ?>
                <option value="<?= htmlspecialchars($b['id']) ?>"
                    <?= (isset($commande['id_banquette']) && (string)$commande['id_banquette'] === (string)$b['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($b['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label for="mousse">Mousse</label>
        <select id="mousse" name="mousse" class="input-field">
            <option value="">-- Choisir --</option>
            <?php foreach ($mousses as $item): ?>
                <option value="<?= htmlspecialchars($item['id']) ?>"
                    <?= (isset($commande['id_mousse']) && (string)$commande['id_mousse'] === (string)$item['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($item['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label for="couleurbois">Couleur du bois</label>
        <select id="couleurbois" name="couleurbois" class="input-field">
            <option value="">-- Choisir --</option>
            <?php foreach ($couleursbois as $item): ?>
                <option value="<?= htmlspecialchars($item['id']) ?>"
                    <?= (isset($commande['id_couleur_bois']) && (string)$commande['id_couleur_bois'] === (string)$item['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($item['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>


<div class="form-row">
    <div class="form-group">
        <label for="decoration">Décoration</label>
        <select id="decoration" name="decoration" class="input-field">
            <option value="">-- Choisir --</option>
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
        <label for="accoudoirbois">Accoudoir bois</label>
        <select id="accoudoirbois" name="accoudoirbois" class="input-field">
            <option value="">-- Choisir --</option>
            <?php foreach ($accoudoirsbois as $item): ?>
                <option value="<?= htmlspecialchars($item['id']) ?>"
                    <?= (isset($commande['id_accoudoir_bois']) && (string)$commande['id_accoudoir_bois'] === (string)$item['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($item['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label for="dossierbois">Dossier bois</label>
        <select id="dossierbois" name="dossierbois" class="input-field">
            <option value="">-- Choisir --</option>
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
        <label for="couleurtissubois">Couleur tissu (bois)</label>
        <select id="couleurtissubois" name="couleurtissubois" class="input-field">
            <option value="">-- Choisir --</option>
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
        <label for="motifbois">Motif bois</label>
        <select id="motifbois" name="motifbois" class="input-field">
            <option value="">-- Choisir --</option>
            <?php foreach ($motifsbois as $item): ?>
                <option value="<?= htmlspecialchars($item['id']) ?>"
                    <?= (isset($commande['id_motif_bois']) && (string)$commande['id_motif_bois'] === (string)$item['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($item['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>


<div class="form-row">
    <div class="form-group">
        <label for="modele">Modèle</label>
        <select id="modele" name="modele" class="input-field">
            <option value="">-- Choisir --</option>
            <?php foreach ($modeles as $item): ?>
                <option value="<?= htmlspecialchars($item['id']) ?>"
                    <?= (isset($commande['id_modele']) && (string)$commande['id_modele'] === (string)$item['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($item['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label for="couleurtissu">Couleur tissu</label>
        <select id="couleurtissu" name="couleurtissu" class="input-field">
            <option value="">-- Choisir --</option>
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
        <label for="motiftissu">Motif tissu</label>
        <select id="motiftissu" name="motiftissu" class="input-field">
            <option value="">-- Choisir --</option>
            <?php foreach ($motifstissu as $item): ?>
                <option value="<?= htmlspecialchars($item['id']) ?>"
                    <?= (isset($commande['id_motif_tissu']) && (string)$commande['id_motif_tissu'] === (string)$item['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($item['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label for="accoudoirtissu">Accoudoir tissu</label>
        <select id="accoudoirtissu" name="accoudoirtissu" class="input-field">
            <option value="">-- Choisir --</option>
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
        <label for="dossiertissu">Dossier tissu</label>
        <select id="dossiertissu" name="dossiertissu" class="input-field">
            <option value="">-- Choisir --</option>
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
        <label for="nb_accoudoir">Nombre d'accoudoirs</label>
        <input type="number" id="nb_accoudoir" name="nb_accoudoir" class="input-field"
            value="<?= isset($commande['id_nb_accoudoir']) ? htmlspecialchars($commande['id_nb_accoudoir']) : '' ?>">
    </div>
</div>

        <div class="form-row">
            <button type="submit" class="btn-noir">Modifier</button>
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