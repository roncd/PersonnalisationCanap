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
        window.location.href = 'etape5-bois-accoudoir-place.php';
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
  <link rel="stylesheet" href="../../styles/buttons.css">
  <script type="module" src="../../script/popup.js"></script>
  <script type="module" src="../../script/keydown.js"></script>


  <title>Étape 5 - Ajoute des accoudoirs</title>

</head>


<body data-user-id="<?php echo $_SESSION['user_id']; ?>" data-current-step="5-accoudoir-bois">
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
        <div style="display: flex; align-items: center; gap: 0.5em;">
        <h2>Étape 5 - Choisi ta forme d'accoudoirs</h2>
        <!-- <label for="deco">Voulez vous ajouter la décoration (choisi avant) sur l'accoudoir ?</label>
        <select class="select-field" id="deco" name="deco">
          <option value="Non" selected>Non</option>
          <option value="oui">Oui</option>
        </select> -->
        <!-- Icône d'information avec popup -->
          <button id="info-coussin-btn" title="Information" style="background: none; border: none; cursor: pointer; padding: 0;">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
              <circle cx="10" cy="10" r="9" stroke="#997765" stroke-width="2" fill="#f5f5f5"/>
              <text x="10" y="15" text-anchor="middle" font-size="13" fill="#997765" font-family="Arial" font-weight="bold">i</text>
            </svg>
          </button>
        </div>
        <div id="info-coussin-popup" style="display:none; position:absolute; background:#fff; border:1px solid #ccc; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1); padding:1em; z-index:1000; max-width:300px;">
          <span style="font-size: 1em;">Forme au choix, les décorations seront celles sélectionnées précédement.</span>
        </div>
      
      <script>
document.addEventListener('DOMContentLoaded', () => {
  const btn   = document.getElementById('info-coussin-btn');
  const popup = document.getElementById('info-coussin-popup');

  btn.addEventListener('click', e => {
    e.stopPropagation();

    /* ── toggle affichage ───────────────────────── */
    const visible = popup.style.display === 'block';
    popup.style.display = visible ? 'none' : 'block';
    if (visible) return;   // on vient de fermer : pas besoin de repositionner

    /* ── coordonnées du bouton ──────────────────── */
    const rect    = btn.getBoundingClientRect();
    const scrollY = window.scrollY || window.pageYOffset;
    const scrollX = window.scrollX || window.pageXOffset;

    /* ── positionner AU‑DESSUS et 8 px À GAUCHE du “i” ── */
    const top  = scrollY + rect.top  - popup.offsetHeight - 10; // 8 px au‑dessus
    const left = scrollX + rect.left - popup.offsetWidth - 10;  // 8 px à gauche

    popup.style.top  = `${top}px`;
    popup.style.left = `${left}px`;
  });

  /* ── clic extérieur : fermer le popup ─────────── */
  document.addEventListener('click', e => {
    if (!popup.contains(e.target) && e.target !== btn) {
      popup.style.display = 'none';
    }
  });
});
</script>
        <section class="color-options">
          <?php if (!empty($accoudoir_bois)): ?>
            <?php foreach ($accoudoir_bois as $bois): ?>
              <div class="option">
                <img src="../../admin/uploads/accoudoirs-bois/<?php echo htmlspecialchars($bois['img']); ?>"
                  alt="<?php echo htmlspecialchars($bois['nom']); ?>" data-bois-id="<?php echo $bois['id']; ?>"
                  data-bois-prix="<?php echo $bois['prix']; ?>"
                  data-can-deselect="true">
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
            <button onclick="retourEtapePrecedente()" class="btn-beige">Retour</button>
            <form method="POST" action="">
              <input type="hidden" name="accoudoir_bois_id" id="selected-accoudoir_bois">
              <input type="hidden" name="nb_accoudoir" id="selected-nb_accoudoir" required>
              <button type="submit" id="btn-suivant" class="btn-noir">Suivant</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Colonne de droite -->
      <div class="right-column">
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
    <div id="help-popup" class="popup">
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
    <div id="abandonner-popup" class="popup">
      <div class="popup-content">
        <h2>Êtes vous sûr de vouloir abandonner ?</h2>
        <br>
        <button class="btn-beige">Oui...</button>
        <button class="btn-noir">Non !</button>
      </div>
    </div>

    <!-- Popup d'erreur si option non selectionnée -->
    <div id="erreur-popup" class="popup">
      <div class="popup-content">
        <h2>Veuillez choisir une option avant de continuer.</h2>
        <button class="btn-noir">OK</button>
      </div>
    </div>

    <!-- Popup maximum 2 accoudoirs -->
    <div id="accoudoir-popup" class="popup">
      <div class="popup-content">
        <h2>Vous devez choisir maximum 2 accoudoirs.</h2>
        <button class="btn-noir">OK</button>
      </div>
    </div>

        <!-- Popup bloquant pour les étapes non validées -->
