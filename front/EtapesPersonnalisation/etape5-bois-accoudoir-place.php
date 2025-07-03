<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Récupérer ou créer la commande temporaire pour ce client
$stmt = $pdo->prepare("SELECT * FROM commande_temporaire WHERE id_client = ?");
$stmt->execute([$id_client]);
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$commande) {
  // Créer une nouvelle commande temporaire si inexistante
  $stmt = $pdo->prepare("INSERT INTO commande_temporaire (id_client) VALUES (?)");
  $stmt->execute([$id_client]);
  $commande_id = $pdo->lastInsertId();
} else {
  $commande_id = $commande['id'];
}

// Récupérer la structure choisie dans la commande temporaire
$stmt = $pdo->prepare("
    SELECT s.nom, s.img
    FROM commande_temporaire ct
    JOIN structure s ON s.id = ct.id_structure
    WHERE ct.id_client = ?
    LIMIT 1
");
$stmt->execute([$id_client]);
$currentStructure = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les accoudoirs associés à la commande temporaire
$stmt = $pdo->prepare("
    SELECT cta.id_accoudoir_bois, cta.nb_accoudoir, ab.nom, ab.img
    FROM commande_temp_accoudoir cta
    JOIN accoudoir_bois ab ON cta.id_accoudoir_bois = ab.id
    WHERE cta.id_commande_temporaire = ?
");
$stmt->execute([$commande_id]);
$accoudoirs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les types d'accoudoirs (pour affichage)
$stmt = $pdo->query("SELECT * FROM accoudoir_bois");
$accoudoir_bois = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gérer la soumission du formulaire POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $id_gauche = $_POST['id_accoudoir_gauche'] ?: null;
  $id_droit  = $_POST['id_accoudoir_droit']  ?: null;

  // Mise à jour dans commande_temporaire uniquement accoudoirs gauche et droit
  $stmt = $pdo->prepare("UPDATE commande_temporaire SET id_accoudoir_gauche = ?, id_accoudoir_droit = ? WHERE id = ?");
  $stmt->execute([$id_gauche, $id_droit, $commande_id]);

  // Si accoudoirs bois sont fournis, on met à jour la table pivot, sinon on ignore
  if (isset($_POST['accoudoir_bois_id']) && !empty($_POST['accoudoir_bois_id'])) {
    $id_accoudoirs = explode(',', $_POST['accoudoir_bois_id']);

    // Suppression des anciens accoudoirs bois pour cette commande temporaire
    $stmt = $pdo->prepare("DELETE FROM commande_temp_accoudoir WHERE id_commande_temporaire = ?");
    $stmt->execute([$commande_id]);

    $insertStmt = $pdo->prepare("INSERT INTO commande_temp_accoudoir (id_commande_temporaire, id_accoudoir_bois, nb_accoudoir) VALUES (?, ?, ?)");
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM accoudoir_bois WHERE id = ?");

    $js_accoudoir_ids = [];
    $js_nb_accoudoirs = [];

    foreach ($id_accoudoirs as $id_accoudoir) {
      $checkStmt->execute([$id_accoudoir]);
      if ($checkStmt->fetchColumn() > 0) {
        // On met toujours 1 car nb_accoudoir n'est plus géré
        $insertStmt->execute([$commande_id, $id_accoudoir, 1]);
        $js_accoudoir_ids[] = $id_accoudoir;
        $js_nb_accoudoirs[] = 1;
      }
    }

    $js_ids = implode(',', $js_accoudoir_ids);
    $js_nbs = implode(',', $js_nb_accoudoirs);

    echo "<script>
            localStorage.setItem('selectedAccoudoirBois', '$js_ids');
            localStorage.setItem('selectedNbAccoudoirBois', '$js_nbs');
            window.location.href = 'etape6-bois-dossier.php';
          </script>";
    exit;
  }

  // Si pas d'accoudoirs bois, on redirige quand même
  echo "<script>window.location.href = 'etape6-bois-dossier.php';</script>";
  exit;
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


  <title>Étape 5 - Place tes accoudoirs</title>

</head>


<body data-user-id="<?php echo $_SESSION['user_id']; ?>" data-current-step="5-accoudoir-bois">
  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <main>
    <div class="fil-ariane-container h2" aria-label="fil-ariane" id="filAriane">
      <ul class="fil-ariane">
        <li><a href="etape1-1-structure.php">Structure</a></li>
        <li><a href="etape1-2-dimension.php">Dimension</a></li>
        <li><a href="etape2-type-banquette.php">Banquette</a></li>
        <li><a href="etape3-bois-couleur.php">Couleur</a></li>
        <li><a href="etape4-bois-decoration.php">Décoration</a></li>
        <li><a href="etape5-bois-accoudoir.php" class="active">Accoudoirs</a></li>
        <li><a href="etape6-bois-dossier.php">Dossier</a></li>
        <li><a href="etape7-1-bois-tissu.php">Tissu</a></li>
        <li><a href="etape8-bois-mousse.php">Mousse</a></li>
      </ul>
    </div>
    <div class="container transition">
      <!-- Colonne de gauche -->
      <div class="left-column ">
        <h2>Étape 5 - Place tes accoudoirs</h2>
        <!-- <label for="deco">Voulez vous ajouter la décoration (choisi avant) sur l'accoudoir ?</label>
        <select class="select-field" id="deco" name="deco">
          <option value="Non" selected>Non</option>
          <option value="oui">Oui</option>
        </select> -->
        <?php
// $accoudoirs est bien défini ici (après la requête ci-dessus)

$index = 0;
?>
<section class="color-options">
  <?php foreach ($accoudoirs as $acc): ?>
    <?php for ($i = 1; $i <= $acc['nb_accoudoir']; $i++, $index++): ?>
      <div class="option" data-acc-id="<?= htmlspecialchars($acc['id_accoudoir_bois']) ?>">
        <img src="../../admin/uploads/accoudoirs-bois/<?= htmlspecialchars($acc['img']) ?>" 
             alt="<?= htmlspecialchars($acc['nom']) ?>">
        <p><?= htmlspecialchars($acc['nom']) ?></p>

        <label>
          <select class="place-select-field" data-idx="<?= $index ?>">
            <option value=""> Choisir </option>
            <option value="gauche">Gauche</option>
            <option value="droit">Droite</option>
          </select>
        </label>
      </div>
    <?php endfor; ?>
  <?php endforeach; ?>
</section>
        <div class="footer">
          <p>Total : <span>0 €</span></p>
          <div class="buttons">
            <button onclick="retourEtapePrecedente()" class="btn-beige">Retour</button>
            <form method="POST" action="">
              <?php
$leftSaved  = $commande['id_accoudoir_gauche'] ?? '';
$rightSaved = $commande['id_accoudoir_droit']  ?? '';
?>
<input type="hidden" name="id_accoudoir_gauche" id="input-acc-gauche"
       value="<?= htmlspecialchars($leftSaved) ?>">
<input type="hidden" name="id_accoudoir_droit"  id="input-acc-droit"
       value="<?= htmlspecialchars($rightSaved) ?>">

              <button type="button" id="btn-suivant" class="btn-noir">Suivant</button>
            </form>
          </div>
        </div>
      </div>

<!-- Colonne de droite -->
<div class="right-column">
  <section class="main-display">
    <div class="buttons ">
      <button id="btn-aide" class="btn-beige">Besoin d'aide ?</button>
      <button type="button" data-url="../pages/dashboard.php" id="btn-abandonner" class="btn-noir">Abandonner</button>
    </div>

    <?php if ($currentStructure): ?>
      <img src="../../admin/uploads/structure/<?= htmlspecialchars($currentStructure['img']) ?>"
           alt="<?= htmlspecialchars($currentStructure['nom']) ?>">
    <?php else: ?>
      <!-- fallback si aucune structure encore choisie -->
      <img src="../../medias/placeholder-structure.png" alt="Structure">
    <?php endif; ?>
  </section>
</div>

</div>

    <!-- Popup besoin d'aide -->
    <div id="help-popup" class="popup">
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
    <div id="abandonner-popup" class="popup">
      <div class="popup-content">
        <h2>Êtes vous sûr de vouloir abandonner ?</h2>
        <br>
        <button class="btn-beige">Oui...</button>
        <button class="btn-noir">Non !</button>
      </div>
    </div>

    <!-- Popup d'erreur si option non selectionnée -->
    <div id="erreur-popup" class="popup">
      <div class="popup-content">
        <h2>Veuillez choisir une option avant de continuer.</h2>
        <button class="btn-noir">OK</button>
      </div>
    </div>

    <!-- Popup maximum 2 accoudoirs -->
    <div id="accoudoir-popup" class="popup">
      <div class="popup-content">
        <h2>Vous devez choisir maximum 2 accoudoirs.</h2>
        <button class="btn-noir">OK</button>
      </div>
    </div>

        <!-- Popup bloquant pour les étapes non validées -->
<div id="filariane-popup" class="popup">
  <div class="popup-content">
    <h2>Veuillez sélectionner une option et cliquez sur "suivant" pour passer à l’étape d’après.</h2>
    <button class="btn-noir">OK</button>
  </div>
</div>

<!-- Popup “un seul accoudoir par côté” -->
<div id="cote-popup" class="popup">
  <div class="popup-content">
    <h2>Un seul accoudoir par côté.</h2>
    <button class="btn-noir">OK</button>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {

  /* --------------------------   CONSTANTES   -------------------------- */
  const inputG      = document.getElementById('input-acc-gauche');
  const inputD      = document.getElementById('input-acc-droit');
  const popupErreur = document.getElementById('erreur-popup');
  const popupCote   = document.getElementById('cote-popup');
  const btnSuivant  = document.getElementById('btn-suivant');
  const form        = document.querySelector('form');

  /* ----------------------  pré‑chargement  ------------------------- */
function initSelections() {
  const leftSaved  = inputG.value;
  const rightSaved = inputD.value;

  // on vide tout
  document.querySelectorAll('.place-select-field').forEach(sel => sel.value = '');

  /* -- Cas où les deux côtés utilisent le même accoudoir -- */
  if (leftSaved && rightSaved && leftSaved === rightSaved) {
    const fields = document.querySelectorAll(
      `.option[data-acc-id="${leftSaved}"] .place-select-field`
    );
    if (fields[0]) fields[0].value = 'gauche';   // premier exemplaire → gauche
    if (fields[1]) fields[1].value = 'droit';    // deuxième exemplaire → droite
  } else {
    /* -- Cas normal : IDs différents -- */
    if (leftSaved) {
      const sel = document.querySelector(
        `.option[data-acc-id="${leftSaved}"] .place-select-field`
      );
      if (sel) sel.value = 'gauche';
    }
    if (rightSaved) {
      const sel = document.querySelector(
        `.option[data-acc-id="${rightSaved}"] .place-select-field`
      );
      if (sel) sel.value = 'droit';
    }
  }

  updatePreview();
}


  /* -----------  recalcule les inputs cachés à chaque changement ----- */
  function refreshHiddenInputs() {
    let left = '', right = '';

    document.querySelectorAll('.place-select-field').forEach(sel => {
      const id = sel.closest('.option').dataset.accId;
      if (sel.value === 'gauche') left  = id;          // un seul « gauche » conservé
      if (sel.value === 'droit')  right = id;          // un seul « droit » conservé
    });

    inputG.value = left;
    inputD.value = right;
  }

  /* -------------------  sélection / placement  -------------------- */
  document.querySelectorAll('.place-select-field').forEach(sel => {
    sel.addEventListener('change', () => {
      // 1) rafraîchir les inputs cachés
      refreshHiddenInputs();
      // 2) mettre à jour l’aperçu
      updatePreview();
    });
  });

  /* ----------------------  pré‑visualisation  --------------------- */
  function updatePreview() {
    [['gauche', inputG.value], ['droit', inputD.value]].forEach(([side, id]) => {
      const img = document.getElementById(side === 'gauche' ? 'arm-left' : 'arm-right');
      if (id) {
        const optionImg = document.querySelector(`.option[data-acc-id="${id}"] img`);
        if (optionImg) {
          img.src = optionImg.src;
          img.classList.remove('hidden');
        } else {
          img.classList.add('hidden');
        }
      } else {
        img.classList.add('hidden');
      }
    });
  }

  /* ---------------------  Bouton “Suivant”  ----------------------- */
  btnSuivant.addEventListener('click', (e) => {
    e.preventDefault();

    // recompte pour vérifier qu’on n’a qu’un seul sélecteur par côté
    let countG = 0, countD = 0;
    document.querySelectorAll('.place-select-field').forEach(sel => {
      if (sel.value === 'gauche') countG++;
      if (sel.value === 'droit')  countD++;
    });

    if (countG === 0 && countD === 0) {
      popupErreur.style.display = 'flex';
      e.stopImmediatePropagation();
      return;
    }
    if (countG > 1 || countD > 1) {
      popupCote.style.display = 'flex';
      e.stopImmediatePropagation();
      return;
    }

    // tout est OK → on soumet
    form.submit();
  });

  /* --------------------  Fermeture pop‑ups  ----------------------- */
  popupErreur.querySelector('.btn-noir').onclick = () => popupErreur.style.display = 'none';
  popupCote.querySelector('.btn-noir').onclick   = () => popupCote.style.display = 'none';
  window.onclick = ev => {
    if (ev.target === popupErreur) popupErreur.style.display = 'none';
    if (ev.target === popupCote)   popupCote.style.display = 'none';
  };

  /* --------------------  Init ----------------- */
  initSelections();

});
</script>



<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    const suivantButton = document.getElementById('btn-suivant');
    const erreurPopup = document.getElementById('erreur-popup');
    const closeErreurBtn = erreurPopup.querySelector('.btn-noir');

    const userId = document.body.getAttribute('data-user-id');
    if (!userId) {
      console.error("ID utilisateur non trouvé.");
      return;
    }

    const sessionKey = `allSelectedOptions_${userId}`;
    let allSelectedOptions = JSON.parse(sessionStorage.getItem(sessionKey)) || [];

    if (!Array.isArray(allSelectedOptions)) {
      allSelectedOptions = [];
    }

    // Met à jour le prix total affiché
    function updateTotal() {
      const total = allSelectedOptions.reduce((sum, opt) => sum + (opt.price || 0) * (opt.quantity || 1), 0);
      const totalElement = document.querySelector(".footer p span");
      if (totalElement) totalElement.textContent = `${total.toFixed(2)} €`;
    }

    updateTotal(); // mise à jour au chargement

    // Bouton suivant
suivantButton.addEventListener('click', (e) => {
  e.preventDefault();

  // >>> AJOUTE ce test pour ne pas soumettre si les accoudoirs ne sont pas choisis
  const gauche = document.getElementById('input-acc-gauche').value;
  const droit  = document.getElementById('input-acc-droit').value;
  if (!gauche && !droit) return;   // on laisse le premier script gérer le popup

  // ta logique allSelectedOptions éventuelle...
  form.submit();
});

    // Fermer le popup erreur
    closeErreurBtn.addEventListener('click', () => {
      erreurPopup.style.display = 'none';
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
        window.location.href = "etape5-bois-accoudoir.php";
      }
    </script>

    
    <!-- FIL ARIANE -->
    <script>
   document.addEventListener('DOMContentLoaded', () => {
  const filAriane = document.querySelector('.fil-ariane');
  const links = filAriane.querySelectorAll('a');

  const filArianePopup = document.getElementById('filariane-popup');
  const closeFilArianePopupBtn = filArianePopup.querySelector('.btn-noir');

  const etapes = [
    { id: 'etape1-1-structure.php', key: null }, // toujours accessible
    { id: 'etape1-2-dimension.php', key: null },
    { id: 'etape2-type-banquette.php', key: null },
    { id: 'etape3-bois-couleur.php', key: null },
    { id: 'etape4-bois-decoration.php', key: null },
    { id: 'etape5-bois-accoudoir.php', key: null },
    { id: 'etape6-bois-dossier.php', key: 'etape6_valide' },
    { id: 'etape7-1-bois-tissu.php', key: 'etape7_valide' },
    { id: 'etape8-bois-mousse.php', key: 'etape8_valide' },
  ];


 links.forEach((link, index) => {
    const etape = etapes[index];

    // Empêche de cliquer si l'étape n’est pas validée
    if (etape.key && sessionStorage.getItem(etape.key) !== 'true') {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        filArianePopup.style.display = 'flex';
      });
      link.classList.add('disabled-link');
    }
  });

  // Fermer le popup avec le bouton
  closeFilArianePopupBtn.addEventListener('click', () => {
    filArianePopup.style.display = 'none';
  });

  // Fermer si on clique en dehors du contenu
  window.addEventListener('click', (event) => {
    if (event.target === filArianePopup) {
      filArianePopup.style.display = 'none';
    }
  });
});

    </script>


  </main>

  <?php require_once '../../squelette/footer.php' ?>
</body>


</html>