<?php
require '../../admin/config.php';
session_start();


// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../formulaire/Connexion.php");
    exit;
}


$id_client = $_SESSION['user_id'];


// Récupérer les types d'accoudoirs en bois depuis la base de données
$stmt = $pdo->query("SELECT * FROM accoudoir_bois");
$accoudoir_bois = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Récupérer la sélection existante de l'utilisateur dans la commande temporaire
$stmt = $pdo->prepare("SELECT id_accoudoir_bois, id_nb_accoudoir FROM commande_temporaire WHERE id_client = ?");
$stmt->execute([$id_client]);
$commande_existante = $stmt->fetch(PDO::FETCH_ASSOC);


$accoudoir_selectionne = $commande_existante['id_accoudoir_bois'] ?? null;
$nb_accoudoir_selectionne = $commande_existante['id_nb_accoudoir'] ?? 1; // Valeur par défaut à 1


// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['accoudoir_bois_id']) || empty($_POST['accoudoir_bois_id']) || !isset($_POST['nb_accoudoir']) || empty($_POST['nb_accoudoir'])) {
        echo "Erreur : Aucun accoudoir ou quantité sélectionné.";
        exit;
    }


    $id_accoudoir_bois = $_POST['accoudoir_bois_id'];
    $nb_accoudoir = $_POST['nb_accoudoir'];


    // Vérifier si une commande temporaire existe déjà pour cet utilisateur
    if ($commande_existante) {
        $stmt = $pdo->prepare("UPDATE commande_temporaire SET id_accoudoir_bois = ?, id_nb_accoudoir = ? WHERE id_client = ?");
        $stmt->execute([$id_accoudoir_bois, $nb_accoudoir, $id_client]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO commande_temporaire (id_client, id_accoudoir_bois, id_nb_accoudoir) VALUES (?, ?, ?)");
        $stmt->execute([$id_client, $id_accoudoir_bois, $nb_accoudoir]);
    }


    // Sauvegarder la sélection dans le localStorage via JavaScript
    echo "<script>
        localStorage.setItem('selectedAccoudoirBois', '$id_accoudoir_bois');
        localStorage.setItem('selectedNbAccoudoirBois', '$nb_accoudoir');
        window.location.href = 'etape6-bois-dossier.php';
    </script>";
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
  <script type="module" src="../../scrpit/popup.js"></script>
  <script type="module" src="../../scrpit/button.js"></script>
  <script type="module" src="../../scrpit/variationPrix.js"></script>

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
      border: 3px solid #997765;
      /* Couleur marron */
      border-radius: 5px;
      box-sizing: border-box;
    }
  </style>

  
<style>
  .close-btn-selection {
    background-color: #997765;
    color: #fff;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    padding: 10px 20px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: bold;
  }

  
  .close-btn-selection {
    background-color: #997765;
    color: #fff;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    padding: 10px 20px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: bold;
  }

  .close-btn-selection:hover {
    transform: scale(1.1);
    transition: transform .2s;
  }
</style>


</head>


<body data-user-id="<?php echo $_SESSION['user_id']; ?>" data-current-step="5-accoudoir-bois">


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
    </div>
    <div class="container">
      <!-- Colonne de gauche -->
      <div class="left-column transition">
        <h2>Étape 5 - Ajoute tes accoudoirs</h2>


        <section class="color-options">
          <?php if (!empty($accoudoir_bois)): ?>
            <?php foreach ($accoudoir_bois as $bois): ?>
              <div class="option transition">
                <img src="../../admin/uploads/accoudoirs-bois/<?php echo htmlspecialchars($bois['img']); ?>"
                  alt="<?php echo htmlspecialchars($bois['nom']); ?>" data-bois-id="<?php echo $bois['id']; ?>"
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
            <button class="btn-retour transition">Retour</button>
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

    <!-- Popup selection option -->
    <div id="selection-popup" class="popup transition">
      <div class="popup-content">
        <h2>Veuillez choisir une option avant de continuer.</h2>
        <br>
        <button class="close-btn-selection">OK</button>
      </div>
    </div>

    <script>
  document.addEventListener('DOMContentLoaded', () => {
    const selectionPopup = document.getElementById('selection-popup');
    const closeSelectionBtn = document.querySelector('#selection-popup .close-btn-selection');

    closeSelectionBtn.addEventListener('click', () => {
      selectionPopup.style.display = 'none';
    });
  });
