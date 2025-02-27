<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../formulaire/Connexion.php");
    exit;
}

// Récupérer les types d'accoudoirs depuis la base de données
$stmt = $pdo->query("SELECT * FROM accoudoir_bois");
$accoudoir_bois = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['accoudoir_bois_id']) || empty($_POST['accoudoir_bois_id']) || !isset($_POST['nb_accoudoir']) || empty($_POST['nb_accoudoir'])) {
      echo "Erreur : Aucun accoudoir ou quantité sélectionné.";
      exit;
  }

  $id_client = $_SESSION['user_id'];
  $id_accoudoirs = explode(',', $_POST['accoudoir_bois_id']);
  $nb_accoudoirs = explode(',', $_POST['nb_accoudoir']);

  // Vérifier si une commande temporaire existe
  $stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ?");
  $stmt->execute([$id_client]);
  $commande = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$commande) {
      // Créer une nouvelle commande temporaire
      $stmt = $pdo->prepare("INSERT INTO commande_temporaire (id_client) VALUES (?)");
      $stmt->execute([$id_client]);
      $commande_id = $pdo->lastInsertId();
  } else {
      $commande_id = $commande['id'];
      
      // Supprimer les anciennes entrées de la table pivot
      $stmt = $pdo->prepare("DELETE FROM commande_temp_accoudoir WHERE id_commande_temporaire = ?");
      $stmt->execute([$commande_id]);
  }

  // Insérer les nouveaux accoudoirs sélectionnés
  $stmt = $pdo->prepare("INSERT INTO commande_temp_accoudoir (id_commande_temporaire, id_accoudoir_bois, nb_accoudoir) VALUES (?, ?, ?)");
  
  foreach ($id_accoudoirs as $index => $id_accoudoir) {
      $nb = (int) $nb_accoudoirs[$index];
      if ($nb > 0) {
          $stmt->execute([$commande_id, $id_accoudoir, $nb]);
      }
  }

  // Rediriger vers l'étape suivante
  header("Location: etape6-bois-dossier.php");
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
  <title>Étape 5 - Ajoute tes accoudoirs</title>
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
      border: 3px solid #997765; /* Couleur marron */
      border-radius: 5px;
      box-sizing: border-box;
    }
  </style>
</head>
<body>

<header>
  <?php require '../../squelette/header.php'; ?>
</header>

<main>
<div class="fil-ariane-container" aria-label="fil-ariane">
  <ul class="fil-ariane">
    <li><a href="etape1-1-structure.php">Structure</a></li>
    <li><a href="etape1-2-dimension.php">Dimension</a></li>
    <li><a href="etape2-type-banquette.php">Banquette</a></li>
    <li><a href="etape3-bois-couleur.php">Couleur</a></li>
    <li><a href="etape4-bois-decoration.php">Décoration</a></li>
    <li><a href="etape5-bois-accoudoir.php" class="active">Accoudoirs</a></li>
    <li><a href="etape6-bois-dossier.php">Dossier</a></li>
    <li><a href="etape7-bois-mousse.php">Mousse</a></li>
    <li><a href="etape8-1-bois-tissu.php">Tissu</a></li>
  </ul>
