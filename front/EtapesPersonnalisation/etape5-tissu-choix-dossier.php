<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
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
    <script type="module" src="../../script/popup.js"></script>
    <script type="module" src="../../script/variationPrix.js"></script>
    <script src="../../script/reset.js"></script>

    <title>Étape 5 - Choisi ton dossier</title>
    <style>
        /* Transition pour les éléments de la page */
        .transition {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }

        .transition.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Appliquer les transitions aux images sélectionnées */
        .option img.selected {
            border: 3px solid #997765;
            /* Couleur marron */
            border-radius: 5px;
            box-sizing: border-box;
        }
    </style>
</head>

<body data-user-id="<?php echo $_SESSION['user_id']; ?>" data-current-step="5-dossier-tissu">

    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>

    <main>
        <div class="fil-ariane-container" aria-label="fil-ariane">
            <ul class="fil-ariane">
                <li><a href="etape1-1-structure.php">Structure</a></li>
                <li><a href="etape1-2-dimension.php">Dimension</a></li>
                <li><a href="etape2-type-banquette.php">Banquette</a></li>
                <li><a href="etape3-tissu-modele-banquette.php">Modèle</a></li>
                <li><a href="etape4-1-tissu-choix-tissu.php">Tissu</a></li>
                <li><a href="etape5-tissu-choix-dossier.php" class="active">Dossier</a></li>
                <li><a href="etape6-tissu-accoudoir.php">Accoudoir</a></li>
                <li><a href="etape7-tissu-choix-mousse.php">Mousse</a></li>
            </ul>
        </div>

        <div class="container">
            <!-- Colonne de gauche -->
            <div class="left-column transition">
                <h2>Étape 5 - Choisi ton dossier</h2>
                <section class="color-2options">
                    <?php if (!empty($dossier_tissu)): ?>
                        <?php foreach ($dossier_tissu as $tissu): ?>
                            <div class="option transition">
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
                    <p>Total : <span>899 €</span></p>
                    <div class="buttons">
                    <button onclick="retourEtapePrecedente()" class="btn-retour transition">Retour</button>
                    <form method="POST" action="">
                            <input type="hidden" name="dossier_tissu_id" id="selected-dossier_tissu">
                            <button type="submit" class="btn-suivant transition">Suivant</button>
                        </form>
                    <button id="reset-selection" class="btn-reset transition">Réinitialiser</button>
                    </div>
                </div>
            </div>

            <!-- Colonne de droite -->
            <div class="right-column transition">
                <section class="main-display">
                    <div class="buttons transition">
                        <button class="btn-aide">Besoin d'aide ?</button>
                        <button class="btn-abandonner">Abandonner</button>
                    </div>
                    <img src="../../medias/process-main-image.png" alt="Armoire" class="transition">
                </section>
            </div>
        </div>

        <!-- Popup besoin d'aide -->
        <div id="help-popup" class="popup transition">
            <div class="popup-content">
                <h2>Vous avez une question ?</h2>
                <p>Contactez nous au numéro suivant et un vendeur vous assistera :
                    <br><br>
                    <strong>06 58 47 58 56</strong>
                </p>
                <br>
                <button class="close-btn">Merci !</button>
            </div>
        </div>

        <!-- Popup abandonner -->
        <div id="abandonner-popup" class="popup transition">
            <div class="popup-content">
                <h2>Êtes vous sûr de vouloir abandonner ?</h2>
                <br>
                <button class="yes-btn">Oui ...</button>
                <button class="no-btn">Non !</button>
            </div>
        </div>

      
<!-- Popup d'erreur si option non selectionné -->
<div id="erreur-popup" class="popup transition">
      <div class="popup-content">
        <h2>Veuillez choisir une option avant de continuer.</h2>
        <button class="close-btn">OK</button>
      </div>
      </div>
        
        <!-- GESTION DES SELECTIONS -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const options = document.querySelectorAll('.color-2options .option img'); // Sélectionne toutes les images
                const selectedDossierTissuInput = document.getElementById('selected-dossier_tissu');
                const mainImage = document.querySelector('.main-display img');
                const erreurPopup = document.getElementById('erreur-popup');
                const closeErreurBtn = erreurPopup.querySelector('.close-btn');
                const form = document.querySelector('form'); // Assure-toi que ton <form> a bien une balise identifiable

                // Vérification si une sélection existe dans localStorage
                let savedDossierTissuId = localStorage.getItem('selectedDossierTissuId');
                let selected = savedDossierTissuId!=='';

                // Affichage des éléments avec la classe "transition"
                document.querySelectorAll('.transition').forEach(element => {
                    element.classList.add('show');
                });   
                
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

                
              /*  if (savedDossierTissuId) {
                    options.forEach(img => {
                        if (img.getAttribute('data-dossier-tissu-id') === savedDossierTissuId) {
                            img.classList.add('selected');
                            mainImage.src = img.src;
                            mainImage.alt = img.alt;
                            selectedDossierTissuInput.value = savedDossierTissuId;
                            selected = true;
                        }
                    });
                }

                // Gestion de la sélection des images
                options.forEach(img => {
                    img.addEventListener('click', () => {
                        // Retirer la classe "selected" de toutes les images
                        options.forEach(opt => opt.classList.remove('selected'));

                        // Ajouter la classe "selected" à l'image cliquée
                        img.classList.add('selected');

                        // Mettre à jour l'image principale
                        mainImage.src = img.src;
                        mainImage.alt = img.alt;

                        // Mettre à jour l'input caché avec l'ID du tissu sélectionné
                        selectedDossierTissuInput.value = img.getAttribute('data-dossier-tissu-id');
                        selected = true; // Marquer comme sélectionné

                        saveSelection(img.getAttribute('data-dossier-tissu-id'));
                    });
                });*/

                form.addEventListener('submit', (e) => {
      if (!selectedDossierTissuInput.value) {
        e.preventDefault();
        erreurPopup.style.display = 'flex'; // ou 'block' selon ton CSS
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
              

                function saveSelection(dossierTissuId) {
                    localStorage.setItem('selectedDossierTissuId', savedDossierTissuId);
                }
            });
        </script>

  
      <!-- BOUTTON RETOUR -->
      <script>
       function retourEtapePrecedente() {
    // Exemple : tu es sur étape 8, tu veux revenir à étape 7
    window.location.href = "etape4-2-tissu-choix-tissu-coussin.php"; 
  }
    </script>
        



    </main>

    <?php require_once '../../squelette/footer.php' ?>

</body>

</html>