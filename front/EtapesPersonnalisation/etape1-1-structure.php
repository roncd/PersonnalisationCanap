<?php
require '../../admin/config.php';
session_start();


// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
  $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
  header("Location: ../formulaire/Connexion.php");
  exit;
}

// Récupérer les structures disponibles depuis la base de données
$stmt = $pdo->query("SELECT * FROM structure ORDER BY prix ASC");
$structures = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $id_client = $_SESSION['user_id'];
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
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/processus.css">
  <link rel="stylesheet" href="../../styles/popup.css">
  <link rel="stylesheet" href="../../styles/buttons.css">
  <script type="module" src="../../script/popup.js"></script>
  <script type="module" src="../../script/keydown.js"></script>

  <title>Étape 1.1 - Choisi ta structure</title>

</head>

<body data-user-id="<?php echo $_SESSION['user_id']; ?>" data-current-step="1-structure">
  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <main>
    <div class="fil-ariane-container h2" aria-label="fil-ariane" id="filAriane">
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
                data-structure-id="<?php echo $structure['id']; ?>"
                data-structure-prix="<?php echo $structure['prix']; ?>"
                data-category="structure">
              <p><?php echo htmlspecialchars($structure['nom']); ?></p>
              <p><strong><?php echo htmlspecialchars($structure['prix']); ?> €</strong></p>
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

    <!-- Popup info pratique -->
    <div id="popup-info" class="popup">
      <div class="popup-content">
        <h2>Informations pratiques</h2>
        <ul>
          <li>Livraison seulement en Île-de-France</li>
          <li>Paiement en magasin ou par virement après contact avec le vendeur</li>
          <li>Prix final n'inclus pas le prix de la livraison</li>
        </ul>
        <button id="close-popup" class="btn-noir">OK</button>
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

    <!-- Popup bloquant pour les étapes non validées -->
    <div id="filariane-popup" class="popup">
      <div class="popup-content">
        <h2>Veuillez cliquez sur "suivant" pour passer à l’étape d’après.</h2>
        <button class="btn-noir">OK</button>
      </div>
    </div>


    <script>
      //Pop up info pratique
      window.addEventListener("load", function() {
        const popup = document.getElementById("popup-info");
        const closeBtn = document.getElementById("close-popup");

        if (!sessionStorage.getItem("popupShown")) {
          popup.style.display = "flex"; 

          // Marque comme affiché
          sessionStorage.setItem("popupShown", "true");
        }

        closeBtn.addEventListener("click", () => {
          popup.style.display = "none";
        });
      });

      // GESTION DES SELECTIONS 
      document.addEventListener('DOMContentLoaded', () => {
        const options = document.querySelectorAll('.color-options .option img');
        const selectedStructureInput = document.getElementById('selected-structure');
        const mainImage = document.getElementById('main-image');
        const erreurPopup = document.getElementById('erreur-popup');
        const closeErreurBtn = erreurPopup.querySelector('.btn-noir');
        const form = document.querySelector('form');

        let savedStructureId = localStorage.getItem('selectedStructureId');
        let selected = savedStructureId !== '';

        function saveSelection() {
          localStorage.setItem('selectedStructureId', savedStructureId);
        }

        // Restaurer la sélection visuelle
        options.forEach(img => {
          if (img.getAttribute('data-structure-id') === savedStructureId) {
            img.classList.add('selected');
            mainImage.src = img.src;
            selectedStructureInput.value = savedStructureId;
          }
        });

        // Clic sur une structure
        options.forEach(img => {
          img.addEventListener('click', () => {
            options.forEach(opt => opt.classList.remove('selected'));
            img.classList.add('selected');
            mainImage.src = img.src;
            savedStructureId = img.getAttribute('data-structure-id');
            selectedStructureInput.value = savedStructureId;
            selected = true;
            saveSelection();

            if (typeof window.toggleOption === 'function') {
              window.toggleOption(img);
            } else {
              console.warn("⚠️ toggleOption n'est pas défini sur window !");
            }
          });
        });

        form.addEventListener('submit', (e) => {
          if (!selectedStructureInput.value) {
            e.preventDefault();
            erreurPopup.style.display = 'flex';
          }
        });

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
        const userId = document.body.getAttribute('data-user-id');
        const currentStep = document.body.getAttribute('data-current-step');

        if (!userId || !currentStep) {
          console.error("ID utilisateur ou étape actuelle manquants !");
          return;
        }

        const sessionKey = `allSelectedOptions_${userId}`;
        let allSelectedOptions = JSON.parse(sessionStorage.getItem(sessionKey)) || [];
        if (!Array.isArray(allSelectedOptions)) allSelectedOptions = [];

        function updateTotal() {
          const basePrice = parseFloat(document.querySelector('.base-price')?.textContent) || 0;
          const totalPrice = basePrice + allSelectedOptions.reduce((sum, opt) => {
            const price = opt.price || 0;
            const qty = opt.quantity || 1;
            return sum + price * qty;
          }, 0);

          const totalElement = document.querySelector(".footer p span");
          if (!totalElement) {
            console.warn("⚠️ Élément total introuvable !");
          } else {
            totalElement.textContent = `${totalPrice.toFixed(2)} €`;
            console.log(`💰 Total mis à jour : ${totalPrice.toFixed(2)} €`);
          }
        }


        function toggleOption(optionElement) {
          const idAttr = [...optionElement.attributes].find(attr =>
            attr.name.startsWith('data-') && attr.name.endsWith('-id')
          );
          const priceAttr = [...optionElement.attributes].find(attr =>
            attr.name.startsWith('data-') && attr.name.endsWith('-prix')
          );

          if (!idAttr || !priceAttr) {
            console.warn("❌ id ou prix manquant dans l'élément :", optionElement);
            return;
          }

          const optionId = optionElement.getAttribute(idAttr.name);
          const price = parseFloat(optionElement.getAttribute(priceAttr.name)) || 0;
          console.log(`🆔 Option sélectionnée : ${optionId}, prix : ${price}`);

          const uniqueId = `${currentStep}_${optionId}`;

          // Suppression des autres options
          const before = [...allSelectedOptions];
          allSelectedOptions = allSelectedOptions.filter(opt =>
            !(opt.id.startsWith(`${currentStep}_`) && optionElement.dataset.category === 'structure')
          );
          const removed = before.filter(opt => !allSelectedOptions.includes(opt));
          if (removed.length > 0) {
            console.log(`🧹 Ancienne structure supprimée :`, removed);
          }

          // Ajout de la nouvelle
          allSelectedOptions.push({
            id: uniqueId,
            price
          });
          console.log("✅ Nouvelle option enregistrée :", allSelectedOptions);

          sessionStorage.setItem(sessionKey, JSON.stringify(allSelectedOptions));
          updateTotal();
        }


        // ✅ Rendre accessible depuis le premier script
        window.toggleOption = toggleOption;

        document.querySelectorAll('img').forEach(img => {
          const hasPriceAttr = [...img.attributes].some(attr => attr.name.endsWith('-prix'));
          if (!hasPriceAttr) return;

          const idAttr = [...img.attributes].find(attr => attr.name.endsWith('-id'));
          const optionId = img.getAttribute(idAttr?.name || '') || '';
          const uniqueId = `${currentStep}_${optionId}`;

          if (allSelectedOptions.some(opt => opt.id === uniqueId)) {
            img.parentElement.classList.add('selected');
          }
        });

        updateTotal();
      });
    </script>



    <!-- FIL ARIANE -->
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const filAriane = document.querySelector('.fil-ariane');
        const links = filAriane.querySelectorAll('a');

        const filArianePopup = document.getElementById('filariane-popup');
        const closeFilArianePopupBtn = filArianePopup.querySelector('.btn-noir');

        const etapes = [{
            id: 'etape1-1-structure.php',
            key: null
          }, // toujours accessible
          {
            id: 'etape1-2-dimension.php',
            key: 'etape1-2_valide'
          },
          {
            id: 'etape2-type-banquette.php',
            key: 'etape2_valide'
          },
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