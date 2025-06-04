<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
  header("Location: ../formulaire/Connexion.php");
  exit;
}

$id_client = $_SESSION['user_id'];

// Récupérer les types d'accoudoirs depuis la base de données
$stmt = $pdo->query("SELECT * FROM accoudoir_bois");
$accoudoir_bois = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['accoudoir_bois_id']) || empty($_POST['accoudoir_bois_id']) || !isset($_POST['nb_accoudoir']) || empty($_POST['nb_accoudoir'])) {
    echo "Erreur : Aucun accoudoir ou quantité sélectionné.";
    exit;
  }

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
  $check = $pdo->prepare("SELECT COUNT(*) FROM accoudoir_bois WHERE id = ?");

  $js_accoudoir_ids = [];
  $js_nb_accoudoirs = [];

  foreach ($id_accoudoirs as $index => $id_accoudoir) {
    $nb = (int) $nb_accoudoirs[$index];

    // Vérifier existence
    $check->execute([$id_accoudoir]);
    if ($nb > 0 && $check->fetchColumn() > 0) {
      $stmt->execute([$commande_id, $id_accoudoir, $nb]);
      $js_accoudoir_ids[] = $id_accoudoir;
      $js_nb_accoudoirs[] = $nb;
    }
  }

  // Sauvegarder la sélection dans le localStorage via JavaScript
  $js_ids = implode(',', $js_accoudoir_ids);
  $js_nbs = implode(',', $js_nb_accoudoirs);

  echo "<script>
        localStorage.setItem('selectedAccoudoirBois', '$js_ids');
        localStorage.setItem('selectedNbAccoudoirBois', '$js_nbs');
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
  <link rel="stylesheet" href="../../styles/buttons.css">
  <script type="module" src="../../script/popup.js"></script>
  <script type="module" src="../../script/keydown.js"></script>


  <title>Étape 5 - Ajoute des accoudoirs</title>

</head>


