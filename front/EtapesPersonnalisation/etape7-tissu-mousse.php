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

// Récupérer les types de mousse depuis la base de données
$stmt = $pdo->query("SELECT * FROM mousse");
$mousse = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $id_client = $_SESSION['user_id'];
  $id_mousse = $_POST['mousse_id']; // ou 'id_mousse' si ton champ s'appelle comme ça dans le HTML
  $prix_total = isset($_POST['total_price']) ? floatval($_POST['total_price']) : 0;

  // Vérifier si une commande temporaire existe déjà pour cet utilisateur
  $stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ?");
  $stmt->execute([$id_client]);
  $existing_order = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($existing_order) {
    $stmt = $pdo->prepare("UPDATE commande_temporaire SET id_mousse = ?, prix = ? WHERE id_client = ?");
    $stmt->execute([$id_mousse, $prix_total, $id_client]);
  } else {
    $stmt = $pdo->prepare("INSERT INTO commande_temporaire (id_client, id_mousse, prix) VALUES (?, ?, ?)");
    $stmt->execute([$id_client, $id_mousse, $prix_total]);
  }

  // Rediriger vers l'étape suivante
  header("Location: recapitulatif-commande-tissu.php");
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
  <script type="module" src="../../script/variationPrix.js"></script>
  <script type="module" src="../../script/keydown.js"></script>

  <title>Étape 7 - Choisi ta mousse</title>

</head>

<body data-user-id="<?php echo $_SESSION['user_id']; ?>" data-current-step="7-mousse-tissu">
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
        <li><a href="etape6-tissu-accoudoir.php">Accoudoir</a></li>
        <li><a href="etape7-tissu-mousse.php" class="active">Mousse</a></li>
      </ul>
    </div>

    <div class="container transition">
      <!-- Colonne de gauche -->
      <div class="left-column ">

        <h2>Étape 7 - Choisi ta mousse</h2>

        <section class="color-options">
          <?php if (!empty($mousse)): ?>
            <?php foreach ($mousse as $mousse_tissu): ?>
              <div class="option ">
                <img src="../../admin/uploads/mousse/<?php echo htmlspecialchars($mousse_tissu['img']); ?>"
                  alt="<?php echo htmlspecialchars($mousse_tissu['nom']); ?>"
                  data-mousse-tissu-id="<?php echo $mousse_tissu['id']; ?>"
                  data-mousse-tissu-prix="<?php echo $mousse_tissu['prix']; ?>">
                <p><?php echo htmlspecialchars($mousse_tissu['nom']); ?></p>
                <p><strong><?php echo htmlspecialchars($mousse_tissu['prix']); ?> €</strong></p>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Aucune mousse disponible pour le moment.</p>
          <?php endif; ?>
        </section>

        <div class="footer">
          <p>Total : <span>0 €</span></p>
          <div class="buttons">
            <button onclick="retourEtapePrecedente()" class="btn-beige  ">Retour</button>
            <form method="POST" action="">
              <input type="hidden" name="mousse_id" id="selected-mousse">
              <input type="hidden" name="total_price" id="total-price"> <!-- Ajout pour envoyer le prix -->
              <button type="submit" id="btn-suivant" class="btn-noir">Terminer</button>
            </form>
          </div>
        </div>
      </div>


      <script>
        document.addEventListener('DOMContentLoaded', () => {
          const totalElement = document.querySelector(".footer p span");
          const totalPriceInput = document.querySelector("#total-price");

          function updateTotalPriceInput() {
            if (totalElement && totalPriceInput) {
              totalPriceInput.value = parseFloat(totalElement.textContent) || 0;
            }
          }

          // Mettre à jour la valeur avant l'envoi
          const suivantButton = document.getElementById("btn-suivant");
          if (suivantButton) {
            suivantButton.addEventListener("click", updateTotalPriceInput);
          }
        });
      </script>


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
  const options = document.querySelectorAll('.color-options .option img');
  const mainImage = document.querySelector('.main-display img');
  const erreurPopup = document.getElementById('erreur-popup');
  const closeErreurBtn = erreurPopup.querySelector('.btn-noir');
  const selectedMousseInput = document.getElementById('selected-mousse');
  const form = document.querySelector('form');

  let selectedMousseId = localStorage.getItem('selectedMousse') || '';
  let selected = selectedMousseId !== '';

  const currentStep = document.body.getAttribute('data-current-step') || '';
  const isTissu = currentStep.includes('tissu');
  const isBois = currentStep.includes('bois');

  // Si chemin bois, enlever sélection mousse tissu, et inversement
  if (isBois && selectedMousseId) {
    options.forEach(img => {
      if (img.getAttribute('data-mousse-tissu-id') === selectedMousseId) {
        img.classList.remove('selected');
      }
    });
    localStorage.removeItem('selectedMousse');
    selectedMousseId = '';
    selected = false;
    selectedMousseInput.value = '';
    console.log('🧹 Suppression sélection mousse TISSU car chemin BOIS');
  }
  if (isTissu && selectedMousseId) {
    options.forEach(img => {
      if (img.getAttribute('data-mousse-bois-id') === selectedMousseId) {
        img.classList.remove('selected');
      }
    });
    localStorage.removeItem('selectedMousse');
    selectedMousseId = '';
    selected = false;
    selectedMousseInput.value = '';
    console.log('🧹 Suppression sélection mousse BOIS car chemin TISSU');
  }

  function saveSelection() {
    localStorage.setItem('selectedMousse', selectedMousseId);
  }

  // Restaurer la sélection selon le chemin
  options.forEach(img => {
    if (
      (isTissu && img.getAttribute('data-mousse-tissu-id') === selectedMousseId) ||
      (isBois && img.getAttribute('data-mousse-bois-id') === selectedMousseId)
    ) {
      img.classList.add('selected');
      mainImage.src = img.src;
      selectedMousseInput.value = selectedMousseId;
    }
  });

  options.forEach(img => {
    img.addEventListener('click', () => {
      options.forEach(opt => opt.classList.remove('selected'));
      img.classList.add('selected');
      mainImage.src = img.src;

      selectedMousseId = isTissu
        ? img.getAttribute('data-mousse-tissu-id')
        : img.getAttribute('data-mousse-bois-id');

      selectedMousseInput.value = selectedMousseId;
      selected = true;
      saveSelection();
      console.log(`🎨 Mousse sélectionnée : ${selectedMousseId}`);
    });
  });

  form.addEventListener('submit', (e) => {
    if (!selectedMousseInput.value) {
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

    <!-- BOUTTON RETOUR -->
    <script>
      function retourEtapePrecedente() {
        window.location.href = "etape6-tissu-accoudoir.php";
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
  { id: 'etape7-tissu-mousse.php', key: null },
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