</div><div class="container">
  <!-- Colonne de gauche -->
  <div class="left-column transition">
    <h2>Étape 5 - Ajoute tes accoudoirs</h2>
    
    <section class="color-options">
        <?php if (!empty($accoudoir_bois)): ?>
          <?php foreach ($accoudoir_bois as $bois): ?>
            <div class="option transition">
              <img src="../../admin/uploads/accoudoirs-bois/<?php echo htmlspecialchars($bois['img']); ?>"
                   alt="<?php echo htmlspecialchars($bois['nom']); ?>"
                   data-bois-id="<?php echo $bois['id']; ?>"
                   data-bois-prix="<?php echo $bois['prix']; ?>">
              <p><?php echo htmlspecialchars($bois['nom']); ?></p>
              <p><strong><?php echo htmlspecialchars($bois['prix']); ?> €</strong></p>
             <!-- Compteur de quantité -->
             <div class="quantity-selector1">
              <button class="btn-decrease" onclick="updateQuantity(this, -1)">-</button>
              <input type="text" class="quantity-input1" value="0" readonly>
              <button class="btn-increase" onclick="updateQuantity(this, 1)">+</button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>Aucun accoudoir disponible pour le moment.</p>
      <?php endif; ?>
    </section>

      <div class="footer">
        <p>Total : <span>899 €</span></p>
        <div class="buttons">
          <button class="btn-retour transition" onclick="history.go(-1)">Retour</button>
          <form method="POST" action="">
          <input type="hidden" name="accoudoir_bois_id" id="selected-accoudoir_bois"> 
          <input type="hidden" name="nb_accoudoir" id="selected-nb_accoudoir" required>
          <button type="submit" class="btn-suivant transition">Suivant</button>
          </form>
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
      <strong>06 58 47 58 56</strong></p>
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
    const selectedAccoudoirBoisInput = document.getElementById('selected-accoudoir_bois');
    const selectedNbAccoudoirInput = document.getElementById('selected-nb_accoudoir');
    let selectedOptions = JSON.parse(localStorage.getItem('selectedOptions')) || {};

    // Afficher la transition des éléments
    document.querySelectorAll('.transition').forEach(element => {
      element.classList.add('show');
    });

    // Restaurer les sélections depuis localStorage
    options.forEach(img => {
      const boisId = img.getAttribute('data-bois-id');
      const parentOption = img.closest('.option');
      let quantityInput = parentOption.querySelector('.quantity-input1');

      if (selectedOptions[boisId]) {
        img.classList.add('selected');
        quantityInput.value = selectedOptions[boisId];
      }
    });

    updateHiddenInputs();

    // Sélectionner un accoudoir
    options.forEach(img => {
      img.addEventListener('click', () => {
        const boisId = img.getAttribute('data-bois-id');
        const parentOption = img.closest('.option');
        let quantityInput = parentOption.querySelector('.quantity-input1');

        if (selectedOptions[boisId]) {
          delete selectedOptions[boisId]; // Désélectionner
          img.classList.remove('selected');
          quantityInput.value = 0;
        } else {
          selectedOptions[boisId] = 1; // Ajouter avec quantité 1 par défaut
          img.classList.add('selected');
          quantityInput.value = 1;
        }

        updateHiddenInputs();
        saveSelection();
      });
    });

    // Mettre à jour la quantité
    document.querySelectorAll('.btn-increase, .btn-decrease').forEach(button => {
      button.addEventListener('click', (event) => {
        const parentOption = event.target.closest('.option');
        const boisId = parentOption.querySelector('img').getAttribute('data-bois-id');
        let quantityInput = parentOption.querySelector('.quantity-input1');

        if (!selectedOptions[boisId]) return; // Si non sélectionné, ne rien faire

        let newValue = parseInt(quantityInput.value, 10) + (event.target.classList.contains('btn-increase') ? 1 : -1);
        newValue = Math.max(newValue, 0); // Empêcher d'aller sous 0
        quantityInput.value = newValue;

        if (newValue === 0) {
          delete selectedOptions[boisId]; // Supprimer si quantité = 0
          parentOption.querySelector('img').classList.remove('selected');
        } else {
          selectedOptions[boisId] = newValue;
        }

        updateHiddenInputs();
        saveSelection();
      });
    });

    // Vérifier la sélection avant de passer à l'étape suivante
    suivantButton.addEventListener('click', (event) => {
      if (Object.keys(selectedOptions).length === 0 || !selectedNbAccoudoirInput.value || selectedNbAccoudoirInput.value === "0") {
        event.preventDefault();
        selectionPopup.style.display = 'flex';
      }
    });

    // Fermer le popup de sélection
    document.querySelector('#selection-popup .close-btn').addEventListener('click', () => {
      selectionPopup.style.display = 'none';
    });

    window.addEventListener('click', (event) => {
      if (event.target === selectionPopup) {
        selectionPopup.style.display = 'none';
      }
    });

    // Afficher l'aide
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

    // Afficher le popup d'abandon
    document.querySelector('.btn-abandonner').addEventListener('click', () => {
      abandonnerPopup.style.display = 'flex';
    });

    document.querySelector('#abandonner-popup .yes-btn').addEventListener('click', () => {
      window.location.href = '../pages/';
    });

    document.querySelector('#abandonner-popup .no-btn').addEventListener('click', () => {
      abandonnerPopup.style.display = 'none';
    });

    // Mettre à jour les champs cachés pour l'envoi du formulaire
    function updateHiddenInputs() {
      selectedAccoudoirBoisInput.value = Object.keys(selectedOptions).join(',');
      selectedNbAccoudoirInput.value = Object.values(selectedOptions).join(',');
    }

    // Sauvegarde dans localStorage
    function saveSelection() {
      localStorage.setItem('selectedOptions', JSON.stringify(selectedOptions));
    }
  });
</script>


</main>

<?php require_once '../../squelette/footer.php' ?>
</body>
</html> 