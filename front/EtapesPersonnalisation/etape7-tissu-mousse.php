<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
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
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/processus.css">
  <link rel="stylesheet" href="../../styles/popup.css">
  <link rel="stylesheet" href="../../styles/buttons.css">
  <script type="module" src="../../script/popup.js"></script>
  <script type="module" src="../../script/variationPrix.js"></script>

  <title>Étape 7 - Choisi ta mousse</title>
  
</head>

<body data-user-id="<?php echo $_SESSION['user_id']; ?>" data-current-step="7-mousse-tissu">

  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>


  <main>


    <div class="fil-ariane-container h2" aria-label="fil-ariane">
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
            <?php foreach ($mousse as $mousse_bois): ?>
              <div class="option ">
                <img src="../../admin/uploads/mousse/<?php echo htmlspecialchars($mousse_bois['img']); ?>"
                  alt="<?php echo htmlspecialchars($mousse_bois['nom']); ?>"
                  data-mousse-bois-id="<?php echo $mousse_bois['id']; ?>"
                  data-mousse-bois-prix="<?php echo $mousse_bois['prix']; ?>">
                <p><?php echo htmlspecialchars($mousse_bois['nom']); ?></p>
                <p><strong><?php echo htmlspecialchars($mousse_bois['prix']); ?> €</strong></p>
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
              <button type="submit" id="btn-suivant" class="btn-noir">Suivant</button>
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


    <!-- GESTION DES SELECTIONS -->
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


        // Restaurer la sélection si elle existe
        options.forEach(img => {
          if (img.getAttribute('data-mousse-bois-id') === selectedMousseId) {
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
            selectedMousseId = img.getAttribute('data-mousse-bois-id');
            selectedMousseInput.value = selectedMousseId;
            selected = true;
            saveSelection();
          });
        });

        // Empêcher la soumission du formulaire si rien n'est sélectionné
        form.addEventListener('submit', (e) => {
          if (!selectedMousseInput.value) {
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

        function saveSelection() {
          localStorage.setItem('selectedMousse', selectedMousseId);
        }
      });
    </script>
    <!-- BOUTTON RETOUR -->
    <script>
      function retourEtapePrecedente() {
        window.location.href = "etape6-tissu-accoudoir.php";
      }
    </script>

  </main>
  <?php require_once '../../squelette/footer.php' ?>
</body>

</html>