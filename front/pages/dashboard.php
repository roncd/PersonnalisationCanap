<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../formulaire/Connexion.php"); // Redirection vers la page de connexion
    exit();
}


$id_client = $_SESSION['user_id'];
$sql = "SELECT cp.*, 
               tb.nom AS type_nom, 
               s.nom AS structure_nom
        FROM commande_prefait cp
        LEFT JOIN type_banquette tb ON cp.id_banquette = tb.id
        LEFT JOIN structure s ON cp.id_structure = s.id
        ORDER BY cp.id DESC
        LIMIT 3";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "
    SELECT vente_produit.*, categorie.nom AS nom_categorie 
    FROM vente_produit 
    JOIN categorie ON vente_produit.id_categorie = categorie.id
";
$stmt = $pdo->query($sql);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);



if (isset($_GET['reset']) && $_GET['reset'] == 1) {
  $stmt = $pdo->prepare("DELETE FROM commande_temporaire WHERE id_client = ?");
  $stmt->execute([$id_client]);
}

$stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ?");
$stmt->execute([$id_client]);
$existing_order = $stmt->fetch(PDO::FETCH_ASSOC);

//commande existante
$show_commencer = !$existing_order;


function calculPrix($commande, &$composition = [])
{
  global $pdo;

  $composition = [];
  $totalPrice = 0;
  $id_commande = $commande['id'];

  // Liste des éléments simples
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
      $detail = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($detail) {
        $composition[$table] = $detail;
        if (!empty($detail['prix'])) {
          $totalPrice += floatval($detail['prix']);
        }
      }
    }
  }

  // Accoudoirs multiples (bois)
  $stmt = $pdo->prepare("SELECT ab.*, cpa.nb_accoudoir
                           FROM commande_prefait_accoudoir cpa
                           JOIN accoudoir_bois ab ON cpa.id_accoudoir_bois = ab.id
                           WHERE cpa.id_commande_prefait = ?");
  $stmt->execute([$id_commande]);
  $accoudoirs = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if ($accoudoirs) {
    $composition['accoudoirs_bois_multiples'] = $accoudoirs;
    foreach ($accoudoirs as $acc) {
      if (!empty($acc['prix']) && !empty($acc['nb_accoudoir'])) {
        $totalPrice += floatval($acc['prix']) * intval($acc['nb_accoudoir']);
      }
    }
  }

  // 💰 Prix par centimètre (350 € / mètre = 3.5 € / cm)
  $prixParCm = 3.5;

  foreach (['longueurA', 'longueurB', 'longueurC'] as $longueur) {
    if (!empty($commande[$longueur])) {
      $totalPrice += floatval($commande[$longueur]) * $prixParCm;
    }
  }

  // 💡 Bonus : traitement spécifique de certains éléments (optionnel)
  if (!empty($composition)) {
    foreach ($composition as $nomTable => $details) {
      if ($nomTable === 'accoudoirs_bois_multiples') continue; // déjà traité
      if ($nomTable === 'accoudoir_tissu') {
        if (!empty($details['prix'])) {
          $totalPrice += floatval($details['prix']); // tu peux multiplier par 2 si besoin
        }
      }
    }
  }

  return $totalPrice;
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tableau de bord</title>
  <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/processus.css">
  <link rel="stylesheet" href="../../styles/dashboard.css">
  <link rel="stylesheet" href="../../styles/popup.css">
  <link rel="stylesheet" href="../../styles/accueil.css">
  <link rel="stylesheet" href="../../styles/buttons.css">
  <script type="module" src="../../script/popup.js"></script>
  <link rel="stylesheet" href="../../styles/transition.css">
  <script type="module" src="../../script/transition.js"></script>
  <script type="module" src="../../script/animate-value.js"></script>



</head>

<body>
  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>
  <main>
    <div class="dashboard">
      <!-- Section avec image de fond et texte superposé -->
      <section class="hero-section">
        <div class="hero-container">
        <img src="../../medias/hero-banner.jpg" alt="Salon marocain" class="hero-image">
          <div class="hero-content">
            <h1 class="hero-title">
              Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
            </h1>
            <p class="hero-description">
              Teste notre <strong>configurateur de canapé, </strong> et imagine le meuble qui répond le plus à tes goûts, et à l’aménagement de ton salon !
            </p>
            <?php if ($show_commencer): ?>
              <form action="../EtapesPersonnalisation/etape1-1-structure.php" method="get">
                <button type="submit" class="btn-noir">Commencer la personnalisation</button>
              </form>
            <?php else: ?>
              <div class="boutons-container">
                <form action="../EtapesPersonnalisation/etape1-1-structure.php" method="get">
                  <button type="submit" class="btn-beige">Reprendre la personnalisation</button>
                </form>
                <form>
                  <button
                    type="button" id="btn-abandonner"
                    class="btn-noir"
                    data-url="../EtapesPersonnalisation/etape1-1-structure.php">
                    Nouvelle personnalisation
                  </button>
                </form>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </section>


      </section>

    <!-- SECTION PERSONNALISATION -->
    <section class="customize-section transition-all">
      <div class="customize-text">
        <h2>Crée toi-même ton canapé marocain idéal</h2>
        <br>
        <br>
        <ul class="customize-features">
          <li><img src="../../medias/canape_icon.png" alt="Forme" class="feature-icon">Choisis la structure du canapé</li>
          <li><img src="../../medias/couleurs_icon.png" alt="Couleurs" class="feature-icon">Sélectionne les couleurs & matières</li>
          <li><img src="../../medias/coussin_icon.png" alt="Coussins" class="feature-icon">Ajoutez tes options préférés</li>
          <li><img src="../../medias/artiste_icon.png" alt="Aperçu" class="feature-icon">Vue d'ensemble de ta création</li>
        </ul>
      </div>
      <div class="customize-image">
        <!-- Blob de fond (SVG ou PNG) 
        <img class="blob" src="../../medias/blob.png" alt="forme décorative">-->
          <!-- Image du canapé (taille réduite, devant le blob)
        <img class="sofa" src="../../medias/sofa.png" alt="Canapé personnalisé"> -->
          <img class="sofa" src="../../medias/sofablob.png" alt="Canapé personnalisé">
        </div>
      </section>

<!-- ------------------- SECTION COMBINAISONS ------------------- -->
<section class="combination-section transition-all">
  <h2>Choisis une combinaison à personnaliser</h2>
  <div class="combination-container">

          <?php foreach ($commandes as $commande): ?>
            <?php
            $composition = [];
            $prixDynamique = calculPrix($commande, $composition);
            ?>
            <div class="product-card">
              <div class="product-image">
                <img
                  src="../../admin/uploads/canape-prefait/<?php echo htmlspecialchars($commande['img'] ?? 'default.jpg', ENT_QUOTES); ?>"
                  alt="<?php echo htmlspecialchars($commande['nom'] ?? 'Canapé pré-fait', ENT_QUOTES); ?>">
              </div>
              <div class="product-content">
                <h3><?= htmlspecialchars($commande['nom']) ?></h3>
                <p class="description">Type : <?= htmlspecialchars($commande['type_nom']) ?></p>
                <p class="description">Structure : <?= htmlspecialchars($commande['structure_nom'] ?? 'Non défini') ?></p>
                <p class="price"><?= number_format($prixDynamique, 2, ',', ' ') ?> €</p>
                <button class="btn-beige"
                  onclick="window.location.href = '../CanapePrefait/canapPrefait.php?id=<?= (int)$commande['id']; ?>'">
                  Personnaliser
                </button>
              </div>
            </div>
          <?php endforeach; ?>

        </div>
      </section>


      <div class="voir-plus">
        <button class="btn-noir"
          onclick="window.location.href = 'noscanapes.php';">
          Voir plus +
        </button>
      </div>

<section class="avantages-card transition-boom">
  <div class="avantages-text">
    <h2>Pourquoi personnaliser ton canapé ici ?</h2>
    <ul>
      <li>Des modèles uniques</li>
      <li>Produits faits main, sur mesure</li>
      <li>Livraison rapide et soignée</li>
      <li>Paiement sécurisé</li>
    </ul>
  </div>
  <div class="avantages-img">
    <img src="../../medias/accueil-whyhere.jpg" alt="Aperçu canapé personnalisé">
  </div>
</section>



<!-- ------------------- SECTION ARTICLES ASSOCIES ------------------- -->
<section class="combination-section transition-all">
  <h2>Ces articles peuvent aussi t'intéresser</h2>
  <div class="combination-container">

          <?php foreach ($produits as $produit): ?>
            <div class="product-card">
              <div class="product-image">
                <img
                  src="../../admin/uploads/autres-produits/<?php echo htmlspecialchars($produit['img'] ?? 'default.jpg', ENT_QUOTES); ?>"
                  alt="<?php echo htmlspecialchars($produit['nom'], ENT_QUOTES); ?>">
              </div>
              <div class="product-content">
                <h3><?php echo htmlspecialchars($produit['nom'], ENT_QUOTES); ?></h3>
                <p class="description">Catégorie : <?php echo htmlspecialchars($produit['nom_categorie'], ENT_QUOTES); ?></p>
                <p class="price"><?php echo number_format($produit['prix'], 2, ',', ' '); ?> €</p>
                <button class="btn-beige"
                  onclick="window.location.href = 'ficheProduit.php?id=<?php echo (int)$produit['id']; ?>'">
                  Voir plus
                </button>
              </div>
            </div>
          <?php endforeach; ?>

        </div>
      </section>


      <div class="voir-plus">
        <button class="btn-noir"
          onclick="window.location.href = 'nosproduits.php';">
          Voir plus +
        </button>
      </div>

<section class="stats-section transition-boom">
  <h2 class="h2-center">Ils nous font confiance</h2>
  <ul class="stats-list">
    <li><strong data-target="500" data-plus="true">0</strong> canapés personnalisés</li>
    <li><strong data-target="4.8" data-decimal="true">0/5</strong> de satisfaction client</li>
    <li><strong data-target="17" data-plus="true">0</strong> ans d’expérience depuis 2006</li>
  </ul>
</section>

    </div>
    <!-- POPUP ABANDONNER -->
    <div id="abandonner-popup" class="popup">
      <div class="popup-content">
        <h2>Êtes-vous sûr ?</h2>
        <p>Si vous commencez votre une nouvelle personnalisation,</p>
        <p>l'ancienne sera supprimer définitevement.</p>
        <br>
        <button id="yes-btn" class="btn-beige">Oui</button>
        <button id="no-btn" class="btn-noir">Non</button>
      </div>
    </div>
  </main>
</body>

<footer>
  <?php require '../../squelette/footer.php'; ?>
</footer>

</html>