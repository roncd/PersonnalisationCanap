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
    $prix_dimensions = !empty($_POST["prix_dimensions"]) ? (float) trim($_POST["prix_dimensions"]) : null;

    if ($commande) {
      $id = $commande['id'];

      $stmt = $pdo->prepare("UPDATE commande_temporaire SET longueurA = ?, longueurB = ?, longueurC = ?, prix_dimensions = ? WHERE id = ?");
      if ($stmt->execute([$longueurA, $longueurB, $longueurC, $prix_dimensions, $id])) {
        header("Location: etape2-type-banquette.php");
        exit();
      }
    }
  }
}
?>

<?php
// Récupérer l'ID de la structure sélectionnée
if (isset($_GET['structure_id'])) {
  $structure_id = $_GET['structure_id'];

  // Récupérer la structure correspondante depuis la base de données
  $stmt = $pdo->prepare("SELECT nb_longueurs, img FROM structure WHERE id = ?");
  $stmt->execute([$structure_id]);
  $structure = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($structure) {
    $nbLongueurs = $structure['nb_longueurs'];
  } else {
    // Gestion de l'erreur si la structure n'existe pas
    $nbLongueurs = 0;
  }
} else {
  // Gestion de l'erreur si aucun ID de structure n'est passé dans l'URL
  $nbLongueurs = 0;
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
  <script type="module" src="../../script/keydown.js"></script>

  <title>Étape 1.2 - Choisi tes dimensions</title>

</head>

<body data-user-id="<?php echo $_SESSION['user_id']; ?>" data-current-step="1-dimensions">
  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>
  <main>
    <div class="fil-ariane-container h2" aria-label="fil-ariane">
      <ul class="fil-ariane">
        <li><a href="etape1-1-structure.php">Structure</a></li>
        <li><a href="etape1-2-dimension.php" class="active">Dimension</a></li>
        <li><a href="etape2-type-banquette.php">Banquette</a></li>
      </ul>
    </div>
    <div class="container transition">
      <!-- Colonne de gauche -->
      <div class="left-column ">
        <h2>Étape 1.2 - Choisi tes dimensions</h2>
        <form method="POST" class="formulaire">
          <p>Largeur banquette : <span class="bold">50cm (par défaut) </span> | Prix total des dimensions : <span id="dimension-price">0.00</span> €</p>
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
            <p>Total : <span>0 €</span></p>
            <div class="buttons">
              <button type="button" onclick="retourEtapePrecedente()" class="btn-beige  ">Retour</button>
              <input type="hidden" name="prix_dimensions" id="prix_dimensions_hidden" value="">
              <button type="submit" id="btn-suivant" class="btn-noir">Suivant</button>
            </div>
          </div>
        </form>
      </div>
      <script>
        document.addEventListener('DOMContentLoaded', () => {
          // Utiliser la variable PHP $nbLongueurs pour afficher les bons champs
          const nbLongueurs = <?php echo $nbLongueurs; ?>;
          updateVisibleInputs(nbLongueurs);

          function updateVisibleInputs(nb) {
            const a = document.getElementById('longueurA').closest('.form-row');
            const b = document.getElementById('longueurB').closest('.form-row');
            const c = document.getElementById('longueurC').closest('.form-row');

            a.style.display = nb >= 1 ? 'block' : 'none';
            b.style.display = nb >= 2 ? 'block' : 'none';
            c.style.display = nb >= 3 ? 'block' : 'none';
          }
        });
      </script>

      <script>
        document.addEventListener("DOMContentLoaded", () => {
          const url = new URL(window.location.href);
          const structureId = url.searchParams.get("structure_id");

          if (structureId) {
            // Sauvegarde l'ID en localStorage
            localStorage.setItem("selectedStructureId", structureId);
          } else {
            // Si pas dans l'URL, on essaie de le récupérer depuis le localStorage
            const savedStructureId = localStorage.getItem("selectedStructureId");
            if (savedStructureId) {
              // Redirige en rajoutant l'id manquant dans l'URL
              url.searchParams.set("structure_id", savedStructureId);
              window.location.href = url.toString();
            }
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
          <?php if (!empty($structure['img'])): ?>
            <img src="../../admin/uploads/structure/<?php echo htmlspecialchars($structure['img']); ?>" alt="Structure sélectionnée">
          <?php else: ?>
            <img src="../../medias/process-main-image.png" alt="Image par défaut">
          <?php endif; ?>
        </section>
      </div>
    </div>

    <!-- Popup d'erreur si les dimensions ne sont pas remplies -->
    <div id="erreur-popup" class="popup ">
      <div class="popup-content">
        <h2>Veuillez choisir une option avant de continuer.</h2>
        <button class="btn-noir">OK</button>
      </div>
    </div>

    <!-- Popup besoin d'aide -->
    <div id="help-popup" class="popup ">
      <div class="popup-content">
        <h2>Vous avez une question ?</h2>
        <p>Contactez-nous au numéro suivant et un vendeur vous assistera :</p>
        <strong>06 58 47 58 56</strong>
        <br><br>
        <button class="btn-noir">Merci !</button>
      </div>
    </div>

    <!-- Popup abandon -->
    <div id="abandonner-popup" class="popup ">
      <div class="popup-content">
        <h2>Êtes-vous sûr de vouloir abandonner ?</h2>
        <br>
        <button class="btn-beige">Oui...</button>
        <button class="btn-noir">Non !</button>
      </div>
    </div>

    <!-- GESTION DES SELECTIONS -->
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        // Sélection des éléments
        const form = document.querySelector('.formulaire');
        const erreurPopup = document.getElementById('erreur-popup');
        const closeErreurBtn = erreurPopup.querySelector('.btn-noir');
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


    <!-- VARIATION DES PRIX  -->
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        let totalPrice = 0; // Total global pour toutes les étapes

        // Identifier l'étape actuelle (par exemple, "1-dimensions")
        const currentStep = "1-dimensions"; // Changez cette valeur pour chaque étape (par exemple "1-dimensions", "4-2", etc.)

        // Charger l'ID utilisateur depuis une variable PHP intégrée dans le HTML
        const userId = document.body.getAttribute('data-user-id'); // Ex. <body data-user-id="<?php echo $_SESSION['user_id']; ?>">
        if (!userId) {
          console.error("ID utilisateur non trouvé. Vérifiez que 'data-user-id' est bien défini dans le HTML.");
          return;
        }

        // Charger les données spécifiques à l'utilisateur depuis sessionStorage
        const sessionKey = `allSelectedOptions_${userId}`;
        const dimensionKey = `${currentStep}_dimensionsValues_${userId}`;
        let allSelectedOptions = JSON.parse(sessionStorage.getItem(sessionKey)) || [];
        let savedDimensions = JSON.parse(sessionStorage.getItem(dimensionKey)) || {
          longueurA: "",
          longueurB: "",
          longueurC: ""
        };

        // Vérifier si `allSelectedOptions` est un tableau
        if (!Array.isArray(allSelectedOptions)) {
          allSelectedOptions = [];
          console.warn("allSelectedOptions n'était pas un tableau. Réinitialisé à []");
        }


        // Fonction pour ajouter les dimensions au calcul
        function calculateDimensionPrice() {
          const longueurA = parseFloat(document.getElementById("longueurA").value) || 0;
          const longueurB = parseFloat(document.getElementById("longueurB").value) || 0;
          const longueurC = parseFloat(document.getElementById("longueurC").value) || 0;

          const totalMeters = (longueurA + longueurB + longueurC) / 100;
          const dimensionPrice = totalMeters * 350;

          document.getElementById("dimension-price").textContent = dimensionPrice.toFixed(2);

          // Mettre à jour le champ caché
          document.getElementById("prix_dimensions_hidden").value = dimensionPrice.toFixed(2);

          // Supprimer les dimensions précédentes pour cette étape
          allSelectedOptions = allSelectedOptions.filter(opt => !opt.id.startsWith(`${currentStep}_`));

          // Ajouter les dimensions au stockage global
          allSelectedOptions.push({
            id: `${currentStep}_dimensions`,
            price: dimensionPrice
          });

          sessionStorage.setItem(sessionKey, JSON.stringify(allSelectedOptions));
        }

        // Fonction pour sauvegarder les valeurs des dimensions dans sessionStorage
        function saveDimensions() {
          const longueurA = document.getElementById("longueurA").value || "";
          const longueurB = document.getElementById("longueurB").value || "";
          const longueurC = document.getElementById("longueurC").value || "";

          const dimensions = {
            longueurA,
            longueurB,
            longueurC
          };
          sessionStorage.setItem(dimensionKey, JSON.stringify(dimensions));
          console.log("Dimensions sauvegardées :", dimensions);
        }

        // Fonction pour mettre à jour le total global
        function updateTotal() {
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

        // Empêcher la saisie de plus de 3 chiffres dans les champs de type number
        document.querySelectorAll(".input-field").forEach(input => {
          input.addEventListener("input", () => {
            if (input.value.length > 3) {
              input.value = input.value.slice(0, 3);
            }
          });
        });

        // Pré-remplir les champs avec les dimensions sauvegardées
        document.getElementById("longueurA").value = savedDimensions.longueurA;
        document.getElementById("longueurB").value = savedDimensions.longueurB;
        document.getElementById("longueurC").value = savedDimensions.longueurC;

        // Mettre à jour le total à chaque modification des dimensions
        document.querySelectorAll(".input-field").forEach(input => {
          input.addEventListener("input", () => {
            calculateDimensionPrice();
            saveDimensions(); // Sauvegarder les dimensions saisies
            updateTotal();
          });
        });

        // Initialiser le total dès le chargement de la page
        calculateDimensionPrice();
        updateTotal();
      });
    </script>


    <!-- BOUTTON RETOUR -->
    <script>
      function retourEtapePrecedente() {
        window.location.href = "etape1-1-structure.php";
      }
    </script>
  </main>
  <?php require_once '../../squelette/footer.php' ?>
</body>

</html>