<?php
require '../../admin/config.php';

// Récupère la commande pré-faite (exemple avec l'ID 1, adapte-le dynamiquement si besoin)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("ID invalide ou non fourni.");
}
$id_commande_prefait = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT * FROM commande_prefait WHERE id = ?");
$stmt->execute([$id_commande_prefait]);
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$commande) {
  die("Aucune commande pré-faite trouvée.");
}

// Initialisation du tableau de composition
$composition = [];
$totalPrice = 0;

// Liste des éléments liés à une table pour récupérer les infos (nom, image, prix)

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

  // Récupération des accoudoirs multiples
  $stmt = $pdo->prepare("SELECT ab.id, ab.nom, ab.img, ab.prix, cpa.nb_accoudoir
                       FROM commande_prefait_accoudoir cpa
                       JOIN accoudoir_bois ab ON cpa.id_accoudoir_bois = ab.id
                       WHERE cpa.id_commande_prefait = ?");
  $stmt->execute([$id_commande_prefait]);
  $accoudoirs = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // On les ajoute à la composition sous une clé spéciale (car il peut y en avoir plusieurs)
  if ($accoudoirs) {
    $composition['accoudoirs_bois_multiples'] = $accoudoirs;

    // On ajoute les prix au total
    foreach ($accoudoirs as $acc) {
      if (!empty($acc['prix']) && !empty($acc['nb_accoudoir'])) {
        $totalPrice += floatval($acc['prix']) * intval($acc['nb_accoudoir']);
      }
    }
  }
}

// Ajouter aussi le prix de dimensions (si pertinent)
$totalPrice += floatval($commande['prix_dimensions'] ?? 0);
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



  <title><?php echo htmlspecialchars($commande['nom']); ?></title>
</head>


