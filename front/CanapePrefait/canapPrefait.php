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
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/processus.css">
  <link rel="stylesheet" href="../../styles/popup.css">
  <link rel="stylesheet" href="../../styles/canapPrefait.css">
  <link rel="stylesheet" href="../../styles/buttons.css">

  <title><?php echo htmlspecialchars($commande['nom']); ?></title>
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
        <h2 class="h2">Composition du <?php echo htmlspecialchars($commande['nom']); ?></h2>
        <section class="color-options">
          <!-- Afficher Longueur A uniquement si elle a une valeur -->
          <?php if (!empty($commande['longueurA'])): ?>
            <div class="option">
              <p><strong>Longueur A :</strong> <?php echo htmlspecialchars($commande['longueurA']); ?> cm</p>
            </div>
          <?php endif; ?>

          <?php if (!empty($commande['longueurB'])): ?>
            <div class="option">
              <p><strong>Longueur B :</strong> <?php echo htmlspecialchars($commande['longueurB']); ?> cm</p>
            </div>
          <?php endif; ?>

          <?php if (!empty($commande['longueurC'])): ?>
            <div class="option">
              <p><strong>Longueur C :</strong> <?php echo htmlspecialchars($commande['longueurC']); ?> cm</p>
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

              <div class="option">
                <?php if (!empty($details['img'])): ?>
                    <img src="../../admin/uploads/<?php echo htmlspecialchars($dossier) . '/' . htmlspecialchars($details['img']); ?>"
                    alt="<?php echo htmlspecialchars($details['nom'] ?? ''); ?>"
                    data-id="<?php echo htmlspecialchars($details['id'] ?? ''); ?>">
                <?php endif; ?>
                <p><?php echo htmlspecialchars($details['nom'] ?? $details['valeur'] ?? 'Non spécifié'); ?></p>
                <?php if (!empty($details['prix'])): ?>
                  <p><strong><?php echo htmlspecialchars($details['prix']); ?> €</strong></p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
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
    foreach ($composition as $details) {
        if (!empty($details['prix'])) {
            $totalPrice += (float)$details['prix'];
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
        <section class="main-display2">
<?php
$imgFile = !empty($commande['img']) ? htmlspecialchars($commande['img'], ENT_QUOTES) : 'default.jpg';
?>
<img 
  src="../../admin/uploads/canape-prefait/<?php echo $imgFile; ?>" 
  alt="<?php echo htmlspecialchars($commande['nom'] ?? 'Canapé préfait', ENT_QUOTES); ?>" 
  class="primary-img"
>
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