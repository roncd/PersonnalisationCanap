<?php
require '../../admin/config.php';
session_start();
require '../../admin/include/session_expiration.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../formulaire/Connexion.php");
    exit;
} 

// Vérifier que l'ID de la commande préfaite est bien passé en URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Commande préfaîte non spécifiée ou ID invalide.");
}

$id_commande_prefait = (int) $_GET['id']; // Sécurisation de l'entrée

// Récupérer les types de mousse (pour afficher tous les choix)
$stmt = $pdo->query("SELECT * FROM mousse");
$mousse = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer l'id de la mousse sélectionnée dans la commande pré-faite
$selectedMousseId = null;
$stmt = $pdo->prepare("SELECT id_mousse FROM commande_prefait WHERE id = ?");
$stmt->execute([$id_commande_prefait]);
$selected = $stmt->fetch(PDO::FETCH_ASSOC);

if ($selected) {
    $selectedMousseId = $selected['id_mousse'];
}

// Récupérer les données complètes de la mousse sélectionnée
$composition = [];
if ($selectedMousseId) {
    $stmt = $pdo->prepare("SELECT * FROM mousse WHERE id = ?");
    $stmt->execute([$selectedMousseId]);
    $composition['mousse'] = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $composition['mousse'] = null;
}

// Récupération du prix de la mousse existante (ou 0 si aucune)
$oldMoussePrice = !empty($composition['mousse']['prix']) ? (float) $composition['mousse']['prix'] : 0;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_mousse = $_POST['mousse_id'] ?? null;
    $prix = $_POST['prix'] ?? null;

    if ($id_mousse !== null && $prix !== null) {
        $stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ? AND id_commande_prefait = ?");
        $stmt->execute([$_SESSION['user_id'], $id_commande_prefait]);
        $commandeTemp = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($commandeTemp) {
            $stmt = $pdo->prepare("UPDATE commande_temporaire SET id_mousse = ?, prix = ? WHERE id = ?");
            $stmt->execute([$id_mousse, $prix, $commandeTemp['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO commande_temporaire (
                commentaire, date, statut, id_client, id_commande_prefait, id_structure, id_mousse, prix
            ) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?)");

            $stmt->execute(['', 'en cours', $_SESSION['user_id'], $id_commande_prefait, $selected['id_structure'], $id_mousse, $prix]);
        }

        header("Location: recapitulatif-commande-tissu.php?id=" . $id_commande_prefait);
        exit;
    } else {
        $error = "Veuillez sélectionner une mousse.";
    }
}
?>

<script>
  const ancienPrixMousse = <?= json_encode($oldMoussePrice) ?>;
  localStorage.setItem('ancienPrixMousse', ancienPrixMousse);
</script>




<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/processus.css">
  <link rel="stylesheet" href="../../styles/popup.css">
  <link rel="stylesheet" href="../../styles/canapPrefait.css">
  <link rel="stylesheet" href="../../styles/buttons.css">

  <title>Choisi ta mousse</title>
</head>

<body>


<style>
  .primary-img {
  width: 700px;
  height: 500px; /* Augmente la hauteur */
  border-radius: 10px;
  object-fit: cover; /* Pour éviter les déformations */
}

</style>




  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>


  <main>

 

    <div class="container">
      <!-- Colonne de gauche -->
      <div class="left-column">
        <h2 class="h2">Choisi ta mousse</h2>
       
      <section class="color-options">
 <?php if (!empty($mousse)): ?>
        <?php foreach ($mousse as $mousse_bois): ?>
          <?php
            $isSelected = ($mousse_bois['id'] == $selectedMousseId) ? 'selected' : '';
          ?>
          <div class="option">
            <img 
              src="../../admin/uploads/mousse/<?php echo htmlspecialchars($mousse_bois['img']); ?>" 
              alt="<?php echo htmlspecialchars($mousse_bois['nom']); ?>" 
              class="<?php echo $isSelected; ?>"
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
<p>Total : <span id="prix-total-final">0,00 €</span></p>

          <div class="buttons">
            <button onclick="retourEtapePrecedente()" class="btn-beige">Retour</button>
            <form method="POST" action="">
              <input type="hidden" name="mousse_id" id="selected-mousse">
              <input type="hidden" name="prix" id="input-prix-total">
              <button type="submit" class="btn-noir">Suivant</button>
            </form>
          </div>
        </div>
      </div>



  <!-- Colonne de droite -->
      <div class="right-column h2 ">
        <section class="main-display2">
<?php if (!empty($composition['mousse']['img'])): ?>
  <img 
    src="../../admin/uploads/mousse/<?php echo htmlspecialchars($composition['mousse']['img'], ENT_QUOTES); ?>" 
    alt="Structure du canapé"
    class="primary-img"
  >