</script>

<SCript>
  // Popup sélection
document.addEventListener('DOMContentLoaded', () => {
  const options = document.querySelectorAll('.color-options .option img');
  const mainImage = document.querySelector('.main-display img');
  const suivantButton = document.querySelector('.btn-suivant');
  const selectionPopup = document.getElementById('selection-popup');
  const closeSelectionBtn = document.querySelector('.close-btn-selection'); // Le bouton OK pour fermer le popup
  const selectedAccoudoirBoisInput = document.getElementById('selected-accoudoir_bois');
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

  // Vérifier la sélection avant de passer à l'étape suivante
  suivantButton.addEventListener('click', (event) => {
    if (Object.keys(selectedOptions).length === 0 || !selectedNbAccoudoirInput.value || selectedNbAccoudoirInput.value === "0") {
      event.preventDefault();
      selectionPopup.style.display = 'flex'; // Afficher le popup de sélection
    }
  });

  // Fermer le popup avec le bouton "OK"
  closeSelectionBtn.addEventListener('click', () => {
    selectionPopup.style.display = 'none'; // Fermer le popup
  });

  // Fermer le popup si clic à l'extérieur
  window.addEventListener('click', (event) => {
    if (event.target === selectionPopup) {
      selectionPopup.style.display = 'none'; // Fermer le popup
    }
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

</SCript>

    <!-- VARIATION DES PRIX  -->
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        let totalPrice = 0; // Total global pour toutes les étapes


        // Identifier l'étape actuelle
        const currentStep = "5-accoudoir-bois"; // Étape spécifique


        // Charger l'ID utilisateur depuis une variable PHP intégrée dans le HTML
        const userId = document.body.getAttribute('data-user-id'); // Ex. <body data-user-id="<?php echo $_SESSION['user_id']; ?>">
        if (!userId) {
          console.error("ID utilisateur non trouvé. Vérifiez que 'data-user-id' est bien défini dans le HTML.");
          return;
        }
        console.log("ID utilisateur récupéré :", userId);


        // Charger toutes les options sélectionnées depuis sessionStorage (par utilisateur)
        const sessionKey = `allSelectedOptions_${userId}`;
        const selectedKey = `selectedOptions_${userId}`;
        let allSelectedOptions = JSON.parse(sessionStorage.getItem(sessionKey)) || [];
        let selectedOptions = JSON.parse(sessionStorage.getItem(selectedKey)) || {};
        console.log("Données globales récupérées depuis sessionStorage :", allSelectedOptions);


        // Vérifier si `allSelectedOptions` est un tableau
        if (!Array.isArray(allSelectedOptions)) {
          allSelectedOptions = [];
          console.warn("allSelectedOptions n'était pas un tableau. Réinitialisé à []");
        }


        // Restaurer les sélections (style "selected" et quantités)
        function restoreSelections() {
          document.querySelectorAll('.color-options .option img').forEach(img => {
            const boisId = img.getAttribute('data-bois-id');
            const parentOption = img.closest('.option');
            const quantityInput = parentOption.querySelector('.quantity-input1');


            if (selectedOptions[boisId]) {
              // Appliquer le style "selected" et restaurer la quantité
              img.classList.add('selected');
              quantityInput.value = selectedOptions[boisId];
            } else {
              // Réinitialiser si non sélectionné
              img.classList.remove('selected');
              quantityInput.value = 0;
            }
          });
        }


        // Fonction pour mettre à jour le total global
        function updateTotal() {
          // Calculer le total global en prenant en compte toutes les options et quantités
          totalPrice = allSelectedOptions.reduce((sum, option) => {
            const price = option.price || 0; // S'assurer que le prix est valide
            const quantity = option.quantity || 1; // Par défaut, quantité = 1
            return sum + (price * quantity);
          }, 0);


          console.log("Total global mis à jour :", totalPrice);


          // Mettre à jour le total dans l'interface
          const totalElement = document.querySelector(".footer p span");
          if (totalElement) {
            totalElement.textContent = `${totalPrice.toFixed(2)} €`;
          } else {
            console.error("L'élément '.footer p span' est introuvable !");
          }
        }


        // Fonction pour sauvegarder les options sélectionnées
        function saveSelectedOption(optionId, price, quantity) {
          // Créer un identifiant unique basé sur l'étape actuelle
          const uniqueId = `${currentStep}_${optionId}`;


          // Supprimer l'option actuelle du stockage global
          allSelectedOptions = allSelectedOptions.filter(opt => opt.id !== uniqueId);


          // Ajouter ou mettre à jour l'option avec la nouvelle quantité et prix si quantité > 0
          if (quantity > 0) {
            allSelectedOptions.push({
              id: uniqueId,
              price: price,
              quantity: quantity
            });
          }


          // Sauvegarder les données globales dans sessionStorage pour cet utilisateur
          sessionStorage.setItem(sessionKey, JSON.stringify(allSelectedOptions));
        }


        // Fonction pour mettre à jour les champs cachés
        function updateHiddenInputs() {
          const selectedAccoudoirBoisInput = document.getElementById('selected-accoudoir_bois');
          const selectedNbAccoudoirInput = document.getElementById('selected-nb_accoudoir');
          selectedAccoudoirBoisInput.value = Object.keys(selectedOptions).join(',');
          selectedNbAccoudoirInput.value = Object.values(selectedOptions).join(',');
        }


        // Fonction pour sauvegarder les sélections
        function saveSelection() {
          sessionStorage.setItem(selectedKey, JSON.stringify(selectedOptions));
        }


        // Sélectionner ou désélectionner un accoudoir
        document.querySelectorAll('.color-options .option img').forEach(img => {
          img.addEventListener('click', () => {
            const boisId = img.getAttribute('data-bois-id');
            const parentOption = img.closest('.option');
            const quantityInput = parentOption.querySelector('.quantity-input1');
            const price = parseFloat(img.getAttribute('data-bois-prix')) || 0;


            if (selectedOptions[boisId]) {
              // Désélectionner l'accoudoir
              delete selectedOptions[boisId];
              img.classList.remove('selected');
              quantityInput.value = 0;
              saveSelectedOption(boisId, price, 0); // Supprimer dans allSelectedOptions
            } else {
              // Sélectionner l'accoudoir avec quantité 1
              selectedOptions[boisId] = 1;
              img.classList.add('selected');
              quantityInput.value = 1;
              saveSelectedOption(boisId, price, 1); // Ajouter dans allSelectedOptions
            }


            updateHiddenInputs();
            saveSelection();
            updateTotal();
          });
        });


        // Gérer les clics sur les boutons d'augmentation et de diminution
        document.querySelectorAll('.btn-increase, .btn-decrease').forEach(button => {
          button.addEventListener('click', (event) => {
            const parentOption = event.target.closest('.option');
            const boisId = parentOption.querySelector('img').getAttribute('data-bois-id');
            const quantityInput = parentOption.querySelector('.quantity-input1');
            const price = parseFloat(parentOption.querySelector('img').getAttribute('data-bois-prix')) || 0;


            if (!selectedOptions[boisId]) return; // Si non sélectionné, ne rien faire


            let newQuantity = parseInt(quantityInput.value || "0") + (event.target.classList.contains('btn-increase') ? 1 : -1);
            newQuantity = Math.max(newQuantity, 0); // Empêcher les quantités négatives
            quantityInput.value = newQuantity;


            if (newQuantity === 0) {
              // Supprimer si quantité = 0
              delete selectedOptions[boisId];
              parentOption.querySelector('img').classList.remove('selected');
              saveSelectedOption(boisId, price, 0);
            } else {
              // Mettre à jour la quantité
              selectedOptions[boisId] = newQuantity;
              saveSelectedOption(boisId, price, newQuantity);
            }


            updateHiddenInputs();
            saveSelection();
            updateTotal();
          });
        });


        // Vérifier la sélection avant de passer à l'étape suivante
        const suivantButton = document.querySelector('.btn-suivant');
        suivantButton.addEventListener('click', (event) => {
          if (Object.keys(selectedOptions).length === 0 || !document.getElementById('selected-nb_accoudoir').value || document.getElementById('selected-nb_accoudoir').value === "0") {
            event.preventDefault();
            const selectionPopup = document.getElementById('selection-popup');
            selectionPopup.style.display = 'flex';
          }
        });


        // Restaurer les sélections au chargement de la page
        restoreSelections();


        // Initialiser le total dès le chargement de la page
        updateTotal();
      });


    </script>






  </main>


  <?php require_once '../../squelette/footer.php' ?>
</body>


</html>

