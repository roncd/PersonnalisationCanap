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

    if (empty($prix) || empty($prixDimensions) || empty($longueurA)) {
        $_SESSION['message'] = 'Les champs obligatoires doivent être remplis.';
        $_SESSION['message_type'] = 'error';
    } 

    try {
    $stmt = $pdo->prepare("INSERT INTO commande_prefait (
        prix, prix_dimensions, id_structure, longueurA, longueurB, longueurC,
        id_banquette, id_mousse, id_couleur_bois, id_decoration,
        id_accoudoir_bois, id_dossier_bois, id_couleur_tissu_bois, id_motif_bois,
        id_modele, id_couleur_tissu, id_motif_tissu, id_dossier_tissu, id_accoudoir_tissu,
        id_nb_accoudoir, nom
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $prix, $prixDimensions, $idStructure, $longueurA, $longueurB, $longueurC,
        $idBanquette, $idMousse, $idCouleurBois, $idDecoration,
        $idAccoudoirBois, $idDossierBois, $idTissuBois, $idMotifBois,
        $idModele, $idCouleurTissu, $idMotifTissu, $idDossierTissu, $idAccoudoirTissu,
        $nbAccoudoir, $nom
    ]);
$_SESSION['message'] = 'La commande préfaite a été ajoutée avec succès.';
$_SESSION['message_type'] = 'success';
header('Location: visualiser.php'); // ou vers la page souhaitée
exit();
} catch (Exception $e) {
           $_SESSION['message'] = 'Erreur lors de l\'ajout du canapé pré personnaliser: ' . $e->getMessage();
           $_SESSION['message_type'] = 'error';
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
    <form class="formulaire-creation-compte" action="" method="POST">
        <div class="form-row">
            <div class="form-group">
                <label for="nom">Nom de la commande</label>
                <input type="text" id="nom" name="nom" class="input-field">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="prix">Prix (€)</label>
                <input type="number" step="0.01" id="prix" name="prix" class="input-field" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="prix_dimensions">Prix dimensions (€)</label>
                <input type="number" step="0.01" id="prix_dimensions" name="prix_dimensions" class="input-field" required>
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

        <!-- Sélections dynamiques (exemple pour structure) -->
        <div class="form-row">
            <div class="form-group">
                <label for="structure">Structure</label>
                <select id="structure" name="structure" class="input-field" required>
                    <option value="">-- Choisir une structure --</option>
                    <?php foreach ($structures as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nom']) ?></option>
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
                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nom']) ?></option>
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
                        <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>


<!-- Couleur bois -->
<div class="form-row">
<div class="form-group">
    <label for="couleurbois">Couleur du bois</label>
    <select id="couleurbois" name="couleurbois" class="input-field">
        <option value="">-- Choisir --</option>
        <?php foreach ($couleursbois as $item): ?>
            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
</div>


<!-- Décoration -->
<div class="form-row">
<div class="form-group">
    <label for="decoration">Décoration</label>
    <select id="decoration" name="decoration" class="input-field">
        <option value="">-- Choisir --</option>
        <?php foreach ($decorations as $item): ?>
            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
</div>


<!-- Accoudoir bois -->
<div class="form-row">
<div class="form-group">
    <label for="accoudoirbois">Accoudoir bois</label>
    <select id="accoudoirbois" name="accoudoirbois" class="input-field">
        <option value="">-- Choisir --</option>
        <?php foreach ($accoudoirsbois as $item): ?>
            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
</div>


<!-- Dossier bois -->
<div class="form-row">
<div class="form-group">
    <label for="dossierbois">Dossier bois</label>
    <select id="dossierbois" name="dossierbois" class="input-field">
        <option value="">-- Choisir --</option>
        <?php foreach ($dossiersbois as $item): ?>
            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
</div>


<!-- Couleur tissu bois -->
<div class="form-row">
<div class="form-group">
    <label for="couleurtissubois">Couleur tissu (bois)</label>
    <select id="couleurtissubois" name="couleurtissubois" class="input-field">
        <option value="">-- Choisir --</option>
        <?php foreach ($couleurstissubois as $item): ?>
            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
</div>


<!-- Motif bois -->
<div class="form-row">
<div class="form-group">
    <label for="motifbois">Motif bois</label>
    <select id="motifbois" name="motifbois" class="input-field">
        <option value="">-- Choisir --</option>
        <?php foreach ($motifsbois as $item): ?>
            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
</div>


<!-- Modèle -->
<div class="form-row">
<div class="form-group">
    <label for="modele">Modèle</label>
    <select id="modele" name="modele" class="input-field">
        <option value="">-- Choisir --</option>
        <?php foreach ($modeles as $item): ?>
            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
</div>


<!-- Couleur tissu -->
<div class="form-row">
<div class="form-group">
    <label for="couleurtissu">Couleur tissu</label>
    <select id="couleurtissu" name="couleurtissu" class="input-field">
        <option value="">-- Choisir --</option>
        <?php foreach ($couleurstissu as $item): ?>
            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
</div>


<!-- Motif tissu -->
<div class="form-row">
<div class="form-group">
    <label for="motiftissu">Motif tissu</label>
    <select id="motiftissu" name="motiftissu" class="input-field">
        <option value="">-- Choisir --</option>
        <?php foreach ($motifstissu as $item): ?>
            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
</div>


<!-- Accoudoir tissu -->
<div class="form-row">
<div class="form-group">
    <label for="accoudoirtissu">Accoudoir tissu</label>
    <select id="accoudoirtissu" name="accoudoirtissu" class="input-field">
        <option value="">-- Choisir --</option>
        <?php foreach ($accoudoirstissu as $item): ?>
            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
</div>


<!-- Dossier tissu -->
<div class="form-row">
<div class="form-group">
    <label for="dossiertissu">Dossier tissu</label>
    <select id="dossiertissu" name="dossiertissu" class="input-field">
        <option value="">-- Choisir --</option>
        <?php foreach ($dossierstissu as $item): ?>
            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nom']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
</div>
    
        <div class="form-row">
            <div class="form-group">
                <label for="nb_accoudoir">Nombre d'accoudoirs</label>
                <input type="number" id="nb_accoudoir" name="nb_accoudoir" class="input-field">
            </div>
        </div>

        <div class="form-row">
            <button type="submit" class="btn">Ajouter</button>
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