<?php endif; ?>
        </section>
      </div>
    </div> 

    <!-- Popup besoin d'aide -->
    <div id="help-popup" class="popup ">
      <div class="popup-content">
        <h2>Vous avez une question ?</h2>
        <p>Contactez-nous au numéro suivant et un vendeur vous assistera :
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
        <h2>Êtes-vous sûr de vouloir abandonner ?</h2>
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
  </main>


  

    <!-- GESTION DES SELECTIONS -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const options = document.querySelectorAll('.color-options .option img');
    const mainImage = document.querySelector('.main-display2 img');
    const erreurPopup = document.getElementById('erreur-popup');
    const closeErreurBtn = erreurPopup?.querySelector('.btn-noir');
    const selectedMousseInput = document.getElementById('selected-mousse');
    const prixTotalInput = document.getElementById('input-prix-total');
    const form = document.querySelector('form');
    const totalSansMousseEl = document.getElementById('prix-sans-mousse');
    const prixTotalFinalEl = document.getElementById('prix-total-final');

    // Récupération des prix et sélection
    const prixAvantMousseStr = localStorage.getItem('prix_total_jusqua_dimensions');
    let ancienPrixMousse = parseFloat(localStorage.getItem('ancienPrixMousse') || '0');
    const prixBrut = parseFloat(prixAvantMousseStr || '0');
    let prixBaseSansMousse = Math.max(prixBrut - ancienPrixMousse, 0);
    let selectedMousseId = localStorage.getItem('selectedMousse') || '';

    // Affichage du prix sans mousse
    if (totalSansMousseEl) {
        totalSansMousseEl.textContent = prixBaseSansMousse.toFixed(2).replace('.', ',') + ' €';
    }

    // Calcul du prix initial
    let prixInitial = prixBaseSansMousse + ancienPrixMousse;
    prixTotalFinalEl.textContent = prixInitial.toFixed(2).replace('.', ',') + ' €';
    prixTotalInput.value = prixInitial.toFixed(2);

    function updateSelection(mousseElement, force = false) {
        const moussePrix = parseFloat(mousseElement.dataset.mousseBoisPrix || '0');
        const newMousseId = mousseElement.getAttribute('data-mousse-bois-id');

        // Si ce n'est pas un chargement forcé et la mousse est déjà sélectionnée, ne rien faire
        if (!force && selectedMousseId === newMousseId) {
            return;
        }

        // Supprimer les sélections visuelles
        options.forEach(opt => opt.classList.remove('selected'));

        // Appliquer la nouvelle sélection
        mousseElement.classList.add('selected');
        selectedMousseId = newMousseId;
        selectedMousseInput.value = selectedMousseId;

        if (mainImage) mainImage.src = mousseElement.src;

        // Mise à jour du prix
        const totalFinal = prixBaseSansMousse + moussePrix;
        prixTotalFinalEl.textContent = totalFinal.toFixed(2).replace('.', ',') + ' €';
        prixTotalInput.value = totalFinal.toFixed(2);

        // Sauvegarder dans le localStorage
        localStorage.setItem('ancienPrixMousse', moussePrix.toFixed(2));
        localStorage.setItem('selectedMousse', selectedMousseId);
    }

    // Réappliquer la sélection si elle existe
    if (selectedMousseId) {
        const previouslySelected = document.querySelector(`[data-mousse-bois-id="${selectedMousseId}"]`);
        if (previouslySelected) {
            updateSelection(previouslySelected, true); // true = forcer rechargement même si déjà sélectionnée
        }
    }

    // Gestion des clics sur les mousses
    options.forEach(img => {
        img.addEventListener('click', () => {
            updateSelection(img);
        });
    });

    // Validation du formulaire
    form?.addEventListener('submit', (e) => {
        if (!selectedMousseInput.value) {
            e.preventDefault();
            if (erreurPopup) erreurPopup.style.display = 'flex';
        }
    });

    // Fermeture du popup
    closeErreurBtn?.addEventListener('click', () => {
        if (erreurPopup) erreurPopup.style.display = 'none';
    });

    window.addEventListener('click', (event) => {
        if (event.target === erreurPopup) {
            erreurPopup.style.display = 'none';
        }
    });
});
</script>


  

  
        <!-- BOUTTON RETOUR -->
<script>
  function retourEtapePrecedente() {
    const id = new URLSearchParams(window.location.search).get('id');
    if (id) {
      window.location.href = `choix-dimension.php?id=${id}`;
    } else {
      alert("ID introuvable dans l'URL.");
    }
  }
</script>

  <?php require_once '../../squelette/footer.php'; ?>


</body>

</html>