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
$selectedCouleurId = $_SESSION['id_couleur_bois'];

//Affiche seulement les deco lié à la couleur séléctionné 
$stmt = $pdo->prepare("SELECT * FROM decoration WHERE id_couleur_bois = ?");
$stmt->execute([$selectedCouleurId]);
$decoration = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $id_client = $_SESSION['user_id'];
  $id_decoration = $_POST['decoration_id'];


  // Vérifier si une commande temporaire existe déjà pour cet utilisateur
  $stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ?");
  $stmt->execute([$id_client]);
  $existing_order = $stmt->fetch(PDO::FETCH_ASSOC);


  if ($existing_order) {
    $stmt = $pdo->prepare("UPDATE commande_temporaire SET id_decoration = ? WHERE id_client = ?");
    $stmt->execute([$id_decoration, $id_client]);
  } else {
    $stmt = $pdo->prepare("INSERT INTO commande_temporaire (id_client, id_decoration) VALUES (?, ?)");
    $stmt->execute([$id_client, $id_decoration]);
  }


  // Rediriger vers l'étape suivante
  header("Location: etape5-bois-accoudoir.php");
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

  <title>Étape 4 - Décore ta banquette</title>

</head>

<body data-user-id="<?php echo $_SESSION['user_id']; ?>" data-current-step="4-deco-bois">
  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <main>
    <div class="fil-ariane-container h2" aria-label="fil-ariane" id="filAriane" >
      <ul class="fil-ariane">
        <li><a href="etape1-1-structure.php">Structure</a></li>
        <li><a href="etape1-2-dimension.php">Dimension</a></li>
        <li><a href="etape2-type-banquette.php">Banquette</a></li>
        <li><a href="etape3-bois-couleur.php">Couleur</a></li>
        <li><a href="etape4-bois-decoration.php" class="active">Décoration</a></li>
        <li><a href="etape5-bois-accoudoir.php">Accoudoirs</a></li>
        <li><a href="etape6-bois-dossier.php">Dossier</a></li>
        <li><a href="etape7-1-bois-tissu.php">Tissu</a></li>
        <li><a href="etape8-bois-mousse.php">Mousse</a></li>
      </ul>
    </div>

    <div class="container transition">
      <!-- Colonne de gauche -->
      <div class="left-column ">
        <h2>Étape 4 - Décore ta banquette</h2>

        <section class="color-options">
          <?php if (!empty($decoration)): ?>
            <?php foreach ($decoration as $deco): ?>
              <div class="option ">
                <img src="../../admin/uploads/decoration/<?php echo htmlspecialchars($deco['img']); ?>"
                  alt="<?php echo htmlspecialchars($deco['nom']); ?>" data-deco-bois-id="<?php echo $deco['id']; ?>"
                  data-deco-bois-prix="<?php echo $deco['prix']; ?>">
                <p><?php echo htmlspecialchars($deco['nom']); ?></p>
                <p><strong><?php echo htmlspecialchars($deco['prix']); ?> €</strong></p>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Aucune décoration avec la couleur sélécdtionné disponible pour le moment.</p>
          <?php endif; ?>
        </section>

        <div class="footer">
          <p>Total : <span>0 €</span></p>
          <div class="buttons">
            <button onclick="retourEtapePrecedente()" class="btn-beige  ">Retour</button>
            <form method="POST" action="">
              <input type="hidden" name="decoration_id" id="selected-decoration">
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
          <img src="../../medias/process-main-image.png" alt="Armoire" class="">
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
    <h2>Veuillez sélectionner une option et cliquez sur "suivant" pour passer à l’étape d’après.</h2>
    <button class="btn-noir">OK</button>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const options               = document.querySelectorAll('.color-options .option img');
  const mainImage             = document.querySelector('.main-display img');
  const erreurPopup           = document.getElementById('erreur-popup');
  const closeErreurBtn        = erreurPopup.querySelector('.btn-noir');
  const selectedDecorationInput = document.getElementById('selected-decoration');
  const form                  = document.querySelector('form');

  // ───────────────────────────────────────────────
  // 1. Récupération et détection du contexte
  // ───────────────────────────────────────────────
  let selectedDecoId = localStorage.getItem('selectedDecoration') || '';
  let selected       = selectedDecoId !== '';

  const currentStep  = document.body.getAttribute('data-current-step') || '';
  const isTissu      = currentStep.includes('tissu');
  const isBois       = currentStep.includes('bois');

  // ───────────────────────────────────────────────
  // 2. Vérifier si la sélection stockée est compatible
  // ───────────────────────────────────────────────
  const decoSelector = isBois
    ? `[data-deco-bois-id="${selectedDecoId}"]`
    : `[data-deco-tissu-id="${selectedDecoId}"]`;

  const idCompatible = selectedDecoId && document.querySelector(decoSelector);

  if (selectedDecoId && !idCompatible) {
    // L’ID stocké ne correspond pas au chemin actuel → on nettoie
    options.forEach(img => img.classList.remove('selected'));
    localStorage.removeItem('selectedDecoration');
    selectedDecoId = '';
    selected = false;
    selectedDecorationInput.value = '';
    console.log('🧹 Changement de chemin : déco réinitialisée');
  }

  // ───────────────────────────────────────────────
  // 3. Restaurer la sélection si cohérente
  // ───────────────────────────────────────────────
  options.forEach(img => {
    if (
      (isBois  && img.getAttribute('data-deco-bois-id')  === selectedDecoId) ||
      (isTissu && img.getAttribute('data-deco-tissu-id') === selectedDecoId)
    ) {
      img.classList.add('selected');
      mainImage.src = img.src;
      selectedDecorationInput.value = selectedDecoId;
    }
  });

  // ───────────────────────────────────────────────
  // 4. Sauvegarde
  // ───────────────────────────────────────────────
  function saveSelection() {
    localStorage.setItem('selectedDecoration', selectedDecoId);
  }

  // ───────────────────────────────────────────────
  // 5. Gestion du clic sur une décoration
  // ───────────────────────────────────────────────
  options.forEach(img => {
    img.addEventListener('click', () => {
      // Retirer la sélection précédente
      options.forEach(opt => opt.classList.remove('selected'));

      // Appliquer la nouvelle sélection
      img.classList.add('selected');
      mainImage.src = img.src;

      // Stocker l’ID selon le chemin courant
      selectedDecoId = isBois
        ? img.getAttribute('data-deco-bois-id')
        : img.getAttribute('data-deco-tissu-id');

      selectedDecorationInput.value = selectedDecoId;
      selected = true;
      saveSelection();

      console.log(`🎨 Décoration sélectionnée : ${selectedDecoId}`);
    });
  });

  // ───────────────────────────────────────────────
  // 6. Empêcher l’envoi si aucune déco n’est choisie
  // ───────────────────────────────────────────────
  form.addEventListener('submit', e => {
    if (!selectedDecorationInput.value) {
      e.preventDefault();
      erreurPopup.style.display = 'flex';
    }
  });

  // ───────────────────────────────────────────────
  // 7. Popup erreur (fermeture)
  // ───────────────────────────────────────────────
  closeErreurBtn.addEventListener('click', () => {
    erreurPopup.style.display = 'none';
  });

  window.addEventListener('click', event => {
    if (event.target === erreurPopup) erreurPopup.style.display = 'none';
  });
});
</script>

    <!-- BOUTTON RETOUR -->
    <script>
      function retourEtapePrecedente() {
        window.location.href = "etape3-bois-couleur.php";
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
    { id: 'etape5-bois-accoudoir.php', key: 'etape5_valide' },
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

  <?php require_once '../../squelette/footer.php'; ?>

</body>

</html>