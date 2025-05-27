<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
  header("Location: ../formulaire/Connexion.php");
  exit;
}

// Récupérer les structures disponibles depuis la base de données
$stmt = $pdo->query("SELECT * FROM structure");
$structures = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $id_client = $_SESSION['user_id']; // Assurez-vous que user_id correspond bien à id_client
  $id_structure = $_POST['structure_id'];

  // Vérifier si une commande temporaire existe déjà pour cet utilisateur
  $stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ?");
  $stmt->execute([$id_client]);
  $existing_order = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($existing_order) {
    // Mise à jour de la structure sélectionnée
    $stmt = $pdo->prepare("UPDATE commande_temporaire SET id_structure = ? WHERE id_client = ?");
    $stmt->execute([$id_structure, $id_client]);
  } else {
    // Création d'une nouvelle commande temporaire
    $stmt = $pdo->prepare("INSERT INTO commande_temporaire (id_client, id_structure) VALUES (?, ?)");
    $stmt->execute([$id_client, $id_structure]);
  }

  // Rediriger vers l'étape suivante
  header("Location: etape1-2-dimension.php?structure_id=" . $id_structure);
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

  <title>Étape 1.1 - Choisi ta structure</title>

</head>

<body data-user-id="<?php echo $_SESSION['user_id']; ?>">

  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <main>
    <div class="fil-ariane-container h2" aria-label="fil-ariane">
      <ul class="fil-ariane">
        <li><a href="etape1-1-structure.php" class="active">Structure</a></li>
        <li><a href="etape1-2-dimension.php">Dimension</a></li>
        <li><a href="etape2-type-banquette.php">Banquette</a></li>
      </ul>
    </div>
    <div class="container transition">
      <div class="left-column ">
        <h2>Étape 1.1 - Choisi ta structure</h2>

        <section class="color-options">
          <?php foreach ($structures as $structure): ?>
            <div class="option " data-nb-longueurs="<?php echo htmlspecialchars($structure['nb_longueurs']); ?>">
              <img src="../../admin/uploads/structure/<?php echo htmlspecialchars($structure['img']); ?>"
                alt="<?php echo htmlspecialchars($structure['nom']); ?>"
                data-structure-id="<?php echo $structure['id']; ?>">
              <p><?php echo htmlspecialchars($structure['nom']); ?></p>
            </div>
          <?php endforeach; ?>
        </section>


        <div class="footer">
          <p>Total : <span>0 €</span></p>
          <div class="buttons">
            <form method="POST" action="">
              <input type="hidden" name="structure_id" id="selected-structure">
              <button type="submit" id="btn-suivant" class="btn-noir">Suivant</button>
            </form>
          </div>
        </div>
      </div>

      <div class="right-column ">
        <section class="main-display">
          <div class="buttons ">
            <button id="btn-aide" class="btn-beige">Besoin d'aide ?</button>
            <button type="button" data-url="../pages/dashboard.php" id="btn-abandonner" class="btn-noir">Abandonner</button>
          </div>
          <img id="main-image" src="../../medias/process-main-image.png" alt="Banquette sélectionnée">
        </section>
      </div>
    </div>


    <!-- POPUP BESOIN D'AIDE -->
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

    <!-- POPUP ABANDONNER -->
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

    <!-- GESTION DES SELECTIONS -->
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const options = document.querySelectorAll('.color-options .option img');
        const selectedStructureInput = document.getElementById('selected-structure');
        const mainImage = document.getElementById('main-image');
        const erreurPopup = document.getElementById('erreur-popup');
        const closeErreurBtn = erreurPopup.querySelector('.btn-noir');
        const form = document.querySelector('form'); // Assure-toi que ton <form> a bien une balise identifiable

        // Vérification si une sélection existe dans localStorage
        let savedStructureId = localStorage.getItem('selectedStructureId');
        let selected = savedStructureId !== '';

        // Restaurer la sélection si elle existe
        options.forEach(img => {
          if (img.getAttribute('data-structure-id') === savedStructureId) {
            img.classList.add('selected');
            mainImage.src = img.src;
            selectedStructureInput.value = savedStructureId;
          }
        });

        // Gestion du clic sur une option
        options.forEach(img => {
          img.addEventListener('click', () => {
            options.forEach(opt => opt.classList.remove('selected'));
            img.classList.add('selected');
            mainImage.src = img.src;
            savedStructureId = img.getAttribute('data-structure-id');
            selectedStructureInput.value = savedStructureId;
            selected = true;
            saveSelection();
          });
        });

        // Empêcher la soumission du formulaire si rien n'est sélectionné
        form.addEventListener('submit', (e) => {
          if (!selectedStructureInput.value) {
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

        function saveSelection() {
          localStorage.setItem('selectedStructureId', savedStructureId);
        }
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


  </main>
  <?php require_once '../../squelette/footer.php'; ?>
</body>

</html>