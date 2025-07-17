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

$id_client = $_SESSION['user_id'];

// Récupérer les types d'accoudoirs depuis la base de données
$stmt = $pdo->query("SELECT * FROM accoudoir_tissu ORDER BY prix ASC");
$accoudoir_tissu = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la sélection existante de l'utilisateur dans la commande temporaire
$stmt = $pdo->prepare("SELECT id_accoudoir_tissu, id_nb_accoudoir FROM commande_temporaire WHERE id_client = ?");
$stmt->execute([$id_client]);
$commande_existante = $stmt->fetch(PDO::FETCH_ASSOC);

$accoudoir_selectionne = $commande_existante['id_accoudoir_tissu'] ?? null;
$nb_accoudoir_selectionne = $commande_existante['id_nb_accoudoir'] ?? 1; // Valeur par défaut à 1

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {


  $id_accoudoir_tissu = $_POST['accoudoir_tissu_id'];
  $nb_accoudoir = $_POST['nb_accoudoir'];

  // Vérifier si une commande temporaire existe déjà pour cet utilisateur
  if ($commande_existante) {
    $stmt = $pdo->prepare("UPDATE commande_temporaire SET id_accoudoir_tissu = ?, id_nb_accoudoir = ? WHERE id_client = ?");
    $stmt->execute([$id_accoudoir_tissu, $nb_accoudoir, $id_client]);
  } else {
    $stmt = $pdo->prepare("INSERT INTO commande_temporaire (id_client, id_accoudoir_tissu, id_nb_accoudoir) VALUES (?, ?, ?)");
    $stmt->execute([$id_client, $id_accoudoir_tissu, $nb_accoudoir]);
  }

  // Sauvegarder la sélection dans le localStorage via JavaScript
  echo "<script>
        localStorage.setItem('selectedAccoudoir', '$id_accoudoir_tissu');
        localStorage.setItem('selectedNbAccoudoir', '$nb_accoudoir');
        window.location.href = 'etape7-tissu-mousse.php';
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
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/processus.css">
  <link rel="stylesheet" href="../../styles/popup.css">
  <script type="module" src="../../script/pathSwitchReset.js"></script>
  <link rel="stylesheet" href="../../styles/buttons.css">
  <script type="module" src="../../script/popup.js"></script>
  <script type="module" src="../../script/keydown.js"></script>

  <title>Étape 6 - Ajoute des accoudoirs</title>

</head>

<body data-user-id="<?php echo $_SESSION['user_id']; ?>" data-current-step="6-accoudoir-tissu">
  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <main>
    <div class="fil-ariane-container h2" aria-label="fil-ariane" id="filAriane">
      <ul class="fil-ariane">
        <li><a href="etape1-1-structure.php">Structure</a></li>
        <li><a href="etape1-2-dimension.php">Dimension</a></li>
        <li><a href="etape2-type-banquette.php">Banquette</a></li>
        <li><a href="etape3-tissu-modele-banquette.php">Modèle</a></li>
        <li><a href="etape4-1-tissu-tissu.php">Tissu</a></li>
        <li><a href="etape5-tissu-dossier.php">Dossier</a></li>
        <li><a href="etape6-tissu-accoudoir.php" class="active">Accoudoir</a></li>
        <li><a href="etape7-tissu-mousse.php">Mousse</a></li>
      </ul>
    </div>

    <div class="container transition">
      <!-- Colonne de gauche -->
      <div class="left-column ">
        <h2>Étape 6 - Ajoute des accoudoirs</h2>
        <section class="color-2options">
          <?php if (!empty($accoudoir_tissu)): ?>
            <?php foreach ($accoudoir_tissu as $accoudoir): ?>
              <div class="option ">
                <img src="../../admin/uploads/accoudoirs-tissu/<?php echo htmlspecialchars($accoudoir['img']); ?>"
                  alt="<?php echo htmlspecialchars($accoudoir['nom']); ?>"
                  data-accoudoir-id="<?php echo $accoudoir['id']; ?>"
                  data-accoudoir-prix="<?php echo $accoudoir['prix']; ?>"
                  data-can-deselect="true">
                <p><?php echo htmlspecialchars($accoudoir['nom']); ?></p>
                <p><strong><?php echo htmlspecialchars($accoudoir['prix']); ?> €</strong></p>

                <!-- Compteur de quantité (maintenant à l'intérieur de .option) -->
                <div class="quantity-selector">
                  <button class="decrease-btn">-</button>
                  <input type="text" class="quantity-input" value="2" readonly>
                  <button class="increase-btn">+</button>

                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Aucun accoudoir disponible pour le moment.</p>
          <?php endif; ?>
        </section>

        <!-- Footer -->
        <div class="footer">
          <p>Total : <span>0 €</span></p>
          <div class="buttons">
            <button onclick="retourEtapePrecedente()" class="btn-beige  ">Retour</button>
            <form method="POST" action="">
              <input type="hidden" name="accoudoir_tissu_id" id="selected-accoudoir_tissu" required>
              <input type="hidden" name="nb_accoudoir" id="selected-nb_accoudoir" required>
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
    <!-- Popup d'erreur si option non sélectionnée -->
    <div id="erreur-popup" class="popup ">
      <div class="popup-content">
        <h2>Veuillez choisir une option avant de continuer.</h2>
        <button class="btn-noir">OK</button>
      </div>
    </div>

        <!-- Popup bloquant pour les étapes non validées -->
<div id="filariane-popup" class="popup">
  <div class="popup-content">
    <h2>Veuillez cliquez sur "suivant" pour passer à l’étape d’après.</h2>
    <button class="btn-noir">OK</button>
  </div>
</div>


    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const options = document.querySelectorAll('.color-2options .option img');
        const suivantButton = document.getElementById('btn-suivant');
        const erreurPopup = document.getElementById('erreur-popup');
        const closeErreurBtn = erreurPopup.querySelector('.btn-noir');
        const form = document.querySelector('form');
        const mainImage = document.querySelector('.main-display img');
        const selectedAccoudoirTissuInput = document.getElementById('selected-accoudoir_tissu');
        const selectedNbAccoudoirInput = document.getElementById('selected-nb_accoudoir');
        const totalElement = document.querySelector(".footer p span");

        const currentStep = "6-accoudoir-tissu";
        const userId = document.body.getAttribute('data-user-id');
        const sessionKey = `allSelectedOptions_${userId}`;
        const selectedKey = `selectedOptions_${userId}`;

        if (!userId) return console.error("ID utilisateur non trouvé.");

        let totalPrice = 0;
        let allSelectedOptions = JSON.parse(sessionStorage.getItem(sessionKey)) || [];
        let selectedOptions = JSON.parse(sessionStorage.getItem(selectedKey)) || {};

        //Déclaration des fonctions
        function updateHiddenInputs() {
          selectedAccoudoirTissuInput.value = Object.keys(selectedOptions).join(',');
          selectedNbAccoudoirInput.value = Object.values(selectedOptions).join(',');
        }

function updateTotal() {
  totalPrice = allSelectedOptions.reduce((sum, option) => {
    // si tu veux compter la quantité (1 par défaut)
    const qte = option.quantity ?? 1;
    return sum + option.price * qte;
  }, 0);

  if (totalElement) totalElement.textContent = `${totalPrice.toFixed(2)} €`;
}

        
        function saveSelectedOption(optionId, price, quantity) {
          const uniqueId = `${currentStep}_${optionId}`;
          allSelectedOptions = allSelectedOptions.filter(opt => opt.id !== uniqueId);
          if (quantity > 0) {
            allSelectedOptions.push({
              id: uniqueId,
              price,
              quantity
            });
          }
          sessionStorage.setItem(sessionKey, JSON.stringify(allSelectedOptions));
        }

        function saveSelection() {
          sessionStorage.setItem(selectedKey, JSON.stringify(selectedOptions));
        }

        function restoreSelections() {
          options.forEach(img => {
            const accoudoirId = img.getAttribute('data-accoudoir-id');
            const parentOption = img.closest('.option');
            const quantityInput = parentOption.querySelector('.quantity-input');
            const quantitySelector = parentOption.querySelector('.quantity-selector');
            const decreaseBtn = parentOption.querySelector('.decrease-btn');
            const increaseBtn = parentOption.querySelector('.increase-btn');

            const storedQuantity = selectedOptions[accoudoirId] || 0;

            if (quantityInput) {
              quantityInput.value = 2;
              quantityInput.readOnly = true;
            }

            if (storedQuantity > 0) {
              img.classList.add('selected');
              quantityInput.value = storedQuantity;
              if (quantitySelector) quantitySelector.style.display = "block";
              if (mainImage) {
                mainImage.src = img.src;
                mainImage.alt = img.alt;
              }
              if (parseInt(quantityInput.value) === 2 && decreaseBtn) {
                decreaseBtn.classList.add('btn-opacity');
              }
              if (parseInt(quantityInput.value) === 2 && increaseBtn) {
                increaseBtn.classList.add('btn-opacity');
              }
            } else {
              img.classList.remove('selected');
              quantityInput.value = 0;
              if (quantitySelector) quantitySelector.style.display = "none";
            }
          });
          updateTotal();
          updateHiddenInputs();
        }

        // // Gérer le clic sur une option
        options.forEach(img => {
          img.addEventListener('click', () => {
            const accoudoirId = img.getAttribute('data-accoudoir-id');
            const price = parseFloat(img.getAttribute('data-accoudoir-prix')) || 0;
            const parentOption = img.closest('.option');
            const quantityInput = parentOption.querySelector('.quantity-input');
            const quantitySelector = parentOption.querySelector('.quantity-selector');
            const decreaseBtn = parentOption.querySelector('.decrease-btn');
            const increaseBtn = parentOption.querySelector('.increase-btn');

            // Supprimer la sélection sur tous les autres accoudoirs
            options.forEach(opt => {
              opt.classList.remove('selected');
              const parentOpt = opt.closest('.option');
              const quantityOpt = parentOpt.querySelector('.quantity-input');
              const quantitySelectorOpt = parentOpt.querySelector('.quantity-selector');
              const decreaseBtnOpt = parentOpt.querySelector('.decrease-btn');
              const increaseBtnOpt = parentOpt.querySelector('.increase-btn');

              if (quantityOpt) {
                quantityOpt.value = 0;
              }
              if (quantitySelectorOpt) {
                quantitySelectorOpt.style.display = "none";
              }
              if (decreaseBtnOpt) {
                decreaseBtnOpt.classList.remove('btn-opacity');
              }
              if (increaseBtnOpt) {
                increaseBtnOpt.classList.remove('btn-opacity');
              }
            });

            // Désélectionner 
            if (selectedOptions[accoudoirId]) {
              delete selectedOptions[accoudoirId];
              img.classList.remove('selected');
              quantityInput.value = 0;
              if (quantitySelector) quantitySelector.style.display = "none";
              saveSelectedOption(accoudoirId, price, 0);
            } else {
              allSelectedOptions = allSelectedOptions.filter(opt => !opt.id.startsWith(`${currentStep}_`));
              selectedOptions = {};
              // Sélectionner uniquement l'accoudoir cliqué
              selectedOptions[accoudoirId] = 2;
              img.classList.add('selected');
              quantityInput.value = 2;
              quantityInput.readOnly = true;
              if (quantitySelector) quantitySelector.style.display = "block";
              saveSelectedOption(accoudoirId, price, 2);
              // Image mise à jour
              if (mainImage) {
                mainImage.src = img.src;
                mainImage.alt = img.alt;
              }
              if (parseInt(quantityInput.value) === 2 && decreaseBtn) {
                decreaseBtn.classList.add('btn-opacity');
              }
              if (parseInt(quantityInput.value) === 2 && increaseBtn) {
                increaseBtn.classList.add('btn-opacity');
              }
            }
            updateHiddenInputs();
            saveSelection();
            updateTotal();
          });
        });

        // Incrémentation button
        document.querySelectorAll('.increase-btn').forEach(button => {
          button.addEventListener('click', event => {
            const parentOption = event.target.closest('.option');
            const img = parentOption.querySelector('img');
            const accoudoirId = img.getAttribute('data-accoudoir-id');
            const price = parseFloat(img.getAttribute('data-accoudoir-prix')) || 0;
            const quantityInput = parentOption.querySelector('.quantity-input');

            let quantity = parseInt(quantityInput.value) || 0;
            if (quantity < 1) return;
            quantityInput.value = quantity;
            selectedOptions[accoudoirId] = quantity;

            saveSelectedOption(accoudoirId, price, quantity);
            saveSelection();
            updateHiddenInputs();

            if (quantity === 2) {
              button.classList.add('btn-opacity');
            }
          });
        });

        // Décrémentation button
        document.querySelectorAll('.decrease-btn').forEach(button => {
          button.addEventListener('click', event => {
            const parentOption = event.target.closest('.option');
            const img = parentOption.querySelector('img');
            const accoudoirId = img.getAttribute('data-accoudoir-id');
            const price = parseFloat(img.getAttribute('data-accoudoir-prix')) || 0;
            const quantityInput = parentOption.querySelector('.quantity-input');
            const increaseBtn = parentOption.querySelector('.increase-btn');

            let quantity = parseInt(quantityInput.value) || 0;
            if (quantity > 1) {
              quantityInput.value = quantity;
              selectedOptions[accoudoirId] = quantity;

              saveSelectedOption(accoudoirId, price, quantity);
              saveSelection();
              updateHiddenInputs();

            }
            if (quantity === 2) {
              button.classList.add('btn-opacity');
            }
          });
        });

        //Etape suivante + pop up erreur
        suivantButton.addEventListener('click', event => {
          event.preventDefault();
          const selectedImg = document.querySelector('.option img.selected');
          if (!selectedImg) return erreurPopup.style.display = 'flex';

          const quantity = parseInt(
            selectedImg.closest('.option').querySelector('.quantity-input').value
          );

          if (isNaN(quantity) || quantity <= 0) {
            erreurPopup.style.display = 'flex';
            return;
          }

          selectedAccoudoirTissuInput.value = selectedImg.getAttribute('data-accoudoir-id');
          selectedNbAccoudoirInput.value = quantity;
          form.submit();
        });

        closeErreurBtn.addEventListener('click', () => {
          erreurPopup.style.display = 'none';
        });

        window.addEventListener('click', (event) => {
          if (event.target === erreurPopup) {
            erreurPopup.style.display = 'none';
          }
        });
        restoreSelections();
      });
    </script>

    <!-- BOUTTON RETOUR -->
    <script>
      function retourEtapePrecedente() {
        window.location.href = "etape5-tissu-dossier.php";
      }
    </script>

    
    <!-- FIL ARIANE -->
    <script>

