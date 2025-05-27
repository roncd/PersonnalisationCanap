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
        <h2 class="h2">Choisi tes dimensions </h2>
        
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
        </form>

        <div class="footer">
          <p>Total : <span id="total-price"><?php echo number_format($totalPrice, 2, ',', ' '); ?> €</span></p>
          <div class="buttons">
            <button class="btn-retour " onclick="history.go(-1)">Retour</button>
            <form method="POST" action="">
              <input type="hidden" name="couleur_bois_id" id="selected-couleur_bois">
              <button type="submit" class="btn-suivant">Suivant</button>
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
    <div id="help-popup" class="popup transition">
      <div class="popup-content">
        <h2>Vous avez une question ?</h2>
        <p>Contactez nous au numéro suivant et un vendeur vous assistera :
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
        <h2>Êtes vous sûr de vouloir abandonner ?</h2>
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
  </main>

    
<script>
  document.querySelector(".btn-suivant").addEventListener("click", function(event) {
    event.preventDefault(); // Empêche l'envoi du formulaire si ce n'est pas nécessaire
    window.location.href = "choix-mousse.php"; // Remplace par l’URL correcte
  });
</script>



  <?php require_once '../../squelette/footer.php'; ?>


</body>

</html>