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
    header("Location: index.php");
    exit();
}

// Récupérer les données actuelles de la commande
$stmt = $pdo->prepare("SELECT * FROM commande_detail WHERE  id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$commande) {
    $_SESSION['message'] = 'Commande introuvable.';
    $_SESSION['message_type'] = 'error';
    header("Location: index.php");
    exit();
}

// Récupérer les accoudoirs déjà liés à cette commande
$stmtAcc = $pdo->prepare("SELECT id_accoudoir_bois FROM commande_detail_accoudoir WHERE id_commande_detail = ?");
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

$clients = fetchData($pdo, 'client');
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
    // Récupérer et valider les données
    $prix = trim($_POST['prix']);
    $commentaire = trim($_POST['commentaire']);
    $date = trim($_POST['date']);
    $statut = trim($_POST['statut']);
    $idClient = trim($_POST['client']);
    $idStructure = trim($_POST['structure']);
    $longueurA = trim($_POST['longueurA']);
    $longueurB = trim($_POST['longueurB'])  ? $_POST['longueurB'] : NULL;
    $longueurC = trim($_POST['longueurC']) ? $_POST['longueurC'] : NULL;
    $idBanquette = trim($_POST['banquette']);
    $idMousse = trim($_POST['mousse']);
    $idCouleurBois = trim($_POST['couleurbois']) ? $_POST['couleurbois'] : NULL;
    $idDecoration = trim($_POST['decoration']) ? $_POST['decoration'] : NULL;
    $idDossierBois = trim($_POST['dossierbois']) ? $_POST['dossierbois'] : NULL;
    $idTissuBois = trim($_POST['couleurtissubois']) ? $_POST['couleurtissubois'] : NULL;
    $idMotifBois = trim($_POST['motifbois']) ? $_POST['motifbois'] : NULL;
    $idModele = trim($_POST['modele']) ? $_POST['modele'] : NULL;
    $idCouleurTissu = trim($_POST['couleurtissu']) ? $_POST['couleurtissu'] : NULL;
    $idMotifTissu = trim($_POST['motiftissu']) ? $_POST['motiftissu'] : NULL;
    $idAccoudoirTissu = trim($_POST['accoudoirtissu']) ? $_POST['accoudoirtissu'] : NULL;
    $idDossierTissu = trim($_POST['dossiertissu']) ? $_POST['dossiertissu'] : NULL;
    $nbAccoudoir = trim($_POST['nb_accoudoir']) ?: null;
    $idAccoudoirGauche = trim($_POST['accoudoir_gauche']) ?: null;
    $idAccoudoirDroit = trim($_POST['accoudoir_droit']) ?: null;



    if (!isset($price) || empty($idClient) || empty($idStructure) || empty($longueurA) || empty($idBanquette) || empty($idMousse)) {
        $_SESSION['message'] = 'Tous les champs requis doivent être remplis.';
        $_SESSION['message_type'] = 'error';
    }

    // Supprimer les anciens accoudoirs
    $pdo->prepare("DELETE FROM commande_detail_accoudoir WHERE id_commande_detail = ?")->execute([$commande['id']]);

    // Réinsérer les nouveaux accoudoirs
    $acc1 = $_POST['accoudoir_gauche'] ?? null;
    $acc2 = $_POST['accoudoir_droit'] ?? null;

    if (!empty($acc1)) {
        $stmtAcc1 = $pdo->prepare("INSERT INTO commande_detail_accoudoir (id_commande_detail, id_accoudoir_bois, nb_accoudoir) VALUES (?, ?, 1)");
        $stmtAcc1->execute([$commande['id'], intval($acc1)]);
    }

    if (!empty($acc2)) {
        $stmtAcc2 = $pdo->prepare("INSERT INTO commande_detail_accoudoir (id_commande_detail, id_accoudoir_bois, nb_accoudoir) VALUES (?, ?, 1)");
        $stmtAcc2->execute([$commande['id'], intval($acc2)]);
    }


    // Mettre à jour la commande dans la base de donnéesnb_accoudoir_bois
    $stmt = $pdo->prepare("UPDATE commande_detail SET prix = ?, commentaire = ?, date = ?, statut = ?, id_client = ?, id_structure = ?, longueurA = ?, longueurB = ?, longueurC = ?, id_banquette = ?, id_mousse = ?, id_couleur_bois = ?, id_decoration = ?, id_dossier_bois = ?, id_couleur_tissu_bois = ?,  id_motif_bois = ?, id_modele = ?, id_couleur_tissu = ?, id_motif_tissu = ?, id_accoudoir_tissu = ?, id_dossier_tissu = ?,  id_nb_accoudoir = ?, id_accoudoir_gauche = ?, id_accoudoir_droit = ?
        WHERE id = ?");
    if ($stmt->execute([
        $prix,
        $commentaire,
        $date,
        $statut,
        $idClient,
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
        $idAccoudoirTissu,
        $idDossierTissu,
        $nbAccoudoir,
        $idAccoudoirGauche,
        $idAccoudoirDroit,
        $id
    ])) {
        $_SESSION['message'] = 'La commande a été mise à jour avec succès !';
        $_SESSION['message_type'] = 'success';
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['message'] = 'Erreur lors de la mise à jour de la commande.';
        $_SESSION['message_type'] = 'error';
    }
}
$valeurs = [
    "validation"   => "En attente de validation",
    "construction" => "En cours de construction",
    "final"        => "Commande finalisée"
];
$selected = (string) $commande['statut'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifie une commande - Canapé marocain</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/admin/ajout.css">
    <link rel="stylesheet" href="../../styles/message.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <script src="../../script/displayInput.js"></script>
</head>

<body>

    <header>
        <?php require '../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
            <h2>Modifie une commande - Canapé marocain</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form action="edit.php?id=<?php echo $commande['id']; ?>" method="POST" enctype="multipart/form-data" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="client">Référence Client <span class="required">*</span></label>
                            <select class="input-field" id="client" name="client" required>
                                <option value="">-- Sélectionnez une option --</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?= htmlspecialchars($client['id']) ?>"
                                        <?= ($client['id'] == $commande['id_client']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($client['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="prix">Prix total (en €) <span class="required">*</span></label>
                            <input type="number" id="prix" name="prix" class="input-field" value="<?php echo htmlspecialchars($commande['prix']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="text">Commentaire</label>
                            <textarea id="text" name="commentaire" value="<?php echo htmlspecialchars($commande['commentaire']); ?>" class="input-field"></textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date">Date de création <span class="required">*</span></label>
                            <input type="datetime-local" id="date" name="date" value="<?php echo htmlspecialchars($commande['date']); ?>" class="input-field" required>
                        </div>
                        <div class="form-group">
                            <label for="statut">Statut de la commande</label>
                            <select class="input-field" id="statut" name="statut">
                                <?php foreach ($valeurs as $valeur => $libelle): ?>
                                    <option value="<?php echo $valeur; ?>" <?php if ($selected === $valeur) echo 'selected'; ?>>
                                        <?php echo $libelle; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="structure">Référence Structure <span class="required">*</span></label>
                            <select class="input-field" id="structure" name="structure" required>
                                <option value="">-- Sélectionnez une option --</option>
                                <?php foreach ($structures as $structure): ?>
                                    <option value="<?= htmlspecialchars($structure['id']) ?>"
                                        <?= ($structure['id'] == $commande['id_structure']) ? 'selected' : '' ?>
                                        data-nb-longueurs="<?= ($structure['nb_longueurs']) ?>">
                                        <?= htmlspecialchars($structure['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="longueurA">Longueur banquette A <span class="required">*</span></label>
                            <input type="number" id="longueurA" name="longueurA" class="input-field" value="<?php echo htmlspecialchars($commande['longueurA']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="longueurB">Longueur banquette B <span class="required">*</span></label>
                            <input type="number" id="longueurB" name="longueurB" class="input-field" value="<?php echo htmlspecialchars($commande['longueurB']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="longueurC">Longueur banquette C <span class="required">*</span></label>
                            <input type="number" id="longueurC" name="longueurC" class="input-field" value="<?php echo htmlspecialchars($commande['longueurC']); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mousse">Référence Mousse <span class="required">*</span></label>
                            <select class="input-field" id="mousse" name="mousse" required>
                                <option value="">-- Sélectionnez une option --</option>
                                <?php foreach ($mousses as $mousse): ?>
                                    <option value="<?= htmlspecialchars($mousse['id']) ?>"
                                        <?= ($mousse['id'] == $commande['id_mousse']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($mousse['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="banquette">Référence Type banquette <span class="required">*</span></label>
                            <select class="input-field" id="banquette" name="banquette" required>
                                <option value="">-- Sélectionnez une option --</option>
                                <?php foreach ($banquettes as $banquette): ?>
                                    <option value="<?= htmlspecialchars($banquette['id']) ?>"
                                        <?= ($banquette['id'] == $commande['id_banquette']) ? 'selected' : '' ?>
                                        data-type="<?= htmlspecialchars($banquette['nom']) ?>">
                                        <?= htmlspecialchars($banquette['nom']) ?>
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


                            <!-- <div class="form-group">
                                <label for="nb_accoudoir_bois">Nombre d'accoudoirs <span class="required">*</span></label>
                                <select id="nb_accoudoir_bois" name="nb_accoudoir_bois" class="input-field">
                                    <option value="">-- Sélectionnez une option --</option>
                                    <option value="0" <?= (isset($commande['nb_accoudoir_bois']) && $commande['nb_accoudoir_bois'] == 0) ? 'selected' : '' ?>>0</option>
                                    <option value="1" <?= (isset($commande['nb_accoudoir_bois']) && $commande['nb_accoudoir_bois'] == 1) ? 'selected' : '' ?>>1</option>
                                    <option value="2" <?= (isset($commande['nb_accoudoir_bois']) && $commande['nb_accoudoir_bois'] == 2) ? 'selected' : '' ?>>2</option>
                                </select>
                            </div>
                        </div> -->


                            <div class="form-group">
                                <label for="accoudoir_gauche">Accoudoir Gauche - bois</label>
                                <select id="accoudoir_gauche" name="accoudoir_gauche" class="input-field">
                                    <option value="">-- Sélectionnez une option --</option>
                                    <?php foreach ($accoudoirsbois as $item): ?>
                                        <option value="<?= $item['id'] ?>"
                                            <?= (isset($accoudoir1) && (string)$accoudoir1 === (string)$item['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($item['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group" id="accoudoir2-group">
                                <label for="accoudoir_droit">Accoudoir Droit - bois</label>
                                <select id="accoudoir_droit" name="accoudoir_droit" class="input-field">
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
                    <div class="button-section">
                        <div class="buttons">
                            <button type="button" id="btn-retour" class="btn-beige" onclick="history.go(-1)">Retour</button>
                            <input type="submit" class="btn-noir" value="Mettre à jour"></input>
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

</html