<div id="filariane-popup" class="popup">
  <div class="popup-content">
    <h2>Veuillez sélectionner une option et cliquez sur "suivant" pour passer à l’étape d’après.</h2>
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
        const accoudoirPopup = document.getElementById('accoudoir-popup');
        const closeBtn = accoudoirPopup.querySelector('.btn-noir');
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

        if (!Array.isArray(allSelectedOptions)) {
          allSelectedOptions = [];
        }

        // Déclaration des fonctions
        function getTotalAccoudoirs() {
          const currentAccoudoirIds = Array.from(document.querySelectorAll('.option img[data-bois-id]'))
            .map(img => img.getAttribute('data-bois-id'));

          return currentAccoudoirIds.reduce((total, boisId) => {
            const qty = parseInt(selectedOptions[boisId]) || 0;
            return total + qty;
          }, 0);
        }

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
              price,
              quantity
            });
          }
          sessionStorage.setItem(sessionKey, JSON.stringify(allSelectedOptions));
        }

        function updateTotal() {
          const total = allSelectedOptions.reduce((sum, opt) => sum + (opt.price || 0) * (opt.quantity || 1), 0);
          const totalElement = document.querySelector(".footer p span");
          if (totalElement) totalElement.textContent = `${total.toFixed(2)} €`;
        }

        function restoreSelections() {
          options.forEach(img => {
            const boisId = img.getAttribute('data-bois-id');
            const parent = img.closest('.option');
            const quantityInput = parent.querySelector('.quantity-input1');
            const quantitySelector = parent.querySelector('.quantity-selector1');

            if (selectedOptions[boisId]) {
              img.classList.add('selected');
              quantityInput.value = selectedOptions[boisId];
              if (quantitySelector) quantitySelector.style.display = "block";
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

        function updateAllIncreaseButtons() {
          const totalAccoudoirs = getTotalAccoudoirs();

          document.querySelectorAll('.option').forEach(option => {
            const img = option.querySelector('img');
            const boisId = img.getAttribute('data-bois-id');
            const increaseBtn = option.querySelector('.btn-increase');
            const decreaseBtn = option.querySelector('.btn-decrease');
            const quantityInput = option.querySelector('.quantity-input1');
            const quantity = parseInt(quantityInput.value) || 0;

            // Gestion bouton +
            if (totalAccoudoirs >= 2 && quantity < 2) {
              increaseBtn.classList.add('btn-opacity');
            } else if (quantity >= 2) {
              increaseBtn.classList.add('btn-opacity');
            } else {
              increaseBtn.classList.remove('btn-opacity');
            }

            // Gestion bouton -
            if (quantity <= 1) {
              decreaseBtn.classList.add('btn-opacity');
            } else {
              decreaseBtn.classList.remove('btn-opacity');
            }
          });
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
            const increaseBtn = parent.querySelector('.btn-increase');

            if (img.classList.contains('selected')) {
              img.classList.remove('selected');
              quantityInput.value = 0;
              delete selectedOptions[boisId];
              if (quantitySelector) quantitySelector.style.display = "none";
              localStorage.removeItem('lastSelectedAccoudoir');
              saveSelectedOption(boisId, price, 0);
            } else {
              const totalAccoudoirs = getTotalAccoudoirs();
              if (totalAccoudoirs >= 2) {
                accoudoirPopup.style.display = 'flex';
                // Fermer le popup accoudoir
                closeBtn.addEventListener('click', () => {
                  accoudoirPopup.style.display = 'none';
                });

                window.addEventListener('click', (event) => {
                  if (event.target === accoudoirPopup) {
                    accoudoirPopup.style.display = 'none';
                  }
                });
                return;
              }

              img.classList.add('selected');
              quantityInput.value = 1;
              selectedOptions[boisId] = 1;
              if (quantitySelector) quantitySelector.style.display = "block";
              localStorage.setItem('lastSelectedAccoudoir', boisId);
              mainImage.src = img.src;
              mainImage.alt = img.alt;
              saveSelectedOption(boisId, price, 1);

              if (decreaseBtn) decreaseBtn.classList.add('btn-opacity');
              if (increaseBtn && parseInt(quantityInput.value) === 2) {
                increaseBtn.classList.add('btn-opacity');
              }
            }
            updateHiddenInputs();
            saveSelection();
            updateTotal();
            updateAllIncreaseButtons();
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

            let currentQuantity = parseInt(quantityInput.value) || 0;
            const totalAccoudoirs = getTotalAccoudoirs();

            // Calculer la quantité totale si on augmente
            if (totalAccoudoirs >= 2) {
              btn.classList.add('btn-opacity');
              return;
            }

            if (currentQuantity < 2) {
              currentQuantity++;
              quantityInput.value = currentQuantity;
              selectedOptions[boisId] = currentQuantity;
              saveSelectedOption(boisId, price, currentQuantity);
              saveSelection();
              updateHiddenInputs();
              updateTotal();
              updateAllIncreaseButtons();

              // Gestion de la classe sur button
              if (currentQuantity === 2 || getTotalAccoudoirs() === 2) {
                btn.classList.add('btn-opacity');
              } else {
                btn.classList.remove('btn-opacity');
              }

              if (currentQuantity > 1) {
                decreaseBtn.classList.remove('btn-opacity');
              }
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
            const increaseBtn = parent.querySelector('.btn-increase');

            let currentQuantity = parseInt(quantityInput.value) || 0;

            if (currentQuantity > 1) {
              currentQuantity--;
              quantityInput.value = currentQuantity;
              selectedOptions[boisId] = currentQuantity;
              saveSelectedOption(boisId, price, currentQuantity);
              saveSelection();
              updateHiddenInputs();
              updateTotal();
              updateAllIncreaseButtons();

              // Gestion des classes
              if (currentQuantity === 1) {
                btn.classList.add('btn-opacity');
              }
              increaseBtn.classList.remove('btn-opacity');
            }
          });
        });

        // Etape suivante
        suivantButton.addEventListener('click', (e) => {
          e.preventDefault();
          const total = getTotalAccoudoirs();
          if (total === 0) {
            erreurPopup.style.display = 'flex';
            return;
          }
          updateHiddenInputs();
          saveSelection();
          form.submit();
        });

        // Validation du formulaire + affichage pop up
        form.addEventListener('submit', (e) => {
          if (getTotalAccoudoirs() === 0) {
            e.preventDefault();
            erreurPopup.style.display = 'flex';
          } else {
            updateHiddenInputs();
          }
        });

        // Fermer le popup erreur
        closeErreurBtn.addEventListener('click', () => {
          erreurPopup.style.display = 'none';
        });

        window.addEventListener('click', (event) => {
          if (event.target === erreurPopup) {
            erreurPopup.style.display = 'none';
          }
        });

        restoreSelections();
        updateTotal();
        updateAllIncreaseButtons();
      });
    </script>


    <!-- BOUTTON RETOUR -->
    <script>
      function retourEtapePrecedente() {
        window.location.href = "etape4-bois-decoration.php";
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
    { id: 'etape1-2-dimension.php', key: null },
    { id: 'etape2-type-banquette.php', key: null },
    { id: 'etape3-bois-couleur.php', key: null },
    { id: 'etape4-bois-decoration.php', key: null },
    { id: 'etape5-bois-accoudoir.php', key: null },
    { id: 'etape6-bois-dossier.php', key: 'etape6_valide' },
    { id: 'etape7-1-bois-tissu.php', key: 'etape7_valide' },
    { id: 'etape8-bois-mousse.php', key: 'etape8_valide' },
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

  <?php require_once '../../squelette/footer.php' ?>
</body>


</html>