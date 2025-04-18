<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
  header("Location: ../formulaire/Connexion.php");
  exit;
}

// Récupérer les motifs de bois depuis la base de données
$stmt = $pdo->query("SELECT * FROM motif_bois");
$motif_bois = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['motif_bois_id']) || empty($_POST['motif_bois_id'])) {
    echo "Erreur : Aucun type de bois sélectionné.";
    exit;
  }


  $id_client = $_SESSION['user_id'];
  $id_motif_bois = $_POST['motif_bois_id'];


  // Vérifier si une commande temporaire existe déjà pour cet utilisateur
  $stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ?");
  $stmt->execute([$id_client]);
  $existing_order = $stmt->fetch(PDO::FETCH_ASSOC);


  if ($existing_order) {
    $stmt = $pdo->prepare("UPDATE commande_temporaire SET id_motif_bois = ? WHERE id_client = ?");
    $stmt->execute([$id_motif_bois, $id_client]);
  } else {
    $stmt = $pdo->prepare("INSERT INTO commande_temporaire (id_client, id_motif_bois) VALUES (?, ?)");
    $stmt->execute([$id_client, $id_motif_bois]);
  }


  // Rediriger vers l'étape suivante
  header("Location: recapitulatif-commande-bois.php");
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

  <title>Étape 8 - Choisi ton motif de coussin</title>
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
        <li><a href="etape3-bois-couleur.php">Couleur</a></li>
        <li><a href="etape4-bois-decoration.php">Décoration</a></li>
        <li><a href="etape5-bois-accoudoir.php">Accoudoirs</a></li>
        <li><a href="etape6-bois-dossier.php">Dossier</a></li>
        <li><a href="etape7-bois-mousse.php">Mousse</a></li>
        <li><a href="etape8-1-bois-tissu.php" class="active">Tissu</a></li>
      </ul>
    </div>

    <div class="container">
      <!-- Colonne de gauche -->
      <div class="left-column transition">
        <h2>Étape 8 - Choisi ton motif de coussin</h2>

        <section class="color-options">
          <?php if (!empty($motif_bois)): ?>
            <?php foreach ($motif_bois as $bois): ?>
              <div class="option transition">
                <img src="../../admin/uploads/motif-bois/<?php echo htmlspecialchars($bois['img']); ?>"
                  alt="<?php echo htmlspecialchars($bois['nom']); ?>"
                  data-bois-id="<?php echo $bois['id']; ?>"
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
            <button class="btn-retour transition" onclick="history.go(-1)">Retour</button>
            <form method="POST" action="">
              <input type="hidden" name="motif_bois_id" id="selected-motif_bois">
              <button type="submit" class="btn-suivant transition">Suivant</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Colonne de droite -->
      <div class="right-column transition">
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
    <div id="help-popup" class="popup transition">
      <div class="popup-content">
        <h2>Vous avez une question ?</h2>
        <p>Contactez-nous au numéro suivant et un vendeur vous assistera :
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
        <h2>Êtes-vous sûr de vouloir abandonner ?</h2>
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
        let totalPrice = 0; // Total global pour toutes les étapes

        // Identifier l'étape actuelle (par exemple, "8-2-bois-tissu")
        const currentStep = "8-2-bois-tissu"; // Étape spécifique

        // Charger l'ID utilisateur depuis une variable PHP intégrée dans le HTML
        const userId = document.body.getAttribute('data-user-id');
        if (!userId) {
          console.error("ID utilisateur non trouvé. Vérifiez que 'data-user-id' est bien défini dans le HTML.");
          return;
        }
        console.log("ID utilisateur récupéré :", userId);

        // Charger toutes les options sélectionnées depuis sessionStorage (par utilisateur)
        const sessionKey = `allSelectedOptions_${userId}`;
        let allSelectedOptions = JSON.parse(sessionStorage.getItem(sessionKey)) || [];
        console.log("Données globales récupérées depuis sessionStorage :", allSelectedOptions);

        // Vérifier si `allSelectedOptions` est un tableau
        if (!Array.isArray(allSelectedOptions)) {
          allSelectedOptions = [];
          console.warn("allSelectedOptions n'était pas un tableau. Réinitialisé à []");
        }

        // Fonction pour mettre à jour le total global
        function updateTotal() {
          // Calculer le total global en prenant en compte les quantités
          totalPrice = allSelectedOptions.reduce((sum, option) => {
            const price = option.price || 0; // S'assurer que le prix est valide
            const quantity = option.quantity || 1; // Par défaut, quantité = 1
            return sum + (price * quantity);
          }, 0);

          console.log("Total global mis à jour :", totalPrice);

          // Mettre à jour le total dans l'interface
          const totalElement = document.querySelector(".footer p span");
          if (totalElement) {
            totalElement.textContent = `${totalPrice.toFixed(2)} €`;
          } else {
            console.error("L'élément '.footer p span' est introuvable !");
          }
        }

        // Gérer les sélections d'options pour cette étape
        document.querySelectorAll('.color-options .option img').forEach(option => {
          const optionId = option.getAttribute('data-bois-id');
          const price = parseFloat(option.getAttribute('data-bois-prix')) || 0;

          // Vérifiez si les attributs sont valides
          if (!optionId || isNaN(price)) {
            console.warn(`Attributs manquants ou invalides pour une option : data-bois-id=${optionId}, data-bois-prix=${price}`);
            return; // Ignorer cette option si les attributs ne sont pas valides
          }

          // Créer un identifiant unique basé sur l'étape actuelle
          const uniqueId = `${currentStep}_${optionId}`;

          console.log(`Option détectée : ID Unique = ${uniqueId}, Prix = ${price}`);

          // Vérifier si l'option est déjà sélectionnée (dans toutes les étapes)
          if (allSelectedOptions.some(opt => opt.id === uniqueId)) {
            option.parentElement.classList.add('selected');
          }

          // Gérer les clics sur les options
          option.addEventListener('click', () => {
            // Supprimer toutes les sélections existantes dans l'étape actuelle
            document.querySelectorAll('.color-options .option img').forEach(opt => {
              opt.parentElement.classList.remove('selected'); // Retirer la classe CSS
            });

            // Supprimer les options de cette étape dans le stockage global
            allSelectedOptions = allSelectedOptions.filter(opt => !opt.id.startsWith(`${currentStep}_`));

            // Ajouter l'option actuellement sélectionnée
            allSelectedOptions.push({
              id: uniqueId,
              price: price
            });
            option.parentElement.classList.add('selected'); // Ajouter la classe CSS

            console.log(`Option sélectionnée : ID Unique = ${uniqueId}, Prix = ${price}`);

            // Sauvegarder les données globales dans sessionStorage pour cet utilisateur
            sessionStorage.setItem(sessionKey, JSON.stringify(allSelectedOptions));

            // Mettre à jour le total
            updateTotal();
          });
        });

        // Initialiser le total dès le chargement de la page
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
                window.location.href = "recapitulatif-commande-bois.php";
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
        const selectedCouleurBoisInput = document.getElementById('selected-motif_bois'); // Input caché

        let selectedMotifBoisId = localStorage.getItem('selectedMotifBois') || '';
        let selected = selectedMotifBoisId !== '';

        // Restaurer la sélection si elle existe
        options.forEach(img => {
          if (img.getAttribute('data-bois-id') === selectedMotifBoisId) {
            img.classList.add('selected');
            mainImage.src = img.src;
            selectedCouleurBoisInput.value = selectedMotifBoisId;
          }
        });

        document.querySelectorAll('.transition').forEach(element => {
          element.classList.add('show');
        });

        options.forEach(img => {
          img.addEventListener('click', () => {
            options.forEach(opt => opt.classList.remove('selected'));
            img.classList.add('selected');
            mainImage.src = img.src;
            selectedMotifBoisId = img.getAttribute('data-bois-id');
            selectedCouleurBoisInput.value = selectedMotifBoisId;
            selected = true;
            saveSelection();
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

        function saveSelection() {
          localStorage.setItem('selectedMotifBois', selectedMotifBoisId);
        }
      });
    </script>

  </main>

  <?php require_once '../../squelette/footer.php' ?>
</body>

</html>