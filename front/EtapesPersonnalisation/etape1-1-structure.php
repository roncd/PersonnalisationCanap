<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../formulaire/Connexion.php");
    exit;
}

// Récupérer les structures disponibles depuis la base de données
$stmt = $pdo->query("SELECT * FROM structure");
$structures = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['structure_id']) || empty($_POST['structure_id'])) {
        echo "Erreur : aucune structure sélectionnée.";
        exit;
    }

    $id_client = $_SESSION['user_id']; // Assurez-vous que user_id correspond bien à id_client
    $id_structure = $_POST['structure_id'];

    // Vérifier si une commande temporaire existe déjà pour cet utilisateur
    $stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ?");
    $stmt->execute([$id_client]);
    $existing_order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_order) {
        // Mise à jour de la structure sélectionnée
        $stmt = $pdo->prepare("UPDATE commande_temporaire SET id_structure = ? WHERE id_client = ?");
        $stmt->execute([$id_structure, $id_client]);
    } else {
        // Création d'une nouvelle commande temporaire
        $stmt = $pdo->prepare("INSERT INTO commande_temporaire (id_client, id_structure) VALUES (?, ?)");
        $stmt->execute([$id_client, $id_structure]);
    }

    // Rediriger vers l'étape suivante
    header("Location: etape1-2-dimension.php");
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

  <title>Étape 1 - Choisi ta structure</title>

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
<body data-user-id="<?php echo $_SESSION['user_id']; ?>">

<header>
  <?php require '../../squelette/header.php'; ?>
</header>

<main>
  <div class="fil-ariane-container" aria-label="fil-ariane">
    <ul class="fil-ariane">
      <li><a href="etape1-1-structure.php" class="active">Structure</a></li>
      <li><a href="etape1-2-dimension.php">Dimension</a></li>
      <li><a href="etape2-type-banquette.php">Banquette</a></li>
    </ul>
  </div>
  <div class="container">
    <div class="left-column transition">
      <h2>Étape 1 - Choisi ta structure</h2>
      
      <section class="color-options">
        <?php foreach ($structures as $structure): ?>
          <div class="option transition">
            <img src="../../admin/uploads/structure/<?php echo htmlspecialchars($structure['img']); ?>" 
                 alt="<?php echo htmlspecialchars($structure['nom']); ?>"
                 data-structure-id="<?php echo $structure['id']; ?>">
            <p><?php echo htmlspecialchars($structure['nom']); ?></p>
          </div>
        <?php endforeach; ?>
      </section>

      <div class="footer">
        <p>Total : <span>899 €</span></p>
        <div class="buttons">
          <form method="POST" action="">
            <input type="hidden" name="structure_id" id="selected-structure">
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
        <img id="main-image" src="../../medias/process-main-image.png" alt="Banquette sélectionnée" class="transition">
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
      <h2>Veuillez sélectionner une option avant de continuer.</h2>
      <br>
      <button class="close-btn">OK</button>
    </div>
  </div>
  
  <script>
   document.addEventListener('DOMContentLoaded', () => {
    let totalPrice = 0; // Total global pour toutes les étapes

    // Charger l'ID utilisateur depuis une variable PHP intégrée dans le HTML
    const userId = document.body.getAttribute('data-user-id'); // Ex. <body data-user-id="<?php echo $_SESSION['user_id']; ?>">
    if (!userId) {
        console.error("ID utilisateur non trouvé. Vérifiez que 'data-user-id' est bien défini dans le HTML.");
        return;
    }
    console.log("ID utilisateur récupéré :", userId);

    // Charger toutes les options sélectionnées depuis sessionStorage (par utilisateur)
    const sessionKey = `allSelectedOptions_${userId}`;
    const selectedKey = `selectedOptions_${userId}`;
    let allSelectedOptions = JSON.parse(sessionStorage.getItem(sessionKey)) || [];
    let selectedOptions = JSON.parse(sessionStorage.getItem(selectedKey)) || {}; // Charger `selectedOptions` pour cet utilisateur
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

    // Sauvegarder les données mises à jour dans sessionStorage
    function saveData() {
        sessionStorage.setItem(sessionKey, JSON.stringify(allSelectedOptions));
        sessionStorage.setItem(selectedKey, JSON.stringify(selectedOptions));
    }

    // Initialiser le total dès le chargement de la page
    updateTotal();

    // Sauvegarder les données au chargement de la page (au cas où elles sont modifiées)
    saveData();
});

  </script>
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const options = document.querySelectorAll('.color-options .option img'); 
    const selectedStructureInput = document.getElementById('selected-structure');
    const suivantButton = document.querySelector('.btn-suivant');
    const helpPopup = document.getElementById('help-popup'); // Popup besoin d'aide
    const abandonnerPopup = document.getElementById('abandonner-popup'); // Popup abandonner
    const selectionPopup = document.getElementById('selection-popup');
    const mainImage = document.getElementById('main-image'); 
    let selected = false;

    document.querySelectorAll('.transition').forEach(element => {
      element.classList.add('show');
    });

    // Vérifier s'il y a une sélection enregistrée dans localStorage
    const savedStructureId = localStorage.getItem('selectedStructure');
    if (savedStructureId) {
      selectedStructureInput.value = savedStructureId;
      options.forEach(img => {
        if (img.getAttribute('data-structure-id') === savedStructureId) {
          img.classList.add('selected');
          mainImage.src = img.src;
          mainImage.alt = img.alt;
          selected = true;
        }
      });
    }

    options.forEach(img => {
      img.addEventListener('click', () => {
        options.forEach(opt => opt.classList.remove('selected'));
        img.classList.add('selected');
        selectedStructureInput.value = img.getAttribute('data-structure-id');

        // Mise à jour de l'image principale
        mainImage.src = img.src;
        mainImage.alt = img.alt;

        selected = true;

        // Sauvegarder l'ID de la structure sélectionnée dans localStorage
        localStorage.setItem('selectedStructure', img.getAttribute('data-structure-id'));
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

    // Gestion du popup "Besoin d'aide"
    document.querySelector('.btn-aide').addEventListener('click', () => {
      helpPopup.style.display = 'flex';
    });

    document.querySelector('.close-btn').addEventListener('click', () => {
      helpPopup.style.display = 'none';
    });

    // Gestion du popup "Abandonner"
    document.querySelector('.btn-abandonner').addEventListener('click', () => {
      abandonnerPopup.style.display = 'flex';
    });

    document.querySelector('.no-btn').addEventListener('click', () => {
      abandonnerPopup.style.display = 'none';
    });

    document.querySelector('.yes-btn').addEventListener('click', () => {
      window.location.href = 'index.php'; // Redirection vers la page d'accueil
    });
  });
</script>

</main>

<?php require_once '../../squelette/footer.php'; ?>
</body>
</html>
