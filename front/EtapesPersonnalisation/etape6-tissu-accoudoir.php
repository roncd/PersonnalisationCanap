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
$stmt = $pdo->query("SELECT * FROM accoudoir_tissu");
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
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/processus.css">
  <link rel="stylesheet" href="../../styles/popup.css">
  <script type="module" src="../../script/popup.js"></script>
  <script type="module" src="../../script/variationPrix.js"></script>

  <title>Étape 6 - Ajoute tes accoudoirs</title>

</head>

<body data-user-id="<?php echo $_SESSION['user_id']; ?>" data-current-step="6-accoudoir-tissu">

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
        <li><a href="etape5-tissu-dossier.php">Dossier</a></li>
        <li><a href="etape6-tissu-accoudoir.php" class="active">Accoudoir</a></li>
        <li><a href="etape7-tissu-mousse.php">Mousse</a></li>
      </ul>
    </div>

    <div class="container">
      <!-- Colonne de gauche -->
      <div class="left-column transition">
        <h2>Étape 6 - Ajoute tes accoudoirs</h2>
        <section class="color-2options">
          <?php if (!empty($accoudoir_tissu)): ?>
            <?php foreach ($accoudoir_tissu as $accoudoir): ?>
              <div class="option transition">
                <img src="../../admin/uploads/accoudoirs-tissu/<?php echo htmlspecialchars($accoudoir['img']); ?>"
                  alt="<?php echo htmlspecialchars($accoudoir['nom']); ?>"
                  data-accoudoir-id="<?php echo $accoudoir['id']; ?>"
                  data-accoudoir-prix="<?php echo $accoudoir['prix']; ?>">
                <p><?php echo htmlspecialchars($accoudoir['nom']); ?></p>
                <p><strong><?php echo htmlspecialchars($accoudoir['prix']); ?> €</strong></p>

                <!-- Compteur de quantité (maintenant à l'intérieur de .option) -->
                <div class="quantity-selector">
                  <button class="decrease-btn" onclick="updateQuantity(this, -1)">-</button>
                  <input type="text" class="quantity-input" value="0" readonly>
                  <button class="increase-btn" onclick="updateQuantity(this, 1)">+</button>

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
            <button onclick="retourEtapePrecedente()" class="btn-retour transition">Retour</button>
            <form method="POST" action="">
              <input type="hidden" name="accoudoir_tissu_id" id="selected-accoudoir_tissu" required>
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
    <!-- Popup d'erreur si option non sélectionnée -->
    <div id="erreur-popup" class="popup transition">
      <div class="popup-content">
        <h2>Veuillez choisir une option avant de continuer.</h2>
        <button class="close-btn">OK</button>
      </div>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const options = document.querySelectorAll('.color-2options .option img');
        const selectedAccoudoirTissuInput = document.getElementById('selected-accoudoir_tissu');
        const selectedNbAccoudoirInput = document.getElementById('selected-nb_accoudoir'); // assure-toi que cet input existe
        const mainImage = document.querySelector('.main-display img');
        const suivantButton = document.querySelector('.btn-suivant');
        const erreurPopup = document.getElementById('erreur-popup');
        const closeErreurBtn = erreurPopup.querySelector('.close-btn');
        const form = document.querySelector('form');

        let selected = false;

        document.querySelectorAll('.transition').forEach(element => {
          element.classList.add('show');
        });

        function saveSelectionToLocalStorage(selectedImage) {
          const selectedAccoudoirId = selectedImage.getAttribute('data-accoudoir-id');
          const quantityInput = selectedImage.closest('.option').querySelector('.quantity-input');
          localStorage.setItem('selectedAccoudoir', selectedAccoudoirId);
          localStorage.setItem('accoudoirQuantity', quantityInput.value);
        }

        function loadSelectionFromLocalStorage() {
          const selectedAccoudoirId = localStorage.getItem('selectedAccoudoir');
          const accoudoirQuantity = localStorage.getItem('accoudoirQuantity');

          if (selectedAccoudoirId && accoudoirQuantity) {
            const selectedImage = document.querySelector(`.color-2options .option img[data-accoudoir-id="${selectedAccoudoirId}"]`);

            if (selectedImage) {
              selectedImage.classList.add('selected');
              mainImage.src = selectedImage.src;
              mainImage.alt = selectedImage.alt;
              selectedAccoudoirTissuInput.value = selectedAccoudoirId;

              const quantityInput = selectedImage.closest('.option').querySelector('.quantity-input');
              quantityInput.value = accoudoirQuantity;

              selected = true;
              selectedNbAccoudoirInput.value = accoudoirQuantity;
            }
          }
        }

        loadSelectionFromLocalStorage();

        options.forEach(img => {
          img.addEventListener('click', () => {
            options.forEach(opt => opt.classList.remove('selected'));
            options.forEach(opt => {
              const quantityInput = opt.closest('.option').querySelector('.quantity-input');
              if (quantityInput) quantityInput.value = 0;
            });

            img.classList.add('selected');
            mainImage.src = img.src;
            mainImage.alt = img.alt;

            selectedAccoudoirTissuInput.value = img.getAttribute('data-accoudoir-id');
            selected = true;

            const quantityInput = img.closest('.option').querySelector('.quantity-input');
            if (quantityInput) quantityInput.value = 1;

            saveSelectionToLocalStorage(img);
          });
        });

        const decreaseButtons = document.querySelectorAll('.decrease-btn');
        const increaseButtons = document.querySelectorAll('.increase-btn');

        // BOUTON SUIVANT : vérifie sélection + quantité
        suivantButton.addEventListener('click', (event) => {
          event.preventDefault();

          const selectedOption = document.querySelector('.option img.selected');

          if (!selectedOption) {
            erreurPopup.style.display = 'flex';
            return;
          }

          const quantityInput = selectedOption.closest('.option').querySelector('.quantity-input');
          const quantity = parseInt(quantityInput.value);

          if (quantity <= 0 || isNaN(quantity)) {
            erreurPopup.style.display = 'flex';
            return;
          }

          selectedNbAccoudoirInput.value = quantity;
          form.submit();
        });

        // FERMETURE DU POPUP
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


    <!-- VARIATION DES PRIX  -->
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        let totalPrice = 0; // Total global pour toutes les étapes
        const currentStep = "6-accoudoir-tissu"; // Étape actuelle
        const userId = document.body.getAttribute('data-user-id');
        const sessionKey = `allSelectedOptions_${userId}`;
        const selectedKey = `selectedOptions_${userId}`;
        let allSelectedOptions = JSON.parse(sessionStorage.getItem(sessionKey)) || [];
        let selectedOptions = JSON.parse(sessionStorage.getItem(selectedKey)) || {};
        const selectedAccoudoirTissuInput = document.getElementById('selected-accoudoir_tissu');
        const selectedNbAccoudoirInput = document.getElementById('selected-nb_accoudoir');

        // Vérifications initiales
        if (!userId) {
          console.error("ID utilisateur non trouvé.");
          return;
        }

        // Restaurer les sélections depuis le localStorage/sessionStorage
        function restoreSelections() {
          document.querySelectorAll('.color-2options .option img').forEach(img => {
            const accoudoirId = img.getAttribute('data-accoudoir-id');
            const parentOption = img.closest('.option');
            const quantityInput = parentOption.querySelector('.quantity-input');

            if (selectedOptions[accoudoirId]) {
              img.classList.add('selected');
              quantityInput.value = selectedOptions[accoudoirId];
            } else {
              img.classList.remove('selected');
              quantityInput.value = 0;
            }
          });
          updateTotal();
        }

        // Fonction pour mettre à jour le total global
        function updateTotal() {
          totalPrice = allSelectedOptions.reduce((sum, option) => {
            const price = option.price || 0;
            const quantity = option.quantity || 1;
            return sum + (price * quantity);
          }, 0);

          const totalElement = document.querySelector(".footer p span");
          if (totalElement) {
            totalElement.textContent = `${totalPrice.toFixed(2)} €`;
          }
        }

        // Fonction pour sauvegarder les sélections dans sessionStorage
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

        // Gestion des clics sur les options
        document.querySelectorAll('.color-2options .option img').forEach(img => {
          img.addEventListener('click', () => {
            const accoudoirId = img.getAttribute('data-accoudoir-id');
            const parentOption = img.closest('.option');
            const quantityInput = parentOption.querySelector('.quantity-input');
            const price = parseFloat(img.getAttribute('data-accoudoir-prix')) || 0;

            // Désélectionner ou sélectionner
            if (selectedOptions[accoudoirId]) {
              delete selectedOptions[accoudoirId];
              img.classList.remove('selected');
              quantityInput.value = 0;
              saveSelectedOption(accoudoirId, price, 0);
            } else {
              selectedOptions[accoudoirId] = 1;
              img.classList.add('selected');
              quantityInput.value = 1;
              saveSelectedOption(accoudoirId, price, 1);
            }

            sessionStorage.setItem(selectedKey, JSON.stringify(selectedOptions));
            updateHiddenInputs();
            updateTotal();
          });
        });

        // Gestion des boutons augmenter et diminuer
        document.querySelectorAll('.increase-btn, .decrease-btn').forEach(button => {
          button.addEventListener('click', (event) => {
            const parentOption = event.target.closest('.option');
            const accoudoirId = parentOption.querySelector('img').getAttribute('data-accoudoir-id');
            const quantityInput = parentOption.querySelector('.quantity-input');
            const price = parseFloat(parentOption.querySelector('img').getAttribute('data-accoudoir-prix')) || 0;

            if (!selectedOptions[accoudoirId]) return;

            let newQuantity = parseInt(quantityInput.value) || 0;
            newQuantity += event.target.classList.contains('increase-btn') ? 1 : -1;
            newQuantity = Math.max(newQuantity, 0);
            quantityInput.value = newQuantity;

            if (newQuantity === 0) {
              delete selectedOptions[accoudoirId];
              parentOption.querySelector('img').classList.remove('selected');
              saveSelectedOption(accoudoirId, price, 0);
            } else {
              selectedOptions[accoudoirId] = newQuantity;
              saveSelectedOption(accoudoirId, price, newQuantity);
            }

            sessionStorage.setItem(selectedKey, JSON.stringify(selectedOptions));
            updateHiddenInputs();
            updateTotal();
          });
        });

        // Mise à jour des champs cachés
        function updateHiddenInputs() {
          selectedAccoudoirTissuInput.value = Object.keys(selectedOptions).join(',');
          selectedNbAccoudoirInput.value = Object.values(selectedOptions).join(',');
        }

        // Restaurer les sélections au chargement
        restoreSelections();
      });
    </script>


    <!-- BOUTTON RETOUR -->
    <script>
      function retourEtapePrecedente() {
        // Exemple : tu es sur étape 8, tu veux revenir à étape 7
        window.location.href = "etape5-tissu-dossier.php";
      }
    </script>




  </main>
  <?php require_once '../../squelette/footer.php'; ?>

</body>

</html>