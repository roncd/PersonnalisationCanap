<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
  header("Location: ../formulaire/Connexion.php");
  exit;
}

// Récupérer l'ID du client
$id_client = $_SESSION['user_id'];

// Récupérer les détails de la commande du client
$stmt = $pdo->prepare("SELECT * FROM commande_detail WHERE id_client = ?");
$stmt->execute([$id_client]);
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer le prix total de la commande
$stmt_price = $pdo->prepare("SELECT prix FROM commande_detail WHERE id_client = ?");
$stmt_price->execute([$id_client]);
$commandePrice = $stmt_price->fetch(PDO::FETCH_ASSOC);

// Vérifier si le prix total existe
if ($commandePrice) {
  $totalPrice = $commandePrice['prix'];
} else {
  $totalPrice = 0; // Si aucune commande n'est trouvée
}

// Liste des champs à récupérer avec leurs tables associées
$elements = [
  'id_structure' => 'structure',
  'id_banquette' => 'type_banquette',
  'id_mousse' => 'mousse',
  'id_couleur_bois' => 'couleur_bois',
  'id_decoration' => 'decoration',
  'id_accoudoir_bois' => 'accoudoir_bois',
  'id_dossier_bois' => 'dossier_bois',
  'id_couleur_tissu_bois' => 'couleur_tissu_bois',
  'id_motif_bois' => 'motif_bois',
  'id_modele' => 'modele',
  'id_couleur_tissu' => 'couleur_tissu',
  'id_motif_tissu' => 'motif_tissu',
  'id_dossier_tissu' => 'dossier_tissu',
  'id_accoudoir_tissu' => 'accoudoir_tissu',
  'id_nb_accoudoir' => 'nb_accoudoir'
];

// Récupérer les informations de chaque élément sélectionné
$composition = [];

foreach ($elements as $champ => $table) {
  if (!empty($commande[$champ])) {
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
    $stmt->execute([$commande[$champ]]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
      $composition[$table] = $result;
    }
  }
}

// Récupérer les dimensions de la commande
// Vérifier si la valeur existe avant de l'afficher
$longueurA = isset($commande['longueurA']) ? $commande['longueurA'] : 'Non spécifiée';
$longueurB = isset($commande['longueurB']) ? $commande['longueurB'] : 'Non spécifiée';
$longueurC = isset($commande['longueurC']) ? $commande['longueurC'] : 'Non spécifiée';


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
  <title>Étape 3 - Choisi ta couleur</title>
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

    .container {
      display: flex;
      flex-direction: row-reverse;
      /* Inverse l'ordre des colonnes */

    }

    /* Style général pour la zone contenant les dimensions et autres éléments */
    .option-l {
      background-color: #f5f5dc;
      /* Couleur beige */
      border-radius: 15px;
      /* Bords arrondis */
      padding: 20px;
      /* Un peu de marge à l'intérieur */
      margin-bottom: 10px;
      /* Espacement entre les options */
      display: flex;
      /* Flexbox pour centrer le contenu */
      justify-content: center;
      /* Centrer horizontalement */
      align-items: center;
      /* Centrer verticalement */
      text-align: center;
      /* Centrer le texte */
      height: 150px;
      /* Hauteur du carré */
      width: 200px;
      /* Largeur du carré */
    }

    /* Si tu veux un style supplémentaire pour le texte */
    .option-l p {
      font-size: 16px;
      /* Taille de la police */
      font-weight: bold;
      /* Mettre le texte en gras */
      color: #333;
      /* Couleur du texte */
    }
  </style>


</head>

