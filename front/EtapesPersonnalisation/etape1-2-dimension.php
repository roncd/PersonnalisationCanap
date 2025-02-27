<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../formulaire/Connexion.php");
    exit;
}

$id_client = $_SESSION['user_id'];

// Vérifier si une commande temporaire existe déjà pour cet utilisateur
$stmt = $pdo->prepare("SELECT * FROM commande_temporaire WHERE id_client = ?");
$stmt->execute([$id_client]);
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Vérifier si longueurA est bien renseignée (obligatoire)
  if (!empty($_POST["longueurA"])) {
      $longueurA = (int) trim($_POST["longueurA"]);
      $longueurB = !empty($_POST["longueurB"]) ? (int) trim($_POST["longueurB"]) : null;
      $longueurC = !empty($_POST["longueurC"]) ? (int) trim($_POST["longueurC"]) : null;

      if ($commande) {
          $id = $commande['id']; // Récupérer l'ID de la commande temporaire

          // Correction de la requête SQL
          $stmt = $pdo->prepare("UPDATE commande_temporaire SET longueurA = ?, longueurB = ?, longueurC = ? WHERE id = ?");
          if ($stmt->execute([$longueurA, $longueurB, $longueurC, $id])) {
            // Redirection après mise à jour réussie
            header("Location: etape2-type-banquette.php");
            exit();
          }
      } 
  } 
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
  <title>Étape 1 - Choisi tes mesures</title>
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
      border: 3px solid #997765; /* Couleur marron */
      border-radius: 5px; /* Coins légèrement arrondis */
      box-sizing: border-box; /* Inclure le padding dans les dimensions */
    }
  </style>
</head>
<body>

<header>
  <?php require '../../squelette/header.php'; ?>
</header>

<main>
<div class="fil-ariane-container" aria-label="fil-ariane">
  <ul class="fil-ariane">
    <li><a href="etape1-1-structure.php" >Structure</a></li>
    <li><a href="etape1-2-dimension.php" class="active">Dimension</a></li>
    <li><a href="etape2-type-banquette.php">Banquette</a></li>
  </ul>
</div>
  <div class="container">
    <!-- Colonne de gauche -->
    <div class="left-column transition">
      <h2>Étape 1 - Choisi tes mesures</h2>
      <form  method="POST" class="formulaire">
        <p>Largeur banquette : <span class="bold">50cm (par défaut)</span></p>
            <div class="form-row">
              <div class="form-group">
                <label for="longueurA">Longueur banquette A (en cm) :</label>
                <input type="number" id="longueurA" name="longueurA" class="input-field" placeholder="Ex: 150">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="longueurB">Longueur banquette B (en cm) :</label>
                <input type="number" id="longueurB" name="longueurB" class="input-field" placeholder="Ex: 350">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="longueurC">Longueur banquette C (en cm) :</label>
                <input type="number" id="longueurC" name="longueurC" class="input-field" placeholder="Ex: 350">
              </div>
            </div>
            <div class="footer">
            <p>Total : <span>899 €</span></p>
            <div class="buttons">
            <button type="button" class="btn-retour" onclick="history.go(-1)">Retour</button>
            <button type="submit" class="btn-suivant transition">Suivant</button>
            </div>
            </div>
      </form>
    </div>



    <script>
  document.addEventListener('DOMContentLoaded', () => {
    // Afficher les éléments avec la classe "transition"
    document.querySelectorAll('.transition').forEach(element => {
      element.classList.add('show');
    });

   
  });
</script>

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
    <strong>06 58 47 58 56</strong></p>
      <br>
    <button class="close-btn">Merci !</button>

  </div>
</div><!-- Popup d'erreur si les dimensions ne sont pas remplies -->
<div id="erreur-popup" class="popup transition">
  <div class="popup-content">
    <h2>Veuillez choisir une option avant de continuer.</h2>
    <button class="close-btn">OK</button>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Sélection des éléments
  const form = document.querySelector('.formulaire');
  const erreurPopup = document.getElementById('erreur-popup');
  const closeErreurBtn = erreurPopup.querySelector('.close-btn');
  const longueurAInput = document.getElementById('longueurA');

  form.addEventListener('submit', (event) => {
    const longueurA = longueurAInput.value.trim();

    if (!longueurA) {
      event.preventDefault(); // Empêche le formulaire d'être soumis
      erreurPopup.style.display = 'flex'; // Afficher le popup d'erreur
    }
  });

  // Fermer le popup d'erreur lorsque le bouton "OK" est cliqué
  closeErreurBtn.addEventListener('click', () => {
    erreurPopup.style.display = 'none';
  });

  // Fermer le popup d'erreur si l'utilisateur clique à l'extérieur du pop-up
  window.addEventListener('click', (event) => {
    if (event.target === erreurPopup) {
      erreurPopup.style.display = 'none';
    }
  });
});
</script>


<!-- Popup besoin d'aide -->
<div id="help-popup" class="popup transition">
  <div class="popup-content">
    <h2>Vous avez une question ?</h2>
    <p>Contactez-nous au numéro suivant et un vendeur vous assistera :</p>
    <strong>06 58 47 58 56</strong>
    <br><br>
    <button class="close-btn">Merci !</button>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const openHelpButton = document.querySelector('.btn-aide');
  const helpPopup = document.getElementById('help-popup');
  const closeHelpButton = document.querySelector('.close-btn');

  openHelpButton.addEventListener('click', () => {
    helpPopup.style.display = 'flex';
  });

  closeHelpButton.addEventListener('click', () => {
    helpPopup.style.display = 'none';
  });

  window.addEventListener('click', (event) => {
    if (event.target === helpPopup) {
      helpPopup.style.display = 'none';
    }
  });
});
</script>

<!-- Popup abandon -->
<div id="abandonner-popup" class="popup transition">
  <div class="popup-content">
    <h2>Êtes-vous sûr de vouloir abandonner ?</h2>
    <br>
    <button class="yes-btn">Oui ...</button>
    <button class="no-btn">Non !</button>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const openAbandonnerButton = document.querySelector('.btn-abandonner');
  const abandonnerPopup = document.getElementById('abandonner-popup');
  const yesButton = document.querySelector('.yes-btn');
  const noButton = document.querySelector('.no-btn');

  openAbandonnerButton.addEventListener('click', () => {
    abandonnerPopup.style.display = 'flex';
  });

  yesButton.addEventListener('click', () => {
    document.body.classList.remove('show');
    setTimeout(() => {
      window.location.href = '../pages/';
    }, 500);
  });

  noButton.addEventListener('click', () => {
    abandonnerPopup.style.display = 'none';
  });

  window.addEventListener('click', (event) => {
    if (event.target === abandonnerPopup) {
      abandonnerPopup.style.display = 'none';
    }
  });
});
</script>


</main>
<?php require_once '../../squelette/footer.php'?>
</body>
</html>
