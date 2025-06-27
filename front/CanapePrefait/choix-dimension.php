<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
  $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
  header("Location: ../formulaire/Connexion.php");
  exit;
}

// Vérifier que l'ID est valide
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("ID invalide ou manquant.");
}

$id_commande = intval($_GET['id']);

// Récupérer les données de la commande pré-faite
$stmt = $pdo->prepare("SELECT * FROM commande_prefait WHERE id = ?");
$stmt->execute([$id_commande]);
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$commande) {
  die("Aucune commande pré-faite trouvée.");
}

// Vérifier s'il existe une commande temporaire pour cet utilisateur
$stmt = $pdo->prepare("SELECT * FROM commande_temporaire WHERE id_client = ? AND id_structure = ?");
$stmt->execute([$_SESSION['user_id'], $commande['id_structure']]);
$commandeTemp = $stmt->fetch(PDO::FETCH_ASSOC);

// Si POST : mettre à jour les dimensions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $longueurA = $_POST['longueurA'] ?? null;
  $longueurB = $_POST['longueurB'] ?? null;
  $longueurC = $_POST['longueurC'] ?? null;

  // Si la commande temporaire existe, mettre à jour
  if ($commandeTemp) {
    $stmt = $pdo->prepare("UPDATE commande_temporaire SET longueurA = ?, longueurB = ?, longueurC = ? WHERE id_client = ? AND id_structure = ?  = ? AND id_commande_prefait = ?");
    $stmt->execute([$longueurA, $longueurB, $longueurC, $_SESSION['user_id'], $commande['id_structure']]);
  }

  // Recharger les nouvelles valeurs depuis la BDD
  $stmt = $pdo->prepare("SELECT * FROM commande_temporaire WHERE id_client = ? AND id_structure = ? AND id_commande_prefait = ?");
  $stmt->execute([$_SESSION['user_id'], $commande['id_structure']]);
  $commandeTemp = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Utiliser les dimensions actuelles
$longueurA = $commandeTemp['longueurA'] ?? $commande['longueurA'];
$longueurB = $commandeTemp['longueurB'] ?? $commande['longueurB'];
$longueurC = $commandeTemp['longueurC'] ?? $commande['longueurC'];

$prixDimensions = $commandeTemp['prix_dimensions'] ?? $commande['prix_dimensions'];

// Initialisation
$composition = [];
$totalPrice = 0;

// Récupérer les éléments liés
$elements = [
  'id_structure' => 'structure',
  'id_banquette' => 'type_banquette',
  'id_mousse' => 'mousse',
  'id_couleur_bois' => 'couleur_bois',
  'id_decoration' => 'decoration',
  'id_accoudoir_bois' => 'accoudoir_bois',
  'id_dossier_bois' => 'dossier_bois',
  'id_couleur_tissu_bois' => 'couleur_tissu_bois',
  'id_motif_bois' => 'motif_bois',
  'id_modele' => 'modele',
  'id_couleur_tissu' => 'couleur_tissu',
  'id_motif_tissu' => 'motif_tissu',
  'id_dossier_tissu' => 'dossier_tissu',
  'id_accoudoir_tissu' => 'accoudoir_tissu',
];

foreach ($elements as $colonne => $table) {
  if (!empty($commande[$colonne])) {
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
    $stmt->execute([$commande[$colonne]]);
    $composition[$table] = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($composition[$table]['prix'])) {
      $totalPrice += floatval($composition[$table]['prix']);
    }
  }
}

$nbLongueurs = isset($composition['structure']['nb_longueurs']) ? (int) $composition['structure']['nb_longueurs'] : 0;

// Ajouter aussi le prix des dimensions
$totalPrice += floatval($prixDimensions);

