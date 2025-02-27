<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../formulaire/Connexion.php");
    exit;
}

// Récupérer les banquettes disponibles depuis la base de données
$stmt = $pdo->query("SELECT * FROM type_banquette");
$banquettes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['banquette_id']) || empty($_POST['banquette_id']) || !isset($_POST['banquette_type'])) {
        echo "Erreur : aucune banquette sélectionnée.";
        exit;
    }

    $id_client = $_SESSION['user_id'];
    $id_banquette = $_POST['banquette_id'];
    $banquette_type = $_POST['banquette_type'];

    // Vérifier si une commande temporaire existe déjà pour cet utilisateur
    $stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ?");
    $stmt->execute([$id_client]);
    $existing_order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_order) {
        $stmt = $pdo->prepare("UPDATE commande_temporaire SET id_banquette = ? WHERE id_client = ?");
        $stmt->execute([$id_banquette, $id_client]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO commande_temporaire (id_client, id_banquette) VALUES (?, ?)");
        $stmt->execute([$id_client, $id_banquette]);
    }

    // Redirection en fonction du type de banquette
    if ($banquette_type === "Bois") {
        header("Location: etape3-bois-couleur.php");
    } elseif ($banquette_type === "Tissu") {
        header("Location: etape3-tissu-modele-banquette.php");
    } else {
        header("Location: etape1-2-dimension.php");
    }
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

  <title>Étape 2 - Choisi ton type de banquette</title>

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
<body>

<header>
  <?php require '../../squelette/header.php'; ?>
</header>

<main>
  <div class="fil-ariane-container" aria-label="fil-ariane">
    <ul class="fil-ariane">
      <li><a href="etape1-1-structure.php">Structure</a></li>
      <li><a href="etape1-2-dimension.php">Dimension</a></li>
      <li><a href="etape2-type-banquette.php" class="active">Banquette</a></li>
    </ul>
  </div>
  <div class="container">
    <div class="left-column transition">
      <h2>Étape 2 - Choisi ton type de banquette</h2>

      <section class="color-2options">
        <?php foreach ($banquettes as $banquette): ?>
          <div class="option transition">
            <img src="../../admin/uploads/banquette/<?php echo htmlspecialchars($banquette['img']); ?>" 
                 alt="<?php echo htmlspecialchars($banquette['nom']); ?>"
                 data-banquette-id="<?php echo $banquette['id']; ?>"
                 data-banquette-type="<?php echo htmlspecialchars($banquette['nom']); ?>"> 
            <p><?php echo htmlspecialchars($banquette['nom']); ?></p>
          </div>
        <?php endforeach; ?>
      </section>

      <div class="footer">
        <p>Total : <span>899 €</span></p>
        <div class="buttons">
        <button class="btn-retour transition" onclick="history.go(-1)">Retour</button>
          <form method="POST" action="">
            <input type="hidden" name="banquette_id" id="selected-banquette">
            <input type="hidden" name="banquette_type" id="selected-banquette-type">
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

    <!-- Pop-up de sélection d'option -->

  <div id="selection-popup" class="popup transition">
    <div class="popup-content">
      <h2>Veuillez sélectionner une option avant de continuer.</h2>
      <br>
      <button class="close-btn">OK</button>
    </div>
  </div>
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const options = document.querySelectorAll('.color-2options .option img'); 
    const selectedBanquetteInput = document.getElementById('selected-banquette');
    const selectedBanquetteTypeInput = document.getElementById('selected-banquette-type');
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
    const savedBanquetteId = localStorage.getItem('selectedBanquette');
    const savedBanquetteType = localStorage.getItem('selectedBanquetteType');

    if (savedBanquetteId && savedBanquetteType) {
      selectedBanquetteInput.value = savedBanquetteId;
      selectedBanquetteTypeInput.value = savedBanquetteType;

      options.forEach(img => {
        if (img.getAttribute('data-banquette-id') === savedBanquetteId && img.getAttribute('data-banquette-type') === savedBanquetteType) {
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
        selectedBanquetteInput.value = img.getAttribute('data-banquette-id');
        selectedBanquetteTypeInput.value = img.getAttribute('data-banquette-type');

        // Mise à jour de l'image principale
        mainImage.src = img.src;
        mainImage.alt = img.alt;

        selected = true;

        // Sauvegarder l'ID et le type de la banquette sélectionnée dans localStorage
        localStorage.setItem('selectedBanquette', img.getAttribute('data-banquette-id'));
        localStorage.setItem('selectedBanquetteType', img.getAttribute('data-banquette-type'));
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
