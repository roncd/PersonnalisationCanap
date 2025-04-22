<?php
require '../config.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

function fetchData($pdo, $table)
{
    $stmt = $pdo->prepare("SELECT id, nom FROM $table");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$clients = fetchData($pdo, 'client');
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

$stmt = $pdo->prepare("SELECT id, longueurA, longueurB, longueurC FROM dimension");
$stmt->execute();
$dimensions = $stmt->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prix = trim($_POST['prix']);
    $commentaire = trim($_POST['commentaire']);
    $date = trim($_POST['date']);
    $statut = trim($_POST['statut']);
    $idClient = trim($_POST['client']);
    $idStructure = trim($_POST['structure']);
    $idDimension = trim($_POST['dimension']);
    $idBanquette = trim($_POST['banquette']);
    $idMousse = trim($_POST['mousse']);
    $idCouleurBois = trim($_POST['couleurbois']) ? $_POST['couleurbois'] : NULL;
    $idDecoration = trim($_POST['decoration']) ? $_POST['decoration'] : NULL;
    $idAccoudoirBois = trim($_POST['accoudoirbois']) ? $_POST['accoudoirbois'] : NULL;
    $idDossierBois = trim($_POST['dossierbois']) ? $_POST['dossierbois'] : NULL;
    $idTissuBois = trim($_POST['couleurtissubois']) ? $_POST['couleurtissubois'] : NULL;
    $idMotifBois = trim($_POST['motifbois']) ? $_POST['motifbois'] : NULL;
    $idModele = trim($_POST['modele']) ? $_POST['modele'] : NULL;
    $idCouleurTissu = trim($_POST['couleurtissu']) ? $_POST['couleurtissu'] : NULL;
    $idMotifTissu = trim($_POST['motiftissu']) ? $_POST['motiftissu'] : NULL;
    $idAccoudoirTissu = trim($_POST['accoudoirtissu']) ? $_POST['accoudoirtissu'] : NULL;
    $idDossierTissu = trim($_POST['dossiertissu']) ? $_POST['dossiertissu'] : NULL;


    // Validation des champs obligatoires
    if (empty($prix) || empty($idClient) || empty($idStructure) || empty($idDimension) || empty($idBanquette) || empty($idMousse)) {
        $_SESSION['message'] = 'Tous les champs sont requis !';
        $_SESSION['message_type'] = 'error';
    }

    // Tentative d'insertion dans la base de données
    try {
        $stmt = $pdo->prepare("INSERT INTO commande_detail (prix, commentaire, date, statut, id_client, id_structure, id_dimension, id_banquette, id_mousse, id_couleur_bois, id_decoration, id_accoudoir_bois, id_dossier_bois, id_couleur_tissu_bois, id_motif_bois, id_modele, id_couleur_tissu, id_motif_tissu, id_accoudoir_tissu, id_dossier_tissu) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$prix, $commentaire, $date, $statut, $idClient, $idStructure, $idDimension, $idBanquette, $idMousse, $idCouleurBois, $idDecoration, $idAccoudoirBois, $idDossierBois, $idTissuBois, $idMotifBois, $idModele, $idCouleurTissu, $idMotifTissu, $idAccoudoirTissu, $idDossierTissu]);

        $_SESSION['message'] = 'La commande a été ajouté avec succès !';
        $_SESSION['message_type'] = 'success';
    } catch (Exception $e) {
        $_SESSION['message'] = 'Erreur lors de l\'ajout de la commande : ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajoute une commande détaillée</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/ajout.css">
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <style>
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>

    <header>
        <?php require '../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
            <h2>Ajoute une commande détaillée</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form class="formulaire-creation-compte" action="" method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="client">ID Client</label>
                            <select class="input-field" id="client" name="client">
                                <option value="">-- Sélectionnez un client --</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?= htmlspecialchars($client['id']) ?>">
                                        <?= htmlspecialchars($client['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="prix">Prix total (en €)</label>
                            <input type="number" id="prix" name="prix" class="input-field" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="text">Commentaire</label>
                            <textarea id="text" name="commentaire" class="input-field"></textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date">Date de création</label>
                            <input type="datetime-local" id="date" name="date" class="input-field" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="statut">Statut de la commande</label>
                            <select class="input-field" id="statut" name="statut">
                                <option value="validation">En attente de validation</option>
                                <option value="construction">En construction</option>
                                <option value="final">Finalisées</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="structure">ID Structure</label>
                            <select class="input-field" id="structure" name="structure">
                                <option value="">-- Sélectionnez un structure --</option>
                                <?php foreach ($structures as $structure): ?>
                                    <option value="<?= htmlspecialchars($structure['id']) ?>">
                                        <?= htmlspecialchars($structure['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="dimension">ID Dimension</label>
                            <select class="input-field" id="dimension" name="dimension">
                                <option value="">-- Sélectionnez une dimension --</option>
                                <?php foreach ($dimensions as $dimension): ?>
                                    <option value="<?= htmlspecialchars($dimension['id']) ?>">
                                        <?= htmlspecialchars($dimension['longueurA'] . ' x ' . $dimension['longueurB'] . ' x ' . $dimension['longueurC']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="banquette">ID Type banquette</label>
                            <select class="input-field" id="banquette" name="banquette">
                                <option value="">-- Sélectionnez un banquette --</option>
                                <?php foreach ($banquettes as $banquette): ?>
                                    <option value="<?= htmlspecialchars($banquette['id']) ?>">
                                        <?= htmlspecialchars($banquette['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mousse">ID Mousse</label>
                            <select class="input-field" id="mousse" name="mousse">
                                <option value="">-- Sélectionnez un mousse --</option>
                                <?php foreach ($mousses as $mousse): ?>
                                    <option value="<?= htmlspecialchars($mousse['id']) ?>">
                                        <?= htmlspecialchars($mousse['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="couleurbois">ID Couleur bois</label>
                            <select class="input-field" id="couleurbois" name="couleurbois">
                                <option value="">-- Sélectionnez un couleur de bois --</option>
                                <?php foreach ($couleursbois as $couleurbois): ?>
                                    <option value="<?= htmlspecialchars($couleurbois['id']) ?>">
                                        <?= htmlspecialchars($couleurbois['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="decoration">ID Décoration bois</label>
                            <select class="input-field" id="decoration" name="decoration">
                                <option value="">-- Sélectionnez un decoration --</option>
                                <?php foreach ($decorations as $decoration): ?>
                                    <option value="<?= htmlspecialchars($decoration['id']) ?>">
                                        <?= htmlspecialchars($decoration['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="accoudoirbois">ID Accoudoir bois</label>
                            <select class="input-field" id="accoudoirbois" name="accoudoirbois">
                                <option value="">-- Sélectionnez un accoudoir en bois --</option>
                                <?php foreach ($accoudoirsbois as $accoudoirbois): ?>
                                    <option value="<?= htmlspecialchars($accoudoirbois['id']) ?>">
                                        <?= htmlspecialchars($accoudoirbois['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="dossierbois">ID Dossier bois</label>
                            <select class="input-field" id="dossierbois" name="dossierbois">
                                <option value="">-- Sélectionnez un dossier en bois --</option>
                                <?php foreach ($dossiersbois as $dossierbois): ?>
                                    <option value="<?= htmlspecialchars($dossierbois['id']) ?>">
                                        <?= htmlspecialchars($dossierbois['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="couleurtissubois">ID Couleur tissu bois</label>
                            <select class="input-field" id="couleurtissubois" name="couleurtissubois">
                                <option value="">-- Sélectionnez une couleur de tissu en bois --</option>
                                <?php foreach ($couleurstissubois as $couleurtissubois): ?>
                                    <option value="<?= htmlspecialchars($couleurtissubois['id']) ?>">
                                        <?= htmlspecialchars($couleurtissubois['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="motifbois">ID Motif bois</label>
                            <select class="input-field" id="motifbois" name="motifbois">
                                <option value="">-- Sélectionnez un motif en bois --</option>
                                <?php foreach ($motifsbois as $motifbois): ?>
                                    <option value="<?= htmlspecialchars($motifbois['id']) ?>">
                                        <?= htmlspecialchars($motifbois['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="modele">ID Modèle banquette tissu</label>
                            <select class="input-field" id="modele" name="modele">
                                <option value="">-- Sélectionnez un modele en tissu --</option>
                                <?php foreach ($modeles as $modele): ?>
                                    <option value="<?= htmlspecialchars($modele['id']) ?>">
                                        <?= htmlspecialchars($modele['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="couleurtissu">ID Couleur tissu</label>
                            <select class="input-field" id="couleurtissu" name="couleurtissu">
                                <option value="">-- Sélectionnez une couleur de tissu --</option>
                                <?php foreach ($couleurstissu as $couleurtissu): ?>
                                    <option value="<?= htmlspecialchars($couleurtissu['id']) ?>">
                                        <?= htmlspecialchars($couleurtissu['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="motiftissu">ID Motif tissu</label>
                            <select class="input-field" id="motiftissu" name="motiftissu">
                                <option value="">-- Sélectionnez un motif en tissu --</option>
                                <?php foreach ($motifstissu as $motiftissu): ?>
                                    <option value="<?= htmlspecialchars($motiftissu['id']) ?>">
                                        <?= htmlspecialchars($motiftissu['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="dossiertissu">ID Dossier tissu</label>
                            <select class="input-field" id="dossiertissu" name="dossiertissu">
                                <option value="">-- Sélectionnez un dossier en tissu --</option>
                                <?php foreach ($dossierstissu as $dossiertissu): ?>
                                    <option value="<?= htmlspecialchars($dossiertissu['id']) ?>">
                                        <?= htmlspecialchars($dossiertissu['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="accoudoirtissu">ID Accoudoir tissu</label>
                            <select class="input-field" id="accoudoirtissu" name="accoudoirtissu">
                                <option value="">-- Sélectionnez un accoudoir en tissu --</option>
                                <?php foreach ($accoudoirstissu as $accoudoirtissu): ?>
                                    <option value="<?= htmlspecialchars($accoudoirtissu['id']) ?>">
                                        <?= htmlspecialchars($accoudoirtissu['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="footer">
                        <div class="buttons">
                            <button type="button" class="btn-retour" onclick="history.go(-1)">Retour</button>
                            <input type="submit" class="btn-valider" value="Ajouter"></input>
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