<body>
  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>


  <main>
    <div class="container transition">
      <!-- Colonne de gauche -->
      <div class="left-column">
        <h2 class="h2">Composition du <?php echo htmlspecialchars($commande['nom']); ?></h2>
        <section class="color-options">
          <!-- Afficher Longueur A uniquement si elle a une valeur -->
          <?php if (!empty($commande['longueurA'])): ?>
            <div class="option">
              <p class="input-field"><strong>Longueur A :</strong> <?php echo htmlspecialchars($commande['longueurA']); ?> cm</p>
            </div>
          <?php endif; ?>

          <?php if (!empty($commande['longueurB'])): ?>
            <div class="option">
              <p class="input-field"><strong>Longueur B :</strong> <?php echo htmlspecialchars($commande['longueurB']); ?> cm</p>
            </div>
          <?php endif; ?>

          <?php if (!empty($commande['longueurC'])): ?>
            <div class="option">
              <p class="input-field"><strong>Longueur C :</strong> <?php echo htmlspecialchars($commande['longueurC']); ?> cm</p>
            </div>
          <?php endif; ?>

          <!-- Afficher la composition des autres éléments -->
          <?php if (!empty($composition)): ?>
            <?php foreach ($composition as $nomTable => $details): ?>

              <?php
              // Mapping entre nom de table et nom de dossier upload
              $dossierUploadMap = [
                'structure' => 'structure',
                'type_banquette' => 'banquette',
                'mousse' => 'mousse',
                'couleur_bois' => 'couleur-banquette-bois',
                'decoration' => 'decoration',
                'accoudoir_bois' => 'accoudoirs-bois',
                'dossier_bois' => 'dossier-bois',
                'couleur_tissu_bois' => 'couleur-tissu-bois',
                'motif_bois' => 'motif-bois',
                'modele' => 'modele',
                'couleur_tissu' => 'couleur-tissu-tissu',
                'motif_tissu' => 'motif-tissu',
                'dossier_tissu' => 'dossier-tissu',
                'accoudoir_tissu' => 'accoudoirs-tissu',
              ];

              $dossier = $dossierUploadMap[$nomTable] ?? $nomTable;
              ?>



              <?php if ($nomTable === 'accoudoirs_bois_multiples'): ?>
                <?php foreach ($details as $accoudoir): ?>
                  <div class="option">
                    <?php if (!empty($accoudoir['img'])): ?>
                      <img src="../../admin/uploads/accoudoirs-bois/<?php echo htmlspecialchars($accoudoir['img']); ?>"
                        alt="<?php echo htmlspecialchars($accoudoir['nom']); ?>">
                    <?php endif; ?>
                    <p><?php echo htmlspecialchars($accoudoir['nom']); ?></p>
                    <p>Quantité : <?php echo htmlspecialchars($accoudoir['nb_accoudoir']); ?></p>
                    <?php if (!empty($accoudoir['prix'])): ?>
                      <p><strong><?php echo htmlspecialchars($accoudoir['prix']); ?> €</strong></p>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>

              <?php elseif ($nomTable === 'accoudoir_tissu'): ?>
                <div class="option">
                  <?php if (!empty($details['img'])): ?>
                    <img src="../../admin/uploads/accoudoirs-tissu/<?php echo htmlspecialchars($details['img']); ?>"
                      alt="<?php echo htmlspecialchars($details['nom']); ?>">
                  <?php endif; ?>
                  <p><?php echo htmlspecialchars($details['nom']); ?></p>
                  <p>Quantité : 2</p>
                  <?php if (!empty($details['prix'])): ?>
                    <p><strong><?php echo htmlspecialchars($details['prix']); ?> € (unité)</strong></p>
                  <?php endif; ?>
                </div>

              <?php else: ?>
                <div class="option">
                  <?php if (!empty($details['img'])): ?>
                    <img src="../../admin/uploads/<?php echo htmlspecialchars($dossier); ?>/<?php echo htmlspecialchars($details['img']); ?>"
                      alt="<?php echo htmlspecialchars($details['nom'] ?? ''); ?>">
                  <?php endif; ?>
                  <p><?php echo htmlspecialchars($details['nom'] ?? $details['valeur'] ?? 'Non spécifié'); ?></p>
                  <?php if (!empty($details['prix'])): ?>
                    <p><strong><?php echo htmlspecialchars($details['prix']); ?> €</strong></p>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
        </section>
      <?php else: ?>
        <p>Aucune composition sélectionnée.</p>
      <?php endif; ?>
      </section>

      <?php
      $totalPrice = 0;

      // Prix par centimètre (350 € le mètre => 3.5 € le centimètre)
      $prixParCm = 3.5;

      // Ajouter le prix des longueurs A, B et C
      if (!empty($commande['longueurA'])) {
        $totalPrice += (float)$commande['longueurA'] * $prixParCm;
      }
      if (!empty($commande['longueurB'])) {
        $totalPrice += (float)$commande['longueurB'] * $prixParCm;
      }
      if (!empty($commande['longueurC'])) {
        $totalPrice += (float)$commande['longueurC'] * $prixParCm;
      }

      // Ajouter le prix des éléments de composition
      if (!empty($composition)) {
        foreach ($composition as $nomTable => $details) {
          if ($nomTable === 'accoudoirs_bois_multiples') {
            foreach ($details as $accoudoir) {
              if (!empty($accoudoir['prix'])) {
                $totalPrice += (float)$accoudoir['prix'];
              }
            }
          } elseif ($nomTable === 'accoudoir_tissu') {
            if (!empty($details['prix'])) {
              $totalPrice += (float)$details['prix'] * 2;
            }
          } else {
            if (!empty($details['prix'])) {
              $totalPrice += (float)$details['prix'];
            }
          }
        }
      }



      ?>

      <div class="footer">
        <p>Total : <span id="total-price"><?php echo number_format($totalPrice, 2, ',', ' '); ?> €</span></p>
        <div class="buttons">
          <button onclick="retourEtapePrecedente()" class="btn-beige">Retour</button>
          <form method="POST" action="">
            <input type="hidden" name="couleur_bois_id" id="selected-couleur_bois">
            <button type="submit" id="btn-suivant" class="btn-noir">Personnaliser</button>
          </form>
        </div>
      </div>
      </div>



      <!-- Colonne de droite -->
      <div class="right-column h2 ">
        <section class="main-display">
          <div class="buttons">
            <button id="btn-aide" class="btn-beige">Besoin d'aide ?</button>
          </div>
          <br>
          <?php
          $imgFile = !empty($commande['img']) ? htmlspecialchars($commande['img'], ENT_QUOTES) : 'default.jpg';
          ?>
          <img
            src="../../admin/uploads/canape-prefait/<?php echo $imgFile; ?>"
            alt="<?php echo htmlspecialchars($commande['nom'] ?? 'Canapé pré-fait', ENT_QUOTES); ?>"
            class="primary-img">
        </section>
      </div>
    </div>

    <!-- POPUP BESOIN D'AIDE -->
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

    <!-- POPUP ABANDONNER -->
    <div id="abandonner-popup" class="popup ">
      <div class="popup-content">
        <h2>Êtes vous sûr de vouloir abandonner ?</h2>
        <br>
        <button class="btn-beige">Oui...</button>
        <button class="btn-noir">Non !</button>
      </div>
    </div>


    <?php require_once '../../squelette/footer.php'; ?>


    <script>
      document.getElementById("btn-suivant").addEventListener("click", function(event) {
        event.preventDefault();
        const id = <?php echo json_encode($id_commande_prefait); ?>;
        window.location.href = "choix-dimension.php?id=" + id;
      });
    </script>


    <!-- BOUTTON RETOUR -->
    <script>
      function retourEtapePrecedente() {
        // Exemple : tu es sur étape 8, tu veux revenir à étape 7
        window.location.href = "../pages/noscanapes.php";
      }
    </script>


</body>

</html>