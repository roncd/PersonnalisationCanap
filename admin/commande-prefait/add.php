<?php
require '../config.php';
session_start();
require '../include/session_expiration.php';

if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
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
    $longueurA = trim($_POST['longueurA']);
    $longueurB = trim($_POST['longueurB']) ?: null;
    $longueurC = trim($_POST['longueurC']) ?: null;
    $prixDimensions = trim($_POST['prix_dimensions']);
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

    $imagePath = null; // Déclarer AVANT le bloc

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
                $imagePath = $imageName; // 👈 C’est ça qui manquait
            } else {
                $_SESSION['message'] = 'Erreur lors du téléchargement de l\'image.';
                $_SESSION['message_type'] = 'error';
            }
        } else {
            $_SESSION['message'] = 'Format de fichier non autorisé.';
            $_SESSION['message_type'] = 'error';
        }
    }

    

    if (empty($prix)) {
        $_SESSION['message'] = 'Les champs obligatoires doivent être remplis.';
        $_SESSION['message_type'] = 'error';
    } else {
        try {
    // 1. Insertion de la commande pré-faite
    $stmt = $pdo->prepare("INSERT INTO commande_prefait (
        prix, prix_dimensions, id_structure, longueurA, longueurB, longueurC,
        id_banquette, id_mousse, id_couleur_bois, id_decoration,
        id_accoudoir_bois, id_dossier_bois, id_couleur_tissu_bois, id_motif_bois,
        id_modele, id_couleur_tissu, id_motif_tissu, id_dossier_tissu, id_accoudoir_tissu,
        id_nb_accoudoir, nom, img
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
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
        $imagePath
    ]);

    // 2. Récupérer l'ID de la commande insérée
    $idCommande = $pdo->lastInsertId();

    // 3. Insérer les accoudoirs bois (si type BOIS)
    $acc1 = $_POST['accoudoir1'] ?? null;
    $acc2 = $_POST['accoudoir2'] ?? null;

    if (!empty($acc1)) {
        $stmtAcc1 = $pdo->prepare("INSERT INTO commande_prefait_accoudoir (id_commande_prefait, id_accoudoir_bois, nb_accoudoir) VALUES (?, ?, 1)");
        $stmtAcc1->execute([$idCommande, intval($acc1)]);
    }

    if (!empty($acc2)) {
        $stmtAcc2 = $pdo->prepare("INSERT INTO commande_prefait_accoudoir (id_commande_prefait, id_accoudoir_bois, nb_accoudoir) VALUES (?, ?, 1)");
        $stmtAcc2->execute([$idCommande, intval($acc2)]);
    }

    $_SESSION['message'] = 'La commande préfaite a été ajoutée avec succès.';
    $_SESSION['message_type'] = 'success';
    header('Location: visualiser.php');
    exit();

} catch (Exception $e) {
    $_SESSION['message'] = 'Erreur lors de l\'ajout du canapé pré-personnalisé : ' . $e->getMessage();
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
    <title>Ajoute une commande préfaites</title>
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
            <h2>Ajoute une commande préfaite</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form class="formulaire-creation-compte" action="" method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom du salon <span class="required">*</span></label>
                            <input type="text" id="nom" name="nom" class="input-field">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="img">Image <span class="required">*</span></label>
                            <input type="file" id="img" name="img" class="input-field" accept="image/*" onchange="loadFile(event)" required>
                            <img class="preview-img" id="output" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="prix">Prix (€) <span class="required">*</span></label>
                            <input type="number" step="0.01" id="prix" name="prix" class="input-field" required>
                        </div>
                    </div>

                            <div class="form-row">
            <div class="form-group">
                <label for="longueurA">Longueur A</label>
                <input type="number" id="longueurA" name="longueurA" class="input-field" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="longueurB">Longueur B</label>
                <input type="number" id="longueurB" name="longueurB" class="input-field">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="longueurC">Longueur C</label>
                <input type="number" id="longueurC" name="longueurC" class="input-field">
            </div>
        </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="structure">Référence Structure <span class="required">*</span></label>
                            <select id="structure" name="structure" class="input-field" required>
                                <option value="">-- Choisir une structure -- </option>
                                <?php foreach ($structures as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="mousse">Référence Mousse <span class="required">*</span></label>
                            <select id="mousse" name="mousse" class="input-field">
                                <option value="">-- Choisir --</option>
                                <?php foreach ($mousses as $item): ?>
                                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="banquette">Référence Type de banquette <span class="required">*</span></label>
                            <select id="banquette" name="banquette" class="input-field">
                                <option value="">-- Choisir --</option>
                                <?php foreach ($banquettes as $b): ?>
                                    <option value="<?= $b['id'] ?>"
                                        data-type="<?= htmlspecialchars($b['nom']) ?>">
                                        <?= htmlspecialchars($b['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="bois">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="couleurbois">Référence Couleur banquette - bois</label>
                                <select id="couleurbois" name="couleurbois" class="input-field">
                                    <option value="">-- Choisir --</option>
                                    <?php foreach ($couleursbois as $item): ?>
                                        <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="decoration">Référence Décoration - bois</label>
                                <select id="decoration" name="decoration" class="input-field">
                                    <option value="">-- Choisir --</option>
                                    <?php foreach ($decorations as $item): ?>
                                        <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="accoudoirbois">Référence Accoudoir - bois</label>
                                <select id="accoudoirbois" name="accoudoirbois" class="input-field">
                                    <option value="">-- Choisir --</option>
                                    <?php foreach ($accoudoirsbois as $item): ?>
                                        <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="dossierbois">Référence Dossier - bois</label>
                                <select id="dossierbois" name="dossierbois" class="input-field">
                                    <option value="">-- Choisir --</option>
                                    <?php foreach ($dossiersbois as $item): ?>
                                        <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="couleurtissubois">Référence Motif tissu - bois</label>
                                <select id="couleurtissubois" name="couleurtissubois" class="input-field">
                                    <option value="">-- Choisir --</option>
                                    <?php foreach ($couleurstissubois as $item): ?>
                                        <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="motifbois">Référence Motif coussin - bois</label>
                                <select id="motifbois" name="motifbois" class="input-field">
                                    <option value="">-- Choisir --</option>
                                    <?php foreach ($motifsbois as $item): ?>
                                        <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                                                        </div>

                            <div class="form-group">
    <label for="nb_accoudoir">Nombre d'accoudoirs</label>
    <select id="nb_accoudoir" name="nb_accoudoir" class="input-field" onchange="toggleAccoudoirs()">
        <option value="1">1</option>
        <option value="2">2</option>
    </select>
</div>

<div class="form-group">
    <label for="accoudoir1">Accoudoir Bois 1 (Obligatoire)</label>
    <select id="accoudoir1" name="accoudoir1" class="input-field">
        <option value="">-- Choisir --</option>
        <?php foreach ($accoudoirsbois as $item): ?>
            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div style="display:none; class="form-group" id="accoudoir2-group" style="display:none;">
    <label for="accoudoir2">Accoudoir Bois 2 (Facultatif)</label>
    <select id="accoudoir2" name="accoudoir2" class="input-field">
        <option value="">-- Choisir --</option>
        <?php foreach ($accoudoirsbois as $item): ?>
            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
        <?php endforeach; ?>
    </select>
</div>     

<script> 
function toggleAccoudoirs() {
    const nb = document.getElementById("nb_accoudoir").value;
    document.getElementById("accoudoir2-group").style.display = (nb == "2") ? "block" : "none";
}
</script>

                        </div>
                    </div>
                    <div class="tissu">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="modele">Référence Modèle banquette - tissu</label>
                                <select id="modele" name="modele" class="input-field">
                                    <option value="">-- Choisir --</option>
                                    <?php foreach ($modeles as $item): ?>
                                        <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="couleurtissu">Référence Couleur banquette - tissu</label>
                                <select id="couleurtissu" name="couleurtissu" class="input-field">
                                    <option value="">-- Choisir --</option>
                                    <?php foreach ($couleurstissu as $item): ?>
                                        <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="motiftissu">Référence Motif coussin - tissu</label>
                                <select id="motiftissu" name="motiftissu" class="input-field">
                                    <option value="">-- Choisir --</option>
                                    <?php foreach ($motifstissu as $item): ?>
                                        <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="accoudoirtissu">Référence Accoudoir - tissu</label>
                                <select id="accoudoirtissu" name="accoudoirtissu" class="input-field">
                                    <option value="">-- Choisir --</option>
                                    <?php foreach ($accoudoirstissu as $item): ?>
                                        <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="dossiertissu">Référence Dossier - tissu</label>
                                <select id="dossiertissu" name="dossiertissu" class="input-field">
                                    <option value="">-- Choisir --</option>
                                    <?php foreach ($dossierstissu as $item): ?>
                                        <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div> 
                            <div class="form-group">
                                <label for="nb_accoudoir">Nombre d'accoudoirs</label>
                                <input type="number" id="nb_accoudoir" name="nb_accoudoir" class="input-field">
                            </div>
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
</body>
<footer>
    <?php require '../squelette/footer.php'; ?>
</footer>

</html>