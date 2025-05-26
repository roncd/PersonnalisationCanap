<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
  header("Location: ../formulaire/Connexion.php");
  exit;
}

// Récupérer les banquettes disponibles depuis la base de données
$stmt = $pdo->query("SELECT * FROM type_banquette");
$banquettes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $id_client = $_SESSION['user_id'];
  $id_banquette = $_POST['banquette_id'];
  $banquette_type = $_POST['banquette_type'];

  // Vérifier si une commande temporaire existe déjà pour cet utilisateur
  $stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ?");
  $stmt->execute([$id_client]);
  $existing_order = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($existing_order) {
    $stmt = $pdo->prepare("UPDATE commande_temporaire SET id_banquette = ? WHERE id_client = ?");
    $stmt->execute([$id_banquette, $id_client]);
  } else {
    $stmt = $pdo->prepare("INSERT INTO commande_temporaire (id_client, id_banquette) VALUES (?, ?)");
    $stmt->execute([$id_client, $id_banquette]);
  }

  // Redirection en fonction du type de banquette
  if ($banquette_type === "Bois") {
    header("Location: etape3-bois-couleur.php");
  } elseif ($banquette_type === "Tissu") {
    header("Location: etape3-tissu-modele-banquette.php");
  } else {
    header("Location: etape1-2-dimension.php");
  }
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

  <title>Étape 2 - Choisi ton type de banquette</title>

  <style>
    .transition {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.5s ease, transform 0.5s ease;
    }

    .transition.show {
      opacity: 1;
      transform: translateY(0);
    }

    .option img.selected {
      border: 3px solid #997765;
      border-radius: 5px;
      box-sizing: border-box;
    }
  </style>

</head>

<body data-user-id="<?php echo $_SESSION['user_id']; ?>">
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <main>
    <div class="fil-ariane-container h2" aria-label="fil-ariane">
      <ul class="fil-ariane">
        <li><a href="etape1-1-structure.php">Structure</a></li>
        <li><a href="etape1-2-dimension.php">Dimension</a></li>
        <li><a href="etape2-type-banquette.php" class="active">Banquette</a></li>
      </ul>
    </div>
    <div class="container">
      <div class="left-column transition">
        <h2>Étape 2 - Choisi ton type de banquette</h2>

        <section class="color-2options">
          <?php foreach ($banquettes as $banquette): ?>
            <div class="option transition">
              <img src="../../admin/uploads/banquette/<?php echo htmlspecialchars($banquette['img']); ?>"
                alt="<?php echo htmlspecialchars($banquette['nom']); ?>"
                data-banquette-id="<?php echo $banquette['id']; ?>"
                data-banquette-type="<?php echo htmlspecialchars($banquette['nom']); ?>">
              <p><?php echo htmlspecialchars($banquette['nom']); ?></p>
            </div>
          <?php endforeach; ?>
        </section>

        <div class="footer">
          <p>Total : <span>899 €</span></p>
          <div class="buttons">
          <button onclick="retourEtapePrecedente()" class="btn-retour transition">Retour</button>
          <form method="POST" action="">
              <input type="hidden" name="banquette_id" id="selected-banquette">
              <input type="hidden" name="banquette_type" id="selected-banquette-type">
              <button type="submit" class="btn-suivant transition">Suivant</button>
          </form>
          </div>
        </div>
      </div>

      <div class="right-column transition">
        <section class="main-display">
          <div class="buttons transition">
            <button class="btn-aide">Besoin d'aide ?</button>
            <button class="btn-abandonner">Abandonner</button>
          </div>
          <img id="main-image" src="../../medias/process-main-image.png" alt="Banquette sélectionnée"
            class="transition">
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

