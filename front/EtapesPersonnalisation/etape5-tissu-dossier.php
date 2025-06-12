<?php
require '../../admin/config.php';
session_start();
require '../../admin/include/session_expiration.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../formulaire/Connexion.php");
    exit;
}

// Récupérer les types de dossier tissu depuis la base de données
$stmt = $pdo->query("SELECT * FROM dossier_tissu");
$dossier_tissu = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_client = $_SESSION['user_id'];
    $id_dossier_tissu = $_POST['dossier_tissu_id'];

    // Vérifier si une commande temporaire existe déjà pour cet utilisateur
    $stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ?");
    $stmt->execute([$id_client]);
    $existing_order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_order) {
        $stmt = $pdo->prepare("UPDATE commande_temporaire SET id_dossier_tissu = ? WHERE id_client = ?");
        $stmt->execute([$id_dossier_tissu, $id_client]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO commande_temporaire (id_client, id_dossier_tissu) VALUES (?, ?)");
        $stmt->execute([$id_client, $id_dossier_tissu]);
    }

    // Rediriger vers l'étape suivante
    header("Location: etape6-tissu-accoudoir.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/processus.css">
    <link rel="stylesheet" href="../../styles/popup.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <script type="module" src="../../script/popup.js"></script>
    <script type="module" src="../../script/variationPrix.js"></script>
    <script type="module" src="../../script/keydown.js"></script>

    <title>Étape 5 - Choisi ton dossier</title>

</head>

<body data-user-id="<?php echo $_SESSION['user_id']; ?>" data-current-step="5-dossier-tissu">

    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>

    <main>
        <div class="fil-ariane-container h2" aria-label="fil-ariane">
            <ul class="fil-ariane">
                <li><a href="etape1-1-structure.php">Structure</a></li>
                <li><a href="etape1-2-dimension.php">Dimension</a></li>
                <li><a href="etape2-type-banquette.php">Banquette</a></li>
                <li><a href="etape3-tissu-modele-banquette.php">Modèle</a></li>
                <li><a href="etape4-1-tissu-tissu.php">Tissu</a></li>
                <li><a href="etape5-tissu-dossier.php" class="active">Dossier</a></li>
                <li><a href="etape6-tissu-accoudoir.php">Accoudoir</a></li>
                <li><a href="etape7-tissu-mousse.php">Mousse</a></li>
            </ul>
        </div>

        <div class="container transition">
            <!-- Colonne de gauche -->
            <div class="left-column ">
                <h2>Étape 5 - Choisi ton dossier</h2>
                <section class="color-2options">
                    <?php if (!empty($dossier_tissu)): ?>
                        <?php foreach ($dossier_tissu as $tissu): ?>
                            <div class="option ">
                                <img src="../../admin/uploads/dossier-tissu/<?php echo htmlspecialchars($tissu['img']); ?>"
                                    alt="<?php echo htmlspecialchars($tissu['nom']); ?>"
                                    data-dossier-tissu-id="<?php echo $tissu['id']; ?>"
                                    data-dossier-tissu-prix="<?php echo $tissu['prix']; ?>">
                                <p><?php echo htmlspecialchars($tissu['nom']); ?></p>
                                <p><strong><?php echo htmlspecialchars($tissu['prix']); ?> €</strong></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun dossier disponible pour le moment.</p>
                    <?php endif; ?>
                </section>

                <div class="footer">
                    <p>Total : <span>0 €</span></p>
                    <div class="buttons">
                        <button onclick="retourEtapePrecedente()" class="btn-beige  ">Retour</button>
                        <form method="POST" action="">
                            <input type="hidden" name="dossier_tissu_id" id="selected-dossier_tissu">
                            <button type="submit" id="btn-suivant" class="btn-noir">Suivant</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Colonne de droite -->
            <div class="right-column ">
                <section class="main-display">
                    <div class="buttons ">
                        <button id="btn-aide" class="btn-beige">Besoin d'aide ?</button>
                        <button type="button" data-url="../pages/dashboard.php" id="btn-abandonner" class="btn-noir">Abandonner</button>
                    </div>
                    <img src="../../medias/process-main-image.png" alt="Armoire">
                </section>
            </div>
        </div>

        <!-- Popup besoin d'aide -->
        <div id="help-popup" class="popup ">
            <div class="popup-content">
                <h2>Vous avez une question ?</h2>
                <p>Contactez nous au numéro suivant et un vendeur vous assistera :
                    <br><br>
                    <strong>06 58 47 58 56</strong>
                </p>
                <br>
                <button class="btn-noir">Merci !</button>
            </div>
        </div>

        <!-- Popup abandonner -->
        <div id="abandonner-popup" class="popup ">
            <div class="popup-content">
                <h2>Êtes vous sûr de vouloir abandonner ?</h2>
                <br>
                <button class="btn-beige">Oui...</button>
                <button class="btn-noir">Non !</button>
            </div>
        </div>

        <!-- Popup d'erreur si option non selectionné -->
        <div id="erreur-popup" class="popup ">
            <div class="popup-content">
                <h2>Veuillez choisir une option avant de continuer.</h2>
                <button class="btn-noir">OK</button>
            </div>
        </div>

        <!-- GESTION DES SELECTIONS -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const options = document.querySelectorAll('.color-2options .option img'); // Sélectionne toutes les images
                const selectedDossierTissuInput = document.getElementById('selected-dossier_tissu');
                const mainImage = document.querySelector('.main-display img');
                const erreurPopup = document.getElementById('erreur-popup');
                const closeErreurBtn = erreurPopup.querySelector('.btn-noir');
                const form = document.querySelector('form');

                // Vérification si une sélection existe dans localStorage
                let savedDossierTissuId = localStorage.getItem('selectedDossierTissuId');
                let selected = savedDossierTissuId !== '';

                function saveSelection(dossierTissuId) {
                    localStorage.setItem('selectedDossierTissuId', savedDossierTissuId);
                }

                // Restaurer la sélection si elle existe
                options.forEach(img => {
                    if (img.getAttribute('data-dossier-tissu-id') === savedDossierTissuId) {
                        img.classList.add('selected');
                        mainImage.src = img.src;
                        selectedDossierTissuInput.value = savedDossierTissuId;
                    }
                });

                // Gestion du clic sur une option
                options.forEach(img => {
                    img.addEventListener('click', () => {
                        options.forEach(opt => opt.classList.remove('selected'));
                        img.classList.add('selected');
                        mainImage.src = img.src;
                        savedDossierTissuId = img.getAttribute('data-dossier-tissu-id');
                        selectedDossierTissuInput.value = savedDossierTissuId;
                        selected = true;
                        saveSelection();
                    });
                });

                form.addEventListener('submit', (e) => {
                    if (!selectedDossierTissuInput.value) {
                        e.preventDefault();
                        erreurPopup.style.display = 'flex';
                    }
                });

                // Fermer le popup
                closeErreurBtn.addEventListener('click', () => {
                    erreurPopup.style.display = 'none';
                });

                window.addEventListener('click', (event) => {
                    if (event.target === erreurPopup) {
                        erreurPopup.style.display = 'none';
                    }
                });
            });
        </script>

        <!-- BOUTTON RETOUR -->
        <script>
            function retourEtapePrecedente() {
                window.location.href = "etape4-2-tissu-choix-tissu-coussin.php";
            }
        </script>

    </main>

    <?php require_once '../../squelette/footer.php' ?>

</body>

</html>