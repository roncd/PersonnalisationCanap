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


// Récupérer les types de bois depuis la base de données
$stmt = $pdo->query("SELECT * FROM couleur_bois");
$couleur_bois = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // A ce stade, on suppose que JavaScript a empêché les soumissions sans sélection

  $id_client = $_SESSION['user_id'];
  $id_couleur_bois = $_POST['couleur_bois_id'];

  // Vérifier si une commande temporaire existe déjà pour cet utilisateur
  $stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ?");
  $stmt->execute([$id_client]);
  $existing_order = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($existing_order) {
    $stmt = $pdo->prepare("UPDATE commande_temporaire SET id_couleur_bois = ? WHERE id_client = ?");
    $stmt->execute([$id_couleur_bois, $id_client]);
  } else {
    $stmt = $pdo->prepare("INSERT INTO commande_temporaire (id_client, id_couleur_bois) VALUES (?, ?)");
    $stmt->execute([$id_client, $id_couleur_bois]);
  }

  $_SESSION['id_couleur_bois'] = $_POST['couleur_bois_id'];
  // Rediriger vers l'étape suivante
  header("Location: etape4-bois-decoration.php");
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

  <title>Étape 3 - Choisi ta couleur de bois</title>

</head>

<body data-user-id="<?php echo $_SESSION['user_id']; ?>" data-current-step="3-couleur-bois">

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
        <li><a href="etape3-bois-couleur.php" class="active">Couleur</a></li>
        <li><a href="etape4-bois-decoration.php">Décoration</a></li>
        <li><a href="etape5-bois-accoudoir.php">Accoudoirs</a></li>
        <li><a href="etape6-bois-dossier.php">Dossier</a></li>
        <li><a href="etape7-1-bois-tissu.php">Tissu</a></li>
        <li><a href="etape8-bois-mousse.php">Mousse</a></li>
      </ul>
    </div>


    <div class="container transition">
      <!-- Colonne de gauche -->
      <div class="left-column ">
        <h2>Étape 3 - Choisi ta couleur de bois</h2>

        <section class="color-options">
          <?php if (!empty($couleur_bois)): ?>
            <?php foreach ($couleur_bois as $bois): ?>
              <div class="option ">
                <img src="../../admin/uploads/couleur-banquette-bois/<?php echo htmlspecialchars($bois['img']); ?>"
                  alt="<?php echo htmlspecialchars($bois['nom']); ?>" data-bois-id="<?php echo $bois['id']; ?>"
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
              <input type="hidden" name="couleur_bois_id" id="selected-couleur_bois">
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


    <!-- GESTION DES SELECTIONS -->
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const options = document.querySelectorAll('.color-options .option img');
        const mainImage = document.querySelector('.main-display img');
        const erreurPopup = document.getElementById('erreur-popup');
        const closeErreurBtn = erreurPopup.querySelector('.btn-noir');
        const selectedCouleurBoisInput = document.getElementById('selected-couleur_bois');
        const form = document.querySelector('form');

        let selectedBoisId = localStorage.getItem('selectedCouleurBois') || '';
        let selected = selectedBoisId !== '';

        function saveSelection() {
          localStorage.setItem('selectedCouleurBois', selectedBoisId);
        }

        // Restaurer la sélection si elle existe
        options.forEach(img => {
          if (img.getAttribute('data-bois-id') === selectedBoisId) {
            img.classList.add('selected');
            mainImage.src = img.src;
            selectedCouleurBoisInput.value = selectedBoisId;
          }
        });

        // Gestion du clic sur une option
        options.forEach(img => {
          img.addEventListener('click', () => {
            options.forEach(opt => opt.classList.remove('selected'));
            img.classList.add('selected');
            mainImage.src = img.src;
            selectedBoisId = img.getAttribute('data-bois-id');
            selectedCouleurBoisInput.value = selectedBoisId;
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

        const etapes = [{
            id: 'etape1-1-structure.php',
            key: null
          }, // toujours accessible
          {
            id: 'etape1-2-dimension.php',
            key: null
          },
          {
            id: 'etape2-type-banquette.php',
            key: null
          },
          {
            id: 'etape3-bois-couleur.php',
            key: 'etape3_valide'
          },
          {
            id: 'etape4-bois-decoration.php',
            key: 'etape4_valide'
          },
          {
            id: 'etape5-bois-accoudoir.php',
            key: 'etape5_valide'
          },
          {
            id: 'etape6-bois-dossier.php',
            key: 'etape6_valide'
          },
          {
            id: 'etape7-1-bois-tissu.php',
            key: 'etape7_valide'
          },
          {
            id: 'etape8-bois-mousse.php',
            key: 'etape8_valide'
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