<body>


  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>


  <main>


    <div class="container">
      <!-- Colonne de gauche -->
      <div class="left-column">
        <h2>Composition du Salon Kénitra </h2>
        <section class="color-options">
          <!-- Afficher Longueur A uniquement si elle a une valeur -->
          <?php if (!empty($commande['longueurA'])): ?>
            <div class="option">
              <p><strong>Longueur A :</strong> <?php echo htmlspecialchars($commande['longueurA']); ?> cm</p>
            </div>
          <?php endif; ?>

          <?php if (!empty($commande['longueurB'])): ?>
            <div class="option">
              <p><strong>Longueur B :</strong> <?php echo htmlspecialchars($commande['longueurB']); ?> cm</p>
            </div>
          <?php endif; ?>

          <?php if (!empty($commande['longueurC'])): ?>
            <div class="option">
              <p><strong>Longueur C :</strong> <?php echo htmlspecialchars($commande['longueurC']); ?> cm</p>
            </div>
          <?php endif; ?>
          <br>

          <!-- Afficher la composition des autres éléments -->
          <?php if (!empty($composition)): ?>
            <?php foreach ($composition as $nomTable => $details): ?>
              <div class="option">
                <?php if (!empty($details['img'])): ?>
                  <img src="../../admin/uploads/<?php echo htmlspecialchars($nomTable) . '/' . htmlspecialchars($details['img']); ?>"
                    alt="<?php echo htmlspecialchars($details['nom'] ?? ''); ?>"
                    data-id="<?php echo htmlspecialchars($details['id'] ?? ''); ?>">
                <?php endif; ?>
                <p><?php echo htmlspecialchars($details['nom'] ?? $details['valeur'] ?? 'Non spécifié'); ?></p>
                <?php if (!empty($details['prix'])): ?>
                  <p><strong><?php echo htmlspecialchars($details['prix']); ?> €</strong></p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Aucune composition sélectionnée.</p>
          <?php endif; ?>
        </section>


        <div class="footer">
          <p>Total : <span id="total-price"><?php echo number_format($totalPrice, 2, ',', ' '); ?> €</span></p>
          <div class="buttons">
            <button class="btn-retour " onclick="history.go(-1)">Retour</button>
            <form method="POST" action="">
              <input type="hidden" name="couleur_bois_id" id="selected-couleur_bois">
              <button type="submit" class="btn-suivant">Personnaliser</button>
            </form>
          </div>
        </div>
      </div>


      <!-- Colonne de droite -->
      <div class="right-column ">
        <section class="main-display2">
          <img src="../../medias/meknes.png" alt="Armoire" class="">
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


    <div id="selection-popup" class="popup transition">
      <div class="popup-content">
        <h2>Veuillez choisir une option avant de continuer.</h2>
        <br>
        <button class="close-btn">OK</button>
      </div>
    </div>


    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const options = document.querySelectorAll('.color-options .option img');
        const mainImage = document.querySelector('.main-display img');
        const suivantButton = document.querySelector('.btn-suivant');
        const helpPopup = document.getElementById('help-popup');
        const abandonnerPopup = document.getElementById('abandonner-popup');
        const selectionPopup = document.getElementById('selection-popup');
        const selectedCouleurBoisInput = document.getElementById('selected-couleur_bois');
        const totalPriceElement = document.getElementById('total-price'); // Élément du prix total


        let selectedBoisId = localStorage.getItem('selectedCouleurBois') || '';
        let selected = selectedBoisId !== '';
        let totalPrice = <?php echo $totalPrice; ?>; // Initialisation du prix à partir de PHP

        document.querySelectorAll('.transition').forEach(element => {
          element.classList.add('show');
        });



        totalPriceElement.textContent = totalPrice + ' €';

        // Restaurer la sélection si elle existe
        options.forEach(img => {
          if (img.getAttribute('data-bois-id') === selectedBoisId) {
            img.classList.add('selected');
            mainImage.src = img.src;
            selectedCouleurBoisInput.value = selectedBoisId;
          }
        });

        options.forEach(img => {
          img.addEventListener('click', () => {
            options.forEach(opt => opt.classList.remove('selected'));
            img.classList.add('selected');
            mainImage.src = img.src;
            selectedBoisId = img.getAttribute('data-bois-id');
            selectedCouleurBoisInput.value = selectedBoisId;
            selected = true;
            saveSelection();
          });
        });

        suivantButton.addEventListener('click', (event) => {
          if (!selected) {
            event.preventDefault();
            selectionPopup.style.display = 'flex';
          }
        });

        document.querySelector('#selection-popup .close-btn').addEventListener('click', () => {
          selectionPopup.style.display = 'none';
        });

        window.addEventListener('click', (event) => {
          if (event.target === selectionPopup) {
            selectionPopup.style.display = 'none';
          }
        });

        document.querySelector('.btn-aide').addEventListener('click', () => {
          helpPopup.style.display = 'flex';
        });

        document.querySelector('#help-popup .close-btn').addEventListener('click', () => {
          helpPopup.style.display = 'none';
        });

        window.addEventListener('click', (event) => {
          if (event.target === helpPopup) {
            helpPopup.style.display = 'none';
          }
        });

        document.querySelector('.btn-abandonner').addEventListener('click', () => {
          abandonnerPopup.style.display = 'flex';
        });

        document.querySelector('#abandonner-popup .yes-btn').addEventListener('click', () => {
          window.location.href = '../pages/';
        });

        document.querySelector('#abandonner-popup .no-btn').addEventListener('click', () => {
          abandonnerPopup.style.display = 'none';
        });

        function saveSelection() {
          localStorage.setItem('selectedCouleurBois', selectedBoisId);
        }
      });
    </script>

  </main>


  <?php require_once '../../squelette/footer.php'; ?>


</body>

</html>