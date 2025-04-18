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
  if (!isset($_POST['mousse_id']) || empty($_POST['mousse_id'])) {
    echo "Erreur : Aucun type de mousse sélectionné.";
    exit;
  }

  $id_client = $_SESSION['user_id'];
  $id_mousse = $_POST['mousse_id'];

  // Vérifier si une commande temporaire existe déjà pour cet utilisateur
  $stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ?");
  $stmt->execute([$id_client]);
  $existing_order = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($existing_order) {
    $stmt = $pdo->prepare("UPDATE commande_temporaire SET id_mousse = ? WHERE id_client = ?");
    $stmt->execute([$id_mousse, $id_client]);
  } else {
    $stmt = $pdo->prepare("INSERT INTO commande_temporaire (id_client, id_mousse) VALUES (?, ?)");
    $stmt->execute([$id_client, $id_mousse]);
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
  <title>Étape 7 - Choisi ta mousse</title>
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

<body data-user-id="<?php echo $_SESSION['user_id']; ?>">

  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <main>
    <div class="fil-ariane-container" aria-label="fil-ariane">
      <ul class="fil-ariane">
        <li><a href="etape1-1-structure.php">Structure</a></li>
        <li><a href="etape1-2-dimension.php">Dimension</a></li>
        <li><a href="etape2-type-banquette.php">Banquette</a></li>
        <li><a href="etape3-tissu-modele-banquette.php">Modèle</a></li>
        <li><a href="etape4-1-tissu-choix-tissu.php">Tissu</a></li>
        <li><a href="etape5-tissu-choix-dossier.php">Dossier</a></li>
        <li><a href="etape6-2-tissu.php">Accoudoir</a></li>
        <li><a href="etape7-tissu-choix-mousse.php" class="active">Mousse</a></li>
      </ul>
    </div>

    <div class="container">
      <!-- Colonne de gauche -->
      <div class="left-column transition">

        <h2>Étape 7 - Choisi ta mousse</h2>

        <section class="color-options">
          <?php if (!empty($mousse)): ?>
            <?php foreach ($mousse as $tissu): ?>
              <div class="option transition">
                <img src="../../admin/uploads/mousse/<?php echo htmlspecialchars($tissu['img']); ?>"
                  alt="<?php echo htmlspecialchars($tissu['nom']); ?>"
                  data-mousse-id="<?php echo $tissu['id']; ?>"
                  data-mousse-prix="<?php echo $tissu['prix']; ?>">
                <p><?php echo htmlspecialchars($tissu['nom']); ?></p>
                <p><strong><?php echo htmlspecialchars($tissu['prix']); ?> €</strong></p>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Aucune mousse disponible pour le moment.</p>
          <?php endif; ?>
        </section>

        <div class="footer">
          <p>Total : <span>899 €</span></p>
          <div class="buttons">
            <button class="btn-retour transition" onclick="history.go(-1)">Retour</button>
            <form method="POST" action="">
              <input type="hidden" name="mousse_id" id="selected-mousse">
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

    <div id="selection-popup" class="popup transition">
      <div class="popup-content">
        <h2>Veuillez choisir une option avant de continuer.</h2>
        <br>
        <button class="close-btn">OK</button>
      </div>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        let totalPrice = 0; // Total global

        // Identifier l'étape actuelle
        const currentStep = "7-mousse-tissu";
        const userId = document.body.getAttribute('data-user-id');

        if (!userId) {
          console.error("ID utilisateur non trouvé.");
          return;
        }

        const sessionKey = `allSelectedOptions_${userId}`;
        let allSelectedOptions = JSON.parse(sessionStorage.getItem(sessionKey)) || [];

        // Fonction pour mettre à jour le total global
        function updateTotal() {
          totalPrice = allSelectedOptions.reduce((sum, option) => {
            const price = option.price || 0;
            const quantity = option.quantity || 1;
            return sum + (price * quantity);
          }, 0);

          const totalElement = document.querySelector(".footer p span");
          if (totalElement) {
            totalElement.textContent = `${totalPrice.toFixed(2)} €`;
          }
        }

        // Gestion des clics sur les options
        document.querySelectorAll('.color-options .option img').forEach(option => {
          const optionId = option.getAttribute('data-mousse-id');
          const price = parseFloat(option.getAttribute('data-mousse-prix')) || 0;

          if (!optionId || isNaN(price)) {
            console.warn(`Attributs invalides : data-mousse-id=${optionId}, data-mousse-prix=${price}`);
            return;
          }

          const uniqueId = `${currentStep}_${optionId}`;

          if (allSelectedOptions.some(opt => opt.id === uniqueId)) {
            option.parentElement.classList.add('selected');
          }

          option.addEventListener('click', () => {
            document.querySelectorAll('.color-options .option img').forEach(opt => {
              opt.parentElement.classList.remove('selected');
            });

            allSelectedOptions = allSelectedOptions.filter(opt => !opt.id.startsWith(`${currentStep}_`));

            allSelectedOptions.push({
              id: uniqueId,
              price: price
            });
            option.parentElement.classList.add('selected');

            sessionStorage.setItem(sessionKey, JSON.stringify(allSelectedOptions));
            updateTotal();
          });
        });

        updateTotal();
      });
    </script>

    <script>
      document.addEventListener('DOMContentLoaded', () => {
        let totalPrice = 0; // Total global
        const suivantButton = document.querySelector('.btn-suivant');
        const userId = document.body.getAttribute('data-user-id');

        if (!userId) {
          console.error("ID utilisateur non trouvé.");
          return;
        }

        const sessionKey = `allSelectedOptions_${userId}`;
        let allSelectedOptions = JSON.parse(sessionStorage.getItem(sessionKey)) || [];

        // Fonction pour mettre à jour le total global
        function updateTotal() {
          totalPrice = allSelectedOptions.reduce((sum, option) => {
            const price = option.price || 0;
            const quantity = option.quantity || 1;
            return sum + (price * quantity);
          }, 0);

          const totalElement = document.querySelector(".footer p span");
          if (totalElement) {
            totalElement.textContent = `${totalPrice.toFixed(2)} €`;
          }
        }

        // Mettre à jour le total au chargement de la page
        updateTotal();

        // Gestion du bouton suivant
        suivantButton.addEventListener('click', (event) => {
          event.preventDefault();

          // Envoyer le total au backend via une requête POST
          fetch('save_total_price.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
              },
              body: JSON.stringify({
                user_id: userId,
                total_price: totalPrice
              }),
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                console.log("Prix total sauvegardé avec succès !");
                // Redirection vers la page suivante
                window.location.href = "recapitulatif-commande-tissu.php";
              } else {
                console.error("Erreur lors de la sauvegarde :", data.message);
              }
            })
            .catch(error => {
              console.error("Erreur de requête :", error);
            });
        });
      });
    </script>


    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const options = document.querySelectorAll('.color-options .option img');
        const mainImage = document.querySelector('.main-display img');
        const suivantButton = document.querySelector('.btn-suivant');
        const helpPopup = document.getElementById('help-popup');
        const abandonnerPopup = document.getElementById('abandonner-popup');
        const selectionPopup = document.getElementById('selection-popup');
        const selectedMousseInput = document.getElementById('selected-mousse'); // Input caché
        let selected = false;

        // Vérification si une sélection existe dans localStorage
        let savedMousseId = localStorage.getItem('selectedMousseId');

        if (savedMousseId) {
          options.forEach(img => {
            if (img.getAttribute('data-mousse-id') === savedMousseId) {
              img.classList.add('selected');
              mainImage.src = img.src;
              selectedMousseInput.value = savedMousseId;
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
            mainImage.src = img.src;
            selectedMousseInput.value = img.getAttribute('data-mousse-id'); // Mettre à jour l'input caché
            selected = true;

            saveSelection(img.getAttribute('data-mousse-id'));
          });
        });

        suivantButton.addEventListener('click', (event) => {
          if (!selected) {
            event.preventDefault();
            selectionPopup.style.display = 'flex';
          }
        });

        document.querySelector('#selection-popup .close-btn').addEventListener('click', () => {
          selectionPopup.style.display = 'none';
        });

        window.addEventListener('click', (event) => {
          if (event.target === selectionPopup) {
            selectionPopup.style.display = 'none';
          }
        });

        document.querySelector('.btn-aide').addEventListener('click', () => {
          helpPopup.style.display = 'flex';
        });

        document.querySelector('#help-popup .close-btn').addEventListener('click', () => {
          helpPopup.style.display = 'none';
        });

        window.addEventListener('click', (event) => {
          if (event.target === helpPopup) {
            helpPopup.style.display = 'none';
          }
        });

        document.querySelector('.btn-abandonner').addEventListener('click', () => {
          abandonnerPopup.style.display = 'flex';
        });

        document.querySelector('#abandonner-popup .yes-btn').addEventListener('click', () => {
          window.location.href = '../pages/';
        });

        document.querySelector('#abandonner-popup .no-btn').addEventListener('click', () => {
          abandonnerPopup.style.display = 'none';
        });

        function saveSelection(mousseId) {
          localStorage.setItem('selectedMousseId', mousseId);
        }
      });
    </script>

  </main>
  <?php require_once '../../squelette/footer.php' ?>
</body>

</html>