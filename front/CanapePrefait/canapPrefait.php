<?php
require '../../admin/config.php';

// Récupère la commande pré-faite (exemple avec l'ID 1, adapte-le dynamiquement si besoin)
$id_commande_prefait = 1;

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
  'id_nb_accoudoir' => 'nb_accoudoir'
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
  <title>Salon Kénitra</title>
</head>

<body>


  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>


  <main>


    <div class="container">
      <!-- Colonne de gauche -->
      <div class="left-column">
        <h2 class="h2">Composition du Salon Kénitra </h2>
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
          <br>

          <!-- Afficher la composition des autres éléments -->
          <?php if (!empty($composition)): ?>
            <?php foreach ($composition as $nomTable => $details): ?>
              <div class="option">
                <?php if (!empty($details['img'])): ?>
                  <img src="../../admin/uploads/<?php echo htmlspecialchars($nomTable) . '/' . htmlspecialchars($details['img']); ?>"
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


        <div class="footer">
          <p>Total : <span id="total-price"><?php echo number_format($totalPrice, 2, ',', ' '); ?> €</span></p>
          <div class="buttons">
            <button type="button" id="btn-retour" class="btn-beige" onclick="history.go(-1)">Retour</button>
            <form method="POST" action="">
              <input type="hidden" name="couleur_bois_id" id="selected-couleur_bois">
              <button type="submit" id="btn-suivant" class="btn-noir">Suivant</button>
            </form>
          </div>
        </div>
      </div>


      <!-- Colonne de droite -->
      <div class="right-column h2 ">
        <section class="main-display2">
          <img src="../../medias/meknes.png" alt="Armoire" class="">
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
        event.preventDefault(); // Empêche l'envoi du formulaire si ce n'est pas nécessaire
        window.location.href = "choix-dimension.php"; // Remplace par l’URL correcte
      });
    </script>


</body>

</html>