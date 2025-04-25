<?php
require '../../admin/config.php';
session_start();


// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
  header("Location: ../formulaire/Connexion.php");
  exit;
}


// Récupérer les modèles disponibles depuis la base de données
$stmt = $pdo->query("SELECT * FROM modele");
$modele = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['modele_id']) || empty($_POST['modele_id']) || !isset($_POST['modele_type'])) {
    echo "Erreur : aucun modèle sélectionné.";
    exit;
  }


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
  header("Location: etape4-1-tissu-choix-tissu.php");
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
  <script type="module" src="../../scrpit/popup.js"></script>
  <script type="module" src="../../scrpit/variationPrix.js"></script>

  <title>Étape 3 - Choisi ton modèle</title>



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

<body data-user-id="<?php echo $_SESSION['user_id']; ?>" data-current-step="3-modele-tissu">


  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>


  <main>


    <div class="fil-ariane-container" aria-label="fil-ariane">
      <ul class="fil-ariane">
        <li><a href="etape1-1-structure.php">Structure</a></li>
        <li><a href="etape1-2-dimension.php">Dimension</a></li>
        <li><a href="etape2-type-banquette.php">Banquette</a></li>
        <li><a href="etape3-tissu-modele-banquette.php" class="active">Modèle</a></li>
        <li><a href="etape4-1-tissu-choix-tissu.php">Tissu</a></li>
        <li><a href="etape5-tissu-choix-dossier.php">Dossier</a></li>
        <li><a href="etape6-2-tissu.php">Accoudoir</a></li>
        <li><a href="etape7-tissu-choix-mousse.php">Mousse</a></li>
      </ul>
    </div>


    <div class="container">
      <div class="left-column transition">
        <h2>Étape 3 - Choisis ton modèle</h2>
        <section class="color-2options">
          <?php foreach ($modele as $item): ?>
            <div class="option transition">
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
          <button onclick="retourEtapePrecedente()" class="btn-retour transition">Retour</button>
          <form method="POST" action="">
              <input type="hidden" name="modele_id" id="selected-modele">
              <input type="hidden" name="modele_type" id="selected-modele-type">
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
          <img id="main-image" src="../../medias/process-main-image.png" alt="Modèle sélectionné" class="transition">
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


    <!-- Pop-up de sélection d'option -->
    <div id="selection-popup" class="popup transition">
      <div class="popup-content">
        <h2>Veuillez sélectionner une option avant de continuer.</h2>
        <br>
        <button class="close-btn">OK</button>
      </div>
    </div>

    <!-- GESTION DES SELECTIONS -->
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const options = document.querySelectorAll('.color-2options .option img');
        const selectedModeleInput = document.getElementById('selected-modele');
        const selectedModeleTypeInput = document.getElementById('selected-modele-type');
        const selectionPopup = document.getElementById('selection-popup');
        const mainImage = document.getElementById('main-image');
        let selected = false;

        let savedModeleId = localStorage.getItem('selectedModeleId');
        let savedModeleType = localStorage.getItem('selectedModeleType');

        if (savedModeleId && savedModeleType) {
          options.forEach(img => {
            if (img.getAttribute('data-modele-tissu-id') === savedModeleId && img.getAttribute('data-modele-tissu-type') === savedModeleType) {
              img.classList.add('selected');
              mainImage.src = img.src;
              mainImage.alt = img.alt;
              selectedModeleInput.value = savedModeleId;
              selectedModeleTypeInput.value = savedModeleType;
              selected = true;
            }
          });

          
        }

        document.querySelectorAll('.transition').forEach(element => {
          element.classList.add('show');
        });

        options.forEach(img => {
          img.addEventListener('click', () => {
            options.forEach(opt => opt.classList.remove('selected'));
            img.classList.add('selected');
            selectedModeleInput.value = img.getAttribute('data-modele-tissu-id');
            selectedModeleTypeInput.value = img.getAttribute('data-modele-tissu-type');

            // Mise à jour de l'image principale
            mainImage.src = img.src;
            mainImage.alt = img.alt;

            selected = true;
            saveSelection(img.getAttribute('data-modele-tissu-id'), img.getAttribute('data-modele-tissu-type'));
          });
        });

        document.querySelector('#selection-popup .close-btn').addEventListener('click', () => {
          selectionPopup.style.display = 'none';
        });

        function saveSelection(modeleId, modeleType) {
          localStorage.setItem('selectedModeleId', modeleId);
          localStorage.setItem('selectedModeleType', modeleType);
        }
      });
    </script>

     <!-- BOUTTON RETOUR -->
     <script>
       function retourEtapePrecedente() {
    // Exemple : tu es sur étape 8, tu veux revenir à étape 7
    window.location.href = "etape2-type-banquette.php"; 
  }
    </script>

  </main>


  <?php require '../../squelette/footer.php'; ?>
</body>

</html>