<!-- Popup d'erreur si option non selectionnée -->
<div id="erreur-popup" class="popup transition">
      <div class="popup-content">
        <h2>Veuillez choisir une option avant de continuer.</h2>
        <button class="close-btn">OK</button>
      </div>
    </div>
    
    <!-- GESTION DES SELECTIONS -->
    <script>document.addEventListener('DOMContentLoaded', () => {
  const options = document.querySelectorAll('.color-2options .option img');
  const selectedBanquetteInput = document.getElementById('selected-banquette');
  const selectedBanquetteTypeInput = document.getElementById('selected-banquette-type');
  const mainImage = document.getElementById('main-image');
  const erreurPopup = document.getElementById('erreur-popup');
  const closeErreurBtn = erreurPopup.querySelector('.close-btn');
  const form = document.querySelector('form');

  let savedBanquetteId = localStorage.getItem('selectedBanquetteId');
  let savedBanquetteType = localStorage.getItem('selectedBanquetteType');

  // Appliquer les transitions
  document.querySelectorAll('.transition').forEach(el => el.classList.add('show'));

  // Restaurer la sélection si elle existe
  options.forEach(img => {
    if (img.getAttribute('data-banquette-id') === savedBanquetteId &&
        img.getAttribute('data-banquette-type') === savedBanquetteType) {
      img.classList.add('selected');
      mainImage.src = img.src;
      selectedBanquetteInput.value = savedBanquetteId;
      selectedBanquetteTypeInput.value = savedBanquetteType;
    }
  });

  // Gérer le clic sur une option
  options.forEach(img => {
    img.addEventListener('click', () => {
      options.forEach(opt => opt.classList.remove('selected'));
      img.classList.add('selected');
      mainImage.src = img.src;

      const id = img.getAttribute('data-banquette-id');
      const type = img.getAttribute('data-banquette-type');

      selectedBanquetteInput.value = id;
      selectedBanquetteTypeInput.value = type;

      localStorage.setItem('selectedBanquetteId', id);
      localStorage.setItem('selectedBanquetteType', type);
    });
  });

  // Empêcher la soumission si rien n'est sélectionné
  form.addEventListener('submit', (e) => {
    if (!selectedBanquetteInput.value || !selectedBanquetteTypeInput.value) {
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


    <!-- VARIATION DES PRIX  -->
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        let totalPrice = 0; // Total global pour toutes les étapes

        // Charger l'ID utilisateur depuis une variable PHP intégrée dans le HTML
        const userId = document.body.getAttribute('data-user-id'); // Ex. <body data-user-id="<?php echo $_SESSION['user_id']; ?>">
        if (!userId) {
          console.error("ID utilisateur non trouvé. Vérifiez que 'data-user-id' est bien défini dans le HTML.");
          return;
        }

        // Charger toutes les options sélectionnées depuis sessionStorage (par utilisateur)
        const sessionKey = `allSelectedOptions_${userId}`;
        const selectedKey = `selectedOptions_${userId}`;
        let allSelectedOptions = JSON.parse(sessionStorage.getItem(sessionKey)) || [];
        let selectedOptions = JSON.parse(sessionStorage.getItem(selectedKey)) || {}; // Charger `selectedOptions` pour cet utilisateur

        // Vérifier si `allSelectedOptions` est un tableau
        if (!Array.isArray(allSelectedOptions)) {
          allSelectedOptions = [];
          console.warn("allSelectedOptions n'était pas un tableau. Réinitialisé à []");
        }

        // Fonction pour mettre à jour le total global
        function updateTotal() {
          // Calculer le total global en prenant en compte les quantités
          totalPrice = allSelectedOptions.reduce((sum, option) => {
            const price = option.price || 0; // S'assurer que le prix est valide
            const quantity = option.quantity || 1; // Par défaut, quantité = 1
            return sum + (price * quantity);
          }, 0);

          console.log("Total global :", totalPrice);

          // Mettre à jour le total dans l'interface
          const totalElement = document.querySelector(".footer p span");
          if (totalElement) {
            totalElement.textContent = `${totalPrice.toFixed(2)} €`;
          } else {
            console.error("L'élément '.footer p span' est introuvable !");
          }
        }

        // Sauvegarder les données mises à jour dans sessionStorage
        function saveData() {
          sessionStorage.setItem(sessionKey, JSON.stringify(allSelectedOptions));
          sessionStorage.setItem(selectedKey, JSON.stringify(selectedOptions));
        }

        // Initialiser le total dès le chargement de la page
        updateTotal();

        // Sauvegarder les données au chargement de la page (au cas où elles sont modifiées)
        saveData();
      });
    </script>


  
      <!-- BOUTTON RETOUR -->
      <script>
       function retourEtapePrecedente() {
    // Exemple : tu es sur étape 8, tu veux revenir à étape 7
    window.location.href = "etape1-2-dimension.php"; 
  }
    </script>
  </main>

  <?php require_once '../../squelette/footer.php'; ?>
</body>

</html>