<body data-user-id="<?php echo $_SESSION['user_id']; ?>" data-current-step="5-accoudoir-bois">

  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <main>
    <div class="fil-ariane-container h2" aria-label="fil-ariane">
      <ul class="fil-ariane">
        <li><a href="etape1-1-structure.php">Structure</a></li>
        <li><a href="etape1-2-dimension.php">Dimension</a></li>
        <li><a href="etape2-type-banquette.php">Banquette</a></li>
        <li><a href="etape3-bois-couleur.php">Couleur</a></li>
        <li><a href="etape4-bois-decoration.php">Décoration</a></li>
        <li><a href="etape5-bois-accoudoir.php" class="active">Accoudoirs</a></li>
        <li><a href="etape6-bois-dossier.php">Dossier</a></li>
        <li><a href="etape7-1-bois-tissu.php">Tissu</a></li>
        <li><a href="etape8-bois-mousse.php">Mousse</a></li>
      </ul>
    </div>
    <div class="container transition">
      <!-- Colonne de gauche -->
      <div class="left-column ">
        <h2>Étape 5 - Ajoute des accoudoirs</h2>


        <section class="color-options">
          <?php if (!empty($accoudoir_bois)): ?>
            <?php foreach ($accoudoir_bois as $bois): ?>
              <div class="option">
                <img src="../../admin/uploads/accoudoirs-bois/<?php echo htmlspecialchars($bois['img']); ?>"
                  alt="<?php echo htmlspecialchars($bois['nom']); ?>" data-bois-id="<?php echo $bois['id']; ?>"
                  data-bois-prix="<?php echo $bois['prix']; ?>">
                <p><?php echo htmlspecialchars($bois['nom']); ?></p>
                <p><strong><?php echo htmlspecialchars($bois['prix']); ?> €</strong></p>
                <!-- Compteur de quantité -->
                <div class="quantity-selector1">
                  <button class="btn-decrease">-</button>
                  <input type="text" class="quantity-input1" value="0" readonly>
                  <button class="btn-increase">+</button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Aucun accoudoir disponible pour le moment.</p>
          <?php endif; ?>
        </section>

        <div class="footer">
          <p>Total : <span>0 €</span></p>
          <div class="buttons">
            <button onclick="retourEtapePrecedente()" class="btn-beige  ">Retour</button>
            <form method="POST" action="">
              <input type="hidden" name="accoudoir_bois_id" id="selected-accoudoir_bois">
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

    <!-- Popup d'erreur si option non selectionnée -->
    <div id="erreur-popup" class="popup ">
      <div class="popup-content">
        <h2>Veuillez choisir une option avant de continuer.</h2>
        <button class="btn-noir">OK</button>
      </div>
    </div>


    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const options = document.querySelectorAll('.color-options .option img');
        const mainImage = document.querySelector('.main-display img');
        const suivantButton = document.getElementById('btn-suivant');
        const form = document.querySelector('form');
        const erreurPopup = document.getElementById('erreur-popup');
        const closeErreurBtn = erreurPopup.querySelector('.btn-noir');
        const selectedAccoudoirBoisInput = document.getElementById('selected-accoudoir_bois');
        const selectedNbAccoudoirInput = document.getElementById('selected-nb_accoudoir');
        const currentStep = "5-accoudoir-bois";

        const userId = document.body.getAttribute('data-user-id');
        if (!userId) {
          console.error("ID utilisateur non trouvé.");
          return;
        }

        const selectedKey = `selectedOptions_${userId}`;
        const sessionKey = `allSelectedOptions_${userId}`;

        let selectedOptions = JSON.parse(sessionStorage.getItem(selectedKey)) || {};
        let allSelectedOptions = JSON.parse(sessionStorage.getItem(sessionKey)) || [];
        let totalPrice = 0;

        if (!Array.isArray(allSelectedOptions)) {
          allSelectedOptions = [];
        }

        // Déclaration des fonctions
        function updateHiddenInputs() {
          selectedAccoudoirBoisInput.value = Object.keys(selectedOptions).join(',');
          selectedNbAccoudoirInput.value = Object.values(selectedOptions).join(',');
        }

        function saveSelection() {
          sessionStorage.setItem(selectedKey, JSON.stringify(selectedOptions));
        }

        function saveSelectedOption(optionId, price, quantity) {
          const uniqueId = `${currentStep}_${optionId}`;
          allSelectedOptions = allSelectedOptions.filter(opt => opt.id !== uniqueId);
          if (quantity > 0) {
            allSelectedOptions.push({
              id: uniqueId,
              price: price,
              quantity: quantity
            });
          }
          sessionStorage.setItem(sessionKey, JSON.stringify(allSelectedOptions));
        }

        function updateTotal() {
          totalPrice = allSelectedOptions.reduce((sum, opt) => sum + (opt.price || 0) * (opt.quantity || 1), 0);
          const totalElement = document.querySelector(".footer p span");
          if (totalElement) {
            totalElement.textContent = `${totalPrice.toFixed(2)} €`;
          }
        }

        function restoreSelections() {
          options.forEach(img => {
            const boisId = img.getAttribute('data-bois-id');
            const parent = img.closest('.option');
            const quantityInput = parent.querySelector('.quantity-input1');
            const quantitySelector = parent.querySelector('.quantity-selector1');
            const decreaseBtn = parent.querySelector('.btn-decrease');

            if (selectedOptions[boisId]) {
              img.classList.add('selected');
              quantityInput.value = selectedOptions[boisId];
              if (quantitySelector) quantitySelector.style.display = "block";
              if (parseInt(quantityInput.value) === 1 && decreaseBtn) {
                decreaseBtn.classList.add('btn-opacity');
              }
            } else {
              img.classList.remove('selected');
              quantityInput.value = 0;
              if (quantitySelector) quantitySelector.style.display = "none";
            }
          });

          const lastSelected = localStorage.getItem('lastSelectedAccoudoir');
          if (lastSelected) {
            const lastImg = document.querySelector(`.color-options .option img[data-bois-id="${lastSelected}"]`);
            if (lastImg) {
              mainImage.src = lastImg.src;
              mainImage.alt = lastImg.alt;
            }
          }
        }

        // Sélection / désélection sur image
        options.forEach(img => {
          img.addEventListener('click', () => {
            const boisId = img.getAttribute('data-bois-id');
            const price = parseFloat(img.getAttribute('data-bois-prix')) || 0;
            const parent = img.closest('.option');
            const quantityInput = parent.querySelector('.quantity-input1');
            const quantitySelector = parent.querySelector('.quantity-selector1');
            const decreaseBtn = parent.querySelector('.btn-decrease');

            if (img.classList.contains('selected')) {
              img.classList.remove('selected');
              quantityInput.value = 0;
              delete selectedOptions[boisId];
              if (quantitySelector) quantitySelector.style.display = "none";
              localStorage.removeItem('lastSelectedAccoudoir');
              saveSelectedOption(boisId, price, 0);
            } else {
              img.classList.add('selected');
              quantityInput.value = 1;
              selectedOptions[boisId] = 1;
              if (quantitySelector) quantitySelector.style.display = "block";
              localStorage.setItem('lastSelectedAccoudoir', boisId);
              mainImage.src = img.src;
              mainImage.alt = img.alt;
              saveSelectedOption(boisId, price, 1);
              if (parseInt(quantityInput.value) === 1 && decreaseBtn) {
                decreaseBtn.classList.add('btn-opacity');
              }
            }
            updateHiddenInputs();
            saveSelection();
            updateTotal();

          });
        });

        // Incrémentation button
        document.querySelectorAll('.btn-increase').forEach(btn => {
          btn.addEventListener('click', (e) => {
            const parent = e.target.closest('.option');
            const img = parent.querySelector('img');
            const boisId = img.getAttribute('data-bois-id');
            const price = parseFloat(img.getAttribute('data-bois-prix')) || 0;
            const quantityInput = parent.querySelector('.quantity-input1');
            const decreaseBtn = parent.querySelector('.btn-decrease');

            let quantity = parseInt(quantityInput.value) || 0;
            if (quantity < 1) return;
            quantity++;
            quantityInput.value = quantity;

            selectedOptions[boisId] = quantity;
            saveSelectedOption(boisId, price, quantity);
            saveSelection();
            updateHiddenInputs();
            updateTotal();
            if (quantity > 1) {
              decreaseBtn.classList.remove('btn-opacity');
            }
          });
        });

        // Décrémentation button
        document.querySelectorAll('.btn-decrease').forEach(btn => {
          btn.addEventListener('click', (e) => {
            const parent = e.target.closest('.option');
            const img = parent.querySelector('img');
            const boisId = img.getAttribute('data-bois-id');
            const price = parseFloat(img.getAttribute('data-bois-prix')) || 0;
            const quantityInput = parent.querySelector('.quantity-input1');

            let quantity = parseInt(quantityInput.value) || 0;
            if (quantity > 1) {
              quantity--;
              quantityInput.value = quantity;
              selectedOptions[boisId] = quantity;
              saveSelectedOption(boisId, price, quantity);
              saveSelection();
              updateHiddenInputs();
              updateTotal();
            }
            if (quantity === 1) {
              btn.classList.add('btn-opacity');
            }
          });
        });

        // Etape suivante
        suivantButton.addEventListener('click', (e) => {
          e.preventDefault();
          const hasSelection = Object.keys(selectedOptions).length > 0;
          const hasValidQuantity = Object.values(selectedOptions).some(q => parseInt(q) > 0);
          if (!hasSelection || !hasValidQuantity) {
            erreurPopup.style.display = 'flex';
            return;
          }
          updateHiddenInputs();
          saveSelection();
          form.submit();
        });

        // Validation du formulaire + affichage pop up
        form.addEventListener('submit', (e) => {
          if (Object.keys(selectedOptions).length === 0) {
            e.preventDefault();
            erreurPopup.style.display = 'flex';
          } else {
            updateHiddenInputs();
          }
        });

        // Fermer le popup erreur
        closeErreurBtn.addEventListener('click', () => erreurPopup.style.display = 'none');
        window.addEventListener('click', (e) => {
          if (e.target === erreurPopup) erreurPopup.style.display = 'none';
        });
        restoreSelections();
        updateTotal();
      });
    </script>


    <!-- BOUTTON RETOUR -->
    <script>
      function retourEtapePrecedente() {
        window.location.href = "etape4-bois-decoration.php";
      }
    </script>

  </main>

  <?php require_once '../../squelette/footer.php' ?>
</body>


</html>