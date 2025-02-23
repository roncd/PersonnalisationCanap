<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/processus.css">
  <link rel="stylesheet" href="../../styles/popup.css">


  <title>Étape 5 - Choisi ton nombre d'accoudoirs</title>
</head>
<body>

<header>
  <?php require '../../squelette/header.php'; ?>
</header>

<main>
<div class="fil-ariane-container" aria-label="fil-ariane">
  <ul class="fil-ariane">
    <li><a href="etape1-1.php">Structure</a></li>
    <li><a href="etape2.php">Banquette</a></li>
    <li><a href="etape3-bois.php">Couleur</a></li>
    <li><a href="etape4-bois.php">Décoration</a></li>
    <li><a href="etape5-1-bois.php" class="active">Accoudoirs</a></li>
    <li><a href="etape6-bois.php">Dossier</a></li>
    <li><a href="etape7-bois.php">Mousse</a></li>
    <li><a href="etape8-1-bois.php">Tissu</a></li>
  </ul>
</div>
  <div class="container">
    <!-- Colonne de gauche -->
    <div class="left-column">
      <h2>Étape 5 - Choisi ton nombre d'accoudoirs</h2>
      
      <form class="formulaire-creation-compte">
          <div class="form-row">
            <div class="form-group">
              <label for="accoudoir">Nombre d'accoudoirs :</label>
              <input type="number" id="accoudoir"  class="input-field" require>
            </div>
          </div>
      </form>

      <div class="footer">
        <p>Total : <span>899 €</span></p>
        <div class="buttons">
          <button class="btn-retour" onclick="history.go(-1)">Retour</button>
          <button class="btn-suivant">Suivant</button>
        </div>
      </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
    // Sélection des boutons
    const suivantButton = document.querySelector('.btn-suivant');


    // Action du bouton "Suivant" : rediriger vers la page suivante
    suivantButton.addEventListener('click', () => {
      window.location.href = 'etape5-2-bois.php'; 
    });
    });
    </script>
    <!-- Colonne de droite -->
    <div class="right-column">
      <section class="main-display">
        <div class="buttons">
          <button class="btn-aide">Besoin d'aide ?</button>
          <button class="btn-abandonner">Abandonner</button>
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
    <strong>06 58 47 58 56</strong></p>
      <br>
    <button class="close-btn">Merci !</button>

  </div>
</div>
  <script>
document.addEventListener('DOMContentLoaded', () => {
  const openButton = document.querySelector('.btn-aide'); // Bouton pour ouvrir le popup
  const popup = document.getElementById('help-popup');
  const closeButton = document.querySelector('.close-btn'); // Bouton "Merci !" pour fermer le popup

  // Afficher le popup
  openButton.addEventListener('click', () => {
    console.log('Bouton Aide cliqué');
    popup.style.display = 'flex';
  });

  // Masquer le popup avec le bouton "Merci !"
  closeButton.addEventListener('click', () => {
    console.log('Bouton Merci cliqué');
    popup.style.display = 'none';
  });

  // Fermer le popup si clic à l'extérieur
  window.addEventListener('click', (event) => {
    if (event.target === popup) {
      console.log('Clic à l\'extérieur du popup');
      popup.style.display = 'none';
    }
  });
});
</script>


<!-- Popup besoin d'aide -->
<div id="abandonner-popup" class="popup">
  <div class="popup-content">
    <h2>Êtes vous sûr de vouloir abandonner ?</h2>
      <br>
    <button class="yes-btn">Oui ...</button>
    <button class="no-btn">Non !</button>


  </div>
</div>


<script>document.addEventListener('DOMContentLoaded', () => {
  const openButton = document.querySelector('.btn-abandonner'); // Bouton pour ouvrir le popup
  const popup = document.getElementById('abandonner-popup');
  const yesButton = document.querySelector('.yes-btn'); // Bouton "Oui ..." pour redirection
  const noButton = document.querySelector('.no-btn'); // Bouton "Non !" pour fermer le popup

  // Afficher le popup
  openButton.addEventListener('click', () => {
    console.log('Bouton Abandonner cliqué');
    popup.style.display = 'flex';
  });

  // Rediriger vers la page d'accueil avec le bouton "Oui ..."
  yesButton.addEventListener('click', () => {
    console.log('Redirection vers la page d\'accueil');
    window.location.href = '../pages/'; // Remplace '/' par l'URL de votre page d'accueil
  });

  // Masquer le popup avec le bouton "Non !"
  noButton.addEventListener('click', () => {
    console.log('Popup fermé via le bouton Non !');
    popup.style.display = 'none';
  });

  // Fermer le popup si clic à l'extérieur
  window.addEventListener('click', (event) => {
    if (event.target === popup) {
      console.log('Clic à l\'extérieur du popup');
      popup.style.display = 'none';
    }
  });
});
</script>
</main>
<?php require_once '../../squelette/footer.php'?>
</body>
</html>