$stmt = $pdo->prepare("SELECT ab.id, ab.nom, ab.img, ab.prix, cpa.nb_accoudoir
                       FROM commande_prefait_accoudoir cpa
                       JOIN accoudoir_bois ab ON cpa.id_accoudoir_bois = ab.id
                       WHERE cpa.id_commande_prefait = ?");
$stmt->execute([$id_commande]);
$accoudoirs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($accoudoirs) {
  $composition['accoudoirs_bois_multiples'] = $accoudoirs;

  // On les ajoute au prix total
  foreach ($accoudoirs as $acc) {
    if (!empty($acc['prix']) && !empty($acc['nb_accoudoir'])) {
      $totalPrice += floatval($acc['prix']) * intval($acc['nb_accoudoir']);
    }
  }
}


// Empêcher le cache navigateur
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>




<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/popup.css">
  <link rel="stylesheet" href="../../styles/canapPrefait.css">
  <link rel="stylesheet" href="../../styles/buttons.css">
  <script type="module" src="../../script/popup.js"></script>
  <script type="module" src="../../script/keydown.js"></script>



  <title>Choisi tes dimensions</title>
</head>

<body>
  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <main>
    
      <div class="fil-ariane-container h2 " aria-label="fil-ariane">
          <ul class="fil-ariane">
            <?php
            $id = $_GET['id'] ?? null;
            ?>
            <li><a href="./choix-dimension.php?id=<?= $id ?>" class="active">Dimension</a></li>
            <li><a href="./choix-mousse.php?id=<?= $id ?>" >Mousse</a></li>
          </ul>
        </div>
<div style="margin-bottom: 60px !important;"></div>    <div class="container transition">
      
    <div class="container transition">
      <!-- Colonne de gauche -->
      <div class="left-column">
        <h2>Choisi tes dimensions</h2>
        <p>Largeur banquette : <span class="bold">50cm (par défaut)</span> | Prix total des dimensions : <span id="dimension-price"><?= number_format($prixDimensions, 2, ',', ' ') ?> €</span></p>

        <!-- Formulaire avec inputs longueur -->
        <form method="POST" class="formulaire">
          <?php if ($nbLongueurs >= 1): ?>
            <div class="form-row">
              <div class="form-group">
                <label for="longueurA">Longueur banquette A (en cm) :</label>
                <input type="number" autocomplete="off" id="longueurA" name="longueurA" class="input-field" placeholder="Ex: 150" value="<?= htmlspecialchars($longueurA) ?>">
              </div>
            </div>
          <?php endif; ?>

          <?php if ($nbLongueurs >= 2): ?>
            <div class="form-row">
              <div class="form-group">
                <label for="longueurB">Longueur banquette B (en cm) :</label>
                <input type="number" autocomplete="off" id="longueurB" name="longueurB" class="input-field" placeholder="Ex: 350" value="<?= htmlspecialchars($longueurB) ?>">
              </div>
            </div>
          <?php endif; ?>

          <?php if ($nbLongueurs >= 3): ?>
            <div class="form-row">
              <div class="form-group">
                <label for="longueurC">Longueur banquette C (en cm) :</label>
                <input type="number" autocomplete="off" id="longueurC" name="longueurC" class="input-field" placeholder="Ex: 350" value="<?= htmlspecialchars($longueurC) ?>">
              </div>
            </div>
          <?php endif; ?>
        </form>
        <?php
        // Prix par centimètre (350 € le mètre => 3.5 € le centimètre)
        $prixParCm = 3.5;

        // Calcul des longueurs depuis la commande
        $longueurA = isset($commande['longueurA']) ? (float)$commande['longueurA'] : 0;
        $longueurB = isset($commande['longueurB']) ? (float)$commande['longueurB'] : 0;
        $longueurC = isset($commande['longueurC']) ? (float)$commande['longueurC'] : 0;

        // Calcul prix dimensions
        $prixDimensions = ($longueurA + $longueurB + $longueurC) * $prixParCm;

        // Calcul prix options
        $totalOptions = 0;
        if (!empty($composition)) {
          foreach ($composition as $nomTable => $details) {
            // Accoudoirs en bois multiples (liste de plusieurs objets)
            if ($nomTable === 'accoudoirs_bois_multiples' && is_array($details)) {
              foreach ($details as $acc) {
                if (!empty($acc['prix']) && !empty($acc['nb_accoudoir'])) {
                  $totalOptions += (float)$acc['prix'] * (int)$acc['nb_accoudoir'];
                }
              }
            }
            // Autres options
            elseif (!empty($details['prix'])) {
              $prix = (float)$details['prix'];

              // Si c’est un accoudoir tissu, on double le prix
              if ($nomTable === 'accoudoir_tissu') {
                $prix *= 2;
              }

              $totalOptions += $prix;
            }
          }
        }

        // Total final
        $totalPrix = $totalOptions + $prixDimensions;
        ?>



        <div class="footer">
          <p>Total : <span id="total-price"><?= number_format($totalPrix, 2, ',', ' ') ?> €</span></p>
          <div class="buttons">
            <button onclick="retourEtapePrecedente()" class="btn-beige">Retour</button>
            <form method="POST" action="creer_commande_temporaire.php">
              <input type="hidden" name="id_commande_prefait" value="<?= htmlspecialchars($id_commande) ?>"> <!-- Ajout de l'ID -->
              <input type="hidden" name="longueurA" value="" id="input-longueurA">
              <input type="hidden" name="longueurB" value="" id="input-longueurB">
              <input type="hidden" name="longueurC" value="" id="input-longueurC">
              <input type="hidden" name="prix_dimensions" value="" id="input-prix-dimensions">
              <button type="submit" class="btn-noir">Suivant</button>
            </form>
          </div>
        </div>
      </div>

      <script>
        const form = document.querySelector('form[action="creer_commande_temporaire.php"]');
        form.addEventListener('submit', function() {
          document.getElementById('input-longueurA').value = longueurAInput ? longueurAInput.value : 0;
          document.getElementById('input-longueurB').value = longueurBInput ? longueurBInput.value : 0;
          document.getElementById('input-longueurC').value = longueurCInput ? longueurCInput.value : 0;

          const prixDimensions = ((+longueurAInput?.value || 0) + (+longueurBInput?.value || 0) + (+longueurCInput?.value || 0)) * prixParCm;

          // Récupérer moussePrix localStorage
          const moussePrix = parseFloat(localStorage.getItem('moussePrix') || '0');

          const total = totalOptions + prixDimensions + moussePrix;

          document.getElementById('input-prix-dimensions').value = prixDimensions.toFixed(2);

          // Sauvegarde dans localStorage
          localStorage.setItem('prix_total_jusqua_dimensions', total.toFixed(2));
        });
      </script>


      <script>
        const prixParCm = <?= json_encode($prixParCm); ?>;
        const totalOptions = <?= json_encode($totalOptions); ?>;

        const longueurAInput = document.getElementById('longueurA');
        const longueurBInput = document.getElementById('longueurB');
        const longueurCInput = document.getElementById('longueurC');

        const dimensionPriceSpan = document.getElementById('dimension-price');
        const totalPriceSpan = document.getElementById('total-price');

        function calculePrix() {
          const longueurA = Number(longueurAInput ? longueurAInput.value : 0);
          const longueurB = Number(longueurBInput ? longueurBInput.value : 0);
          const longueurC = Number(longueurCInput ? longueurCInput.value : 0);

          const prixDimensions = (longueurA + longueurB + longueurC) * prixParCm;
          dimensionPriceSpan.textContent = prixDimensions.toFixed(2).replace('.', ',') + ' €';

          // Récupérer le prix mousse depuis localStorage (ou 0 si absent)
          const moussePrix = parseFloat(localStorage.getItem('moussePrix') || '0');

          // Ajouter moussePrix au total
          const total = totalOptions + prixDimensions + moussePrix;
          totalPriceSpan.textContent = total.toFixed(2).replace('.', ',') + ' €';
        }


        [longueurAInput, longueurBInput, longueurCInput].forEach(input => {
          if (input) {
            input.addEventListener('input', calculePrix);
            input.addEventListener('change', calculePrix);
          }
        });

        window.addEventListener('DOMContentLoaded', calculePrix);
      </script>


      <!-- Colonne de droite -->
      <div class="right-column ">
        <section class="main-display2">

          <div class="buttons">
            <button id="btn-aide" class="btn-beige">Besoin d'aide ?</button>
          </div>
          <br>
          <?php if (!empty($composition['structure']['img'])): ?>
            <img
              src="../../admin/uploads/structure/<?php echo htmlspecialchars($composition['structure']['img'], ENT_QUOTES); ?>"
              alt="Structure du canapé"
              class="primary-img">
          <?php endif; ?>
        </section>
      </div>
    </div>


    <!-- Popup besoin d'aide -->
    <div id="help-popup" class="popup">
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
    <div id="abandonner-popup" class="popup">
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


  <script>
    document.querySelector(".btn-suivant").addEventListener("click", function(event) {
      event.preventDefault(); // Empêche l'envoi du formulaire si ce n'est pas nécessaire
      window.location.href = "choix-mousse.php"; // Remplace par l’URL correcte
    });
  </script>

  <!-- BOUTTON RETOUR -->
  <script>
    function retourEtapePrecedente() {
      const id = new URLSearchParams(window.location.search).get('id');
      if (id) {
        window.location.href = `canapPrefait.php?id=${id}`;
      } else {
        alert("ID introuvable dans l'URL.");
      }
    }
  </script>



  <?php require_once '../../squelette/footer.php'; ?>


</body>

</html>