document.addEventListener('DOMContentLoaded', () => {
  const filAriane = document.querySelector('.fil-ariane');
  const links = filAriane.querySelectorAll('a');

  const filArianePopup = document.getElementById('filariane-popup');
  const closeFilArianePopupBtn = filArianePopup.querySelector('.btn-noir');

  const etapes = [
  { id: 'etape1-1-structure.php', key: null }, // toujours accessible
  { id: 'etape1-2-dimension.php', key:  null },
  { id: 'etape2-type-banquette.php', key:  null },
  { id: 'etape3-tissu-modele-banquette.php', key:  null},
  { id: 'etape4-1-tissu-tissu.php', key: null },
  { id: 'etape5-tissu-dossier.php', key: null },
  { id: 'etape6-tissu-accoudoir.php', key: null },
  { id: 'etape7-tissu-mousse.php', key: 'etape7_valide' },
];

 links.forEach((link, index) => {
    const etape = etapes[index];

    // Empêche de cliquer si l'étape n’est pas validée
    if (etape.key && sessionStorage.getItem(etape.key) !== 'true') {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        filArianePopup.style.display = 'flex';
      });
      link.classList.add('disabled-link');
    }
  });

  // Fermer le popup avec le bouton
  closeFilArianePopupBtn.addEventListener('click', () => {
    filArianePopup.style.display = 'none';
  });

  // Fermer si on clique en dehors du contenu
  window.addEventListener('click', (event) => {
    if (event.target === filArianePopup) {
      filArianePopup.style.display = 'none';
    }
  });
});

    </script>


  </main>
  <?php require_once '../../squelette/footer.php'; ?>

</body>

</html>