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

$selectedTissuId = $_SESSION['id_couleur_tissu_bois'];


// Récupérer les motifs de bois depuis la base de données
$stmt = $pdo->prepare("SELECT * FROM motif_bois WHERE id_couleur_tissu = ? ORDER BY prix ASC");
$stmt->execute([$selectedTissuId]);
$motif_bois = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $id_client = $_SESSION['user_id'];
  $id_motif_bois = $_POST['motif_bois_id'];

  // Vérifier si une commande temporaire existe déjà pour cet utilisateur
  $stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ?");
  $stmt->execute([$id_client]);
  $existing_order = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($existing_order) {
    $stmt = $pdo->prepare("UPDATE commande_temporaire SET id_motif_bois = ? WHERE id_client = ?");
    $stmt->execute([$id_motif_bois, $id_client]);
  } else {
    $stmt = $pdo->prepare("INSERT INTO commande_temporaire (id_client, id_motif_bois) VALUES (?, ?)");
    $stmt->execute([$id_client, $id_motif_bois]);
  }

  // Rediriger vers l'étape suivante
  header("Location: etape8-bois-mousse.php");
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
  <script type="module" src="../../script/pathSwitchReset.js"></script>
  <script type="module" src="../../script/variationPrix.js"></script>
  <script type="module" src="../../script/keydown.js"></script>


  <title>Étape 7.2 - Choisi ton kit de coussinss</title>

</head>

<body data-user-id="<?php echo $_SESSION['user_id']; ?>" data-current-step="7-2-coussin-bois">
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
        <li><a href="etape5-bois-accoudoir.php">Accoudoirs</a></li>
        <li><a href="etape6-bois-dossier.php">Dossier</a></li>
        <li><a href="etape7-1-bois-tissu.php" class="active">Tissu</a></li>
        <li><a href="etape8-bois-mousse.php">Mousse</a></li>
      </ul>
    </div>

    <div class="container transition">
      <!-- Colonne de gauche -->
      <div class="left-column ">
        <div style="display: flex; align-items: center; gap: 0.5em;">
          <h2 style="margin: 0;">Étape 7.2 - Choisi ton kit de coussins</h2>
          <!-- Icône d'information avec popup -->
          <button id="info-coussin-btn" title="Information" style="background: none; border: none; cursor: pointer; padding: 0;">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
              <circle cx="10" cy="10" r="9" stroke="#997765" stroke-width="2" fill="#f5f5f5"/>
              <text x="10" y="15" text-anchor="middle" font-size="13" fill="#997765" font-family="Arial" font-weight="bold">i</text>
            </svg>
          </button>
        </div>
        <div id="info-coussin-popup" style="display:none; position:absolute; background:#fff; border:1px solid #ccc; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1); padding:1em; z-index:1000; max-width:300px;">
          <span style="font-size: 1em;">Le nombre de coussin est calculé en fonction de la longueur.</span>
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
          <?php if (!empty($motif_bois)): ?>
            <?php foreach ($motif_bois as $bois): ?>
              <div class="option ">
                <img src="../../admin/uploads/motif-bois/<?php echo htmlspecialchars($bois['img']); ?>"
                  alt="<?php echo htmlspecialchars($bois['nom']); ?>"
                  data-bois-id="<?php echo $bois['id']; ?>"
                  data-bois-prix="<?php echo $bois['prix']; ?>">
                <p><?php echo htmlspecialchars($bois['nom']); ?></p>
                <p><strong><?php echo htmlspecialchars($bois['prix']); ?> €</strong></p>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Aucune couleur disponible pour le moment.</p>
          <?php endif; ?>
        </section>

        <div class="footer">
          <p>Total : <span>0 €</span></p>
          <div class="buttons">
            <button onclick="retourEtapePrecedente()" class="btn-beige  ">Retour</button>
            <form method="POST" action="">
              <input type="hidden" name="motif_bois_id" id="selected-motif_bois">
              <button type="submit" id="btn-suivant" class="btn-noir">Suivant</button>
            </form>
          </div>
        </div>
      </div>


      <!-- Colonne de droite -->
      <div class="right-column ">
        <section class="main-display">
          <div class="buttons">
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
        <p>Contactez-nous au numéro suivant et un vendeur vous assistera :
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
        <h2>Êtes-vous sûr de vouloir abandonner ?</h2>
        <br>
        <button class="btn-beige">Oui...</button>
        <button class="btn-noir">Non !</button>
      </div>
    </div>

    <!-- Popup d'erreur si les dimensions ne sont pas remplies -->
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



    <!-- BOUTTON RETOUR -->
    <script>
      function retourEtapePrecedente() {
        window.location.href = "etape7-1-bois-tissu.php";
      }
    </script>

    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const options = document.querySelectorAll('.color-options .option img');
        const mainImage = document.querySelector('.main-display img');
        const erreurPopup = document.getElementById('erreur-popup');
        const closeErreurBtn = erreurPopup.querySelector('.btn-noir');
        const selectedCouleurBoisInput = document.getElementById('selected-motif_bois'); // Input caché
        const form = document.querySelector('form');

        let selectedMotifBoisId = localStorage.getItem('selectedMotifBois') || '';
        let selected = selectedMotifBoisId !== '';

        function saveSelection() {
          localStorage.setItem('selectedMotifBois', selectedMotifBoisId);
        }

        // Restaurer la sélection si elle existe
        options.forEach(img => {
          if (img.getAttribute('data-bois-id') === selectedMotifBoisId) {
            img.classList.add('selected');
            mainImage.src = img.src;
            selectedCouleurBoisInput.value = selectedMotifBoisId;
          }
        });

        options.forEach(img => {
          img.addEventListener('click', () => {
            options.forEach(opt => opt.classList.remove('selected'));
            img.classList.add('selected');
            mainImage.src = img.src;
            selectedMotifBoisId = img.getAttribute('data-bois-id');
            selectedCouleurBoisInput.value = selectedMotifBoisId;
            selected = true;
            saveSelection();
          });
        });

        // Empêcher la soumission du formulaire si rien n'est sélectionné
        form.addEventListener('submit', (e) => {
          if (!selectedCouleurBoisInput.value) {
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
    { id: 'etape6-bois-dossier.php', key: null },
    { id: 'etape7-1-bois-tissu.php', key: null},
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