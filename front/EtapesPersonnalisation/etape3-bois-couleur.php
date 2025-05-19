<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
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
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/processus.css">
  <link rel="stylesheet" href="../../styles/popup.css">
  <script type="module" src="../../script/popup.js"></script>
  <script type="module" src="../../script/variationPrix.js"></script>
  <script src="../../script/abandonner.js"></script>


  <title>Étape 3 - Choisi ta couleur</title>
  <style>
    /* Transition pour les éléments de la page */
    .transition {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.5s ease, transform 0.5s ease;
    }


    .transition.show {
      opacity: 1;
      transform: translateY(0);
    }


    /* Appliquer les transitions aux images sélectionnées */
    .option img.selected {
      border: 3px solid #997765;
      /* Couleur marron */
      border-radius: 5px;
      box-sizing: border-box;
    }
  </style>
</head>

<body data-user-id="<?php echo $_SESSION['user_id']; ?>" data-current-step="3-couleur-bois">


  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>


  <main>
    <div class="fil-ariane-container" aria-label="fil-ariane">
      <ul class="fil-ariane">
        <li><a href="etape1-1-structure.php">Structure</a></li>
        <li><a href="etape1-2-dimension.php">Dimension</a></li>
        <li><a href="etape2-type-banquette.php">Banquette</a></li>
        <li><a href="etape3-bois-couleur.php" class="active">Couleur</a></li>
        <li><a href="etape4-bois-decoration.php">Décoration</a></li>
        <li><a href="etape5-bois-accoudoir.php">Accoudoirs</a></li>
        <li><a href="etape6-bois-dossier.php">Dossier</a></li>
        <li><a href="etape7-bois-mousse.php">Mousse</a></li>
        <li><a href="etape8-1-bois-tissu.php">Tissu</a></li>
      </ul>
    </div>


    <div class="container">
      <!-- Colonne de gauche -->
      <div class="left-column transition">
        <h2>Étape 3 - Choisi ta couleur</h2>

        <section class="color-options">
          <?php if (!empty($couleur_bois)): ?>
            <?php foreach ($couleur_bois as $bois): ?>
              <div class="option transition">
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
          <p>Total : <span>899 €</span></p>
          <div class="buttons">
          <button onclick="retourEtapePrecedente()" class="btn-retour transition">Retour</button>
          <form method="POST" action="">
              <input type="hidden" name="couleur_bois_id" id="selected-couleur_bois">
              <button type="submit" class="btn-suivant transition">Suivant</button>
            </form>
          </div>
        </div>
      </div>

      
      <!-- Colonne de droite -->
      <div class="right-column transition">
        <section class="main-display">
          <div class="buttons transition">
            <button class="btn-aide">Besoin d'aide ?</button>
            <button class="btn-abandonner">Abandonner</button>
          </div>
          <img src="../../medias/process-main-image.png" alt="Armoire" class="transition">
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

<!-- Popup d'erreur si les dimensions ne sont pas remplies -->
<div id="erreur-popup" class="popup transition">
      <div class="popup-content">
        <h2>Veuillez choisir une option avant de continuer.</h2>
        <button class="close-btn">OK</button>
      </div>
    </div>

    <!-- GESTION DES SELECTIONS -->
    <script>
  document.addEventListener('DOMContentLoaded', () => {
    const options = document.querySelectorAll('.color-options .option img');
    const mainImage = document.querySelector('.main-display img');
    const erreurPopup = document.getElementById('erreur-popup');
    const closeErreurBtn = erreurPopup.querySelector('.close-btn');
    const selectedCouleurBoisInput = document.getElementById('selected-couleur_bois');
    const form = document.querySelector('form'); // Assure-toi que ton <form> a bien une balise identifiable

    let selectedBoisId = localStorage.getItem('selectedCouleurBois') || '';
    let selected = selectedBoisId !== '';

    // Animation de transition (facultatif)
    document.querySelectorAll('.transition').forEach(element => {
      element.classList.add('show');
    });

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
      localStorage.setItem('selectedCouleurBois', selectedBoisId);
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

  <?php require_once '../../squelette/footer.php'; ?>

</body>

</html>