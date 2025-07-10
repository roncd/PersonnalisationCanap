<?php
require '../../admin/config.php';
session_start();



// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
  $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
  header("Location: ../formulaire/Connexion.php");
  exit;
}


// Récupérer les modèles disponibles depuis la base de données
$stmt = $pdo->query("SELECT * FROM modele  ORDER BY prix ASC");
$modele = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $id_client = $_SESSION['user_id'];
  $id_modele = $_POST['modele_id'];


  // Vérifier si une commande temporaire existe déjà pour cet utilisateur
  $stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ?");
  $stmt->execute([$id_client]);
  $existing_order = $stmt->fetch(PDO::FETCH_ASSOC);


  if ($existing_order) {
    $stmt = $pdo->prepare("UPDATE commande_temporaire SET id_modele = ? WHERE id_client = ?");
    $stmt->execute([$id_modele, $id_client]);
  } else {
    $stmt = $pdo->prepare("INSERT INTO commande_temporaire (id_client, id_modele) VALUES (?, ?)");
    $stmt->execute([$id_client, $id_modele]);
  }


  // Rediriger vers l'étape suivante
  header("Location: etape4-1-tissu-tissu.php");
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

  <title>Étape 3 - Choisi ton modèle de banquette</title>

</head>

<body data-user-id="<?php echo $_SESSION['user_id']; ?>" data-current-step="3-modele-tissu">
  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>


  <main>


    <div class="fil-ariane-container h2" aria-label="fil-ariane"  id="filAriane">
      <ul class="fil-ariane">
        <li><a href="etape1-1-structure.php">Structure</a></li>
        <li><a href="etape1-2-dimension.php">Dimension</a></li>
        <li><a href="etape2-type-banquette.php">Banquette</a></li>
        <li><a href="etape3-tissu-modele-banquette.php" class="active">Modèle</a></li>
        <li><a href="etape4-1-tissu-tissu.php">Tissu</a></li>
        <li><a href="etape5-tissu-dossier.php">Dossier</a></li>
        <li><a href="etape6-tissu-accoudoir.php">Accoudoir</a></li>
        <li><a href="etape7-tissu-mousse.php">Mousse</a></li>
      </ul>
    </div>


    <div class="container transition">
      <div class="left-column ">
        <h2>Étape 3 - Choisis ton modèle de banquette</h2>
        <section class="color-2options">
          <?php foreach ($modele as $item): ?>
            <div class="option ">
              <img src="../../admin/uploads/modele/<?php echo htmlspecialchars($item['img']); ?>"
                alt="<?php echo htmlspecialchars($item['nom']); ?>" data-modele-tissu-id="<?php echo $item['id']; ?>"
                data-modele-tissu-prix="<?php echo $item['prix']; ?>">
              <p><?php echo htmlspecialchars($item['nom']); ?></p>
              <p><strong><?php echo htmlspecialchars($item['prix']); ?> €</strong></p>

            </div>
          <?php endforeach; ?>
        </section>
        <div class="footer">
          <p>Total : <span>0 €</span></p>
          <div class="buttons">
            <button onclick="retourEtapePrecedente()" class="btn-beige  ">Retour</button>
            <form method="POST" action="">
              <input type="hidden" name="modele_id" id="selected-modele_tissu">
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
          <img id="main-image" src="../../medias/process-main-image.png" alt="Modèle sélectionné">
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

        <!-- Popup bloquant pour les étapes non validées -->
<div id="filariane-popup" class="popup">
  <div class="popup-content">
    <h2>Veuillez cliquez sur "suivant" pour passer à l’étape d’après.</h2>
    <button class="btn-noir">OK</button>
  </div>
</div>


    <!-- GESTION DES SELECTIONS -->
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const options = document.querySelectorAll('.color-2options .option img');
        const selectedModeleTissuInput = document.getElementById('selected-modele_tissu');
        const mainImage = document.getElementById('main-image');
        const erreurPopup = document.getElementById('erreur-popup');
        const closeErreurBtn = erreurPopup.querySelector('.btn-noir');
        const form = document.querySelector('form');

        let savedModeleTissuId = localStorage.getItem('selectedModeleTissuId');
        let selected = savedModeleTissuId !== '';

        function saveSelection() {
          localStorage.setItem('selectedModeleTissuId', savedModeleTissuId);
        }

        // Restaurer la sélection si elle existe
        options.forEach(img => {
          if (img.getAttribute('data-modele-tissu-id') === savedModeleTissuId) {
            img.classList.add('selected');
            mainImage.src = img.src;
            selectedModeleTissuInput.value = savedModeleTissuId;
          }
        });

        // Gestion du clic sur une option
        options.forEach(img => {
          img.addEventListener('click', () => {
            options.forEach(opt => opt.classList.remove('selected'));
            img.classList.add('selected');
            mainImage.src = img.src;
            savedModeleTissuId = img.getAttribute('data-modele-tissu-id');
            selectedModeleTissuInput.value = savedModeleTissuId;
            selected = true;
            saveSelection();
          });
        });

        // Empêcher la soumission du formulaire si rien n'est sélectionné
        form.addEventListener('submit', (e) => {
          if (!selectedModeleTissuInput.value) {
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

    <!-- BOUTTON RETOUR -->
    <script>
      function retourEtapePrecedente() {
        window.location.href = "etape2-type-banquette.php";
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
  { id: 'etape4-1-tissu-tissu.php', key: 'etape4_valide' },
  { id: 'etape5-tissu-dossier.php', key: 'etape5_valide' },
  { id: 'etape6-tissu-accoudoir.php', key: 'etape6_valide' },
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


  <?php require '../../squelette/footer.php'; ?>
</body>

</html>