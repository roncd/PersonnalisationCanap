<?php
require '../../admin/config.php';
session_start();


// 1. Canapés préfaits
$sqlCommandes = "
    SELECT cp.*, tb.nom AS type_nom 
    FROM commande_prefait cp
    LEFT JOIN type_banquette tb ON cp.id_banquette = tb.id
";
$stmtCommandes = $pdo->prepare($sqlCommandes);
$stmtCommandes->execute();
$commandes = $stmtCommandes->fetchAll(PDO::FETCH_ASSOC);

// 2. Articles à l’unité (vente_produit)
$sqlProduits = "
    SELECT vente_produit.*, categorie.nom AS nom_categorie 
    FROM vente_produit 
    JOIN categorie ON vente_produit.id_categorie = categorie.id
    ORDER BY vente_produit.id DESC
";
$stmtProduits = $pdo->prepare($sqlProduits);
$stmtProduits->execute();
$produits = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);

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
  <title>Accueil</title>
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
  <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
  <link rel="stylesheet" href="../../styles/styles.css">
  <link rel="stylesheet" href="../../styles/buttons.css">
  <link rel="stylesheet" href="../../styles/accueil.css">
  <link rel="stylesheet" href="../../styles/transition.css">
  <script type="module" src="../../script/transition.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="../../node_modules/@preline/carousel/index.js"></script>
  <script type="module" src="../../script/animate-value.js"></script>
</head>

<body>
  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <!-- Main -->
  <main>

    <section class="hero-section">
      <div class="hero-container">
        <img src="../../medias/salon-marocain.jpg" alt="Salon marocain" class="hero-image">
        <div class="hero-content">
          <br><br><br>
          <h1 class="hero-title h2">
            Personnalisez votre salon marocain
          </h1>

          <p class="hero-description">
            Laissez-vous tenter et personnalisez votre salon de A à Z !
            Du canapé à la table, choisissez les configurations qui vous plaisent le plus.
            La couleur, le tissu, la forme... faites ce qui vous ressemble pour un prix raisonnable.
          </p>
          <a href="dashboard.php">
            <button class="btn-noir">PERSONNALISER</button>
          </a>
        </div>
      </div>
    </section>

    <section class="banner-artisanal">
      <div class="banner-track">
        <span>100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal •100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal •100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal •100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal •</span>
      </div>
    </section>

    <div class="accueil">
      <section class="avantages-card transition-boom">
        <div class="histoire-img">
          <img src="../../medias/canapekenitra.png" alt="Aperçu canapé personnalisé">
        </div>
        <div class="avantages-text">
          <h2>Qui sommes-nous ?</h2>
          <p class="histoire-description">
            Chez Déco du Monde, chaque salon marocain est pensé comme une œuvre unique,
            façonnée selon vos envies, vos goûts et vos traditions. Du choix des tissus à la finition
            des détails,
            nous mettons notre passion et notre savoir-faire au service d'un mobilier qui vous
            ressemble.
          </p>
          <p class="histoire-mission">
            Notre mission : faire vivre l'artisanat marocain dans des intérieurs modernes et
            chaleureux,
            en alliant confort, élégance et culture.
          </p>
          <a href="apropos.php" class="btn-beige histoire-btn">
            En savoir plus
          </a>
        </div>
      </section>
      <section class="combination-section transition-all">
        <h2>Inspirez-vous de nos salons marocains</h2>
        <div class="carousel-container" id="carousel">
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
                <p class="price"><?= number_format($prixDynamique, 2, ',', ' ') ?> €</p>
                <button class="btn-beige btn-fullwidth"
                  onclick="window.location.href = '../CanapePrefait/canapPrefait.php?id=<?= (int)$commande['id']; ?>'">
                  Personnaliser
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
      <section class="avantages-card transition-boom">
        <div class="avantages-text">
          <h2>Pourquoi personnaliser votre canapé ici ?</h2>
          <ul>
            <li>Visualisation en temps réel de votre canapé</li>
            <li>Produits faits main, sur mesure</li>
            <li>Livraison rapide et soignée</li>
            <li>Paiement sécurisé</li>
          </ul>
        </div>
        <div class="avantages-img">
          <img src="../../medias/canapekenitra.png" alt="Aperçu canapé personnalisé">
        </div>
      </section>
      <script>
        const carousel = document.getElementById('carousel');
        // Dupliquer le contenu pour effet infini
        const clone = carousel.innerHTML;
        carousel.innerHTML += clone;
        let scrollAmount = 0;
        let speed = 2; // vitesse de défilement automatique
        let isPaused = false;
        let isUserScrolling = false;
        let userScrollTimeout;
        function scrollCarousel() {
          if (!isPaused && !isUserScrolling) {
            scrollAmount += speed;
            carousel.scrollLeft = scrollAmount;
            if (scrollAmount >= carousel.scrollWidth / 2) {
              scrollAmount = 0;
              carousel.scrollLeft = 0;
            }
          }
          requestAnimationFrame(scrollCarousel);
        }
        scrollCarousel();
        // Pause au survol
        carousel.addEventListener('mouseenter', () => {
          isPaused = true;
        });
        carousel.addEventListener('mouseleave', () => {
          isPaused = false;
        });
        // Pause si l'utilisateur scrolle manuellement
        carousel.addEventListener('scroll', () => {
          isUserScrolling = true;
          clearTimeout(userScrollTimeout);
          userScrollTimeout = setTimeout(() => {
            isUserScrolling = false;
            scrollAmount = carousel.scrollLeft; // reprendre à la bonne position
          }, 1000); // attend 1s après dernier scroll
        });
      </script>
      <section class="combination-section transition-all" >
        <h2>Les indispensables à l’unité</h2>
        <div class="carousel-container" id="carousel-unitaires">
          <?php foreach ($produits as $produit): ?>
            <div class="product-card">
              <div class="product-image">
                <img
                  src="<?= htmlspecialchars($produit['img']) ?>"
                  alt="<?= htmlspecialchars($produit['nom']) ?>" />
              </div>
              <div class="product-content">
                <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                <p class="price"><?= number_format($produit['prix'], 2, ',', ' ') ?> €</p>
                <button class="btn-beige btn-fullwidth"
                  onclick="window.location.href = 'page-produit.php?id=<?= (int)$produit['id']; ?>'">
                  Voir l'article
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
      <script>
        const carouselUnit = document.getElementById('carousel-unitaires');
        const cloneUnit = carouselUnit.innerHTML;
        carouselUnit.innerHTML += cloneUnit;
        let scrollAmountUnit = 0;
        let speedUnit = 2;
        let isPausedUnit = false;
        let isUserScrollingUnit = false;
        let userScrollTimeoutUnit;
        function scrollCarouselUnit() {
          if (!isPausedUnit && !isUserScrollingUnit) {
            scrollAmountUnit += speedUnit;
            carouselUnit.scrollLeft = scrollAmountUnit;
            if (scrollAmountUnit >= carouselUnit.scrollWidth / 2) {
              scrollAmountUnit = 0;
              carouselUnit.scrollLeft = 0;
            }
          }
          requestAnimationFrame(scrollCarouselUnit);
        }
        scrollCarouselUnit();
        carouselUnit.addEventListener('mouseenter', () => {
          isPausedUnit = true;
        });
        carouselUnit.addEventListener('mouseleave', () => {
          isPausedUnit = false;
        });
        carouselUnit.addEventListener('scroll', () => {
          isUserScrollingUnit = true;
          clearTimeout(userScrollTimeoutUnit);
          userScrollTimeoutUnit = setTimeout(() => {
            isUserScrollingUnit = false;
            scrollAmountUnit = carouselUnit.scrollLeft;
          }, 1000);
        });
      </script>
      <!-- Section Commencer un devis -->
      <section class="devis-section transition-all">
        <div class="devis-container">
          <section class="process-section">
            <h2 class="h2-center">Personnalisez votre salon en 4 étapes</h2>
            <div class="process-cards">
              <div class="process-card">
                <i class="fas fa-couch"></i>
                <h3>1. Je personnalise</h3>
                <p>Choisissez votre modèle, les couleurs, les modules et envoyez votre demande.</p>
              </div>
              <div class="process-card">
                <i class="fas fa-file-invoice"></i>
                <h3>2. Je reçois un devis</h3>
                <p>Notre équipe vous envoie un devis clair et détaillé sous 24h maximum.</p>
              </div>
              <div class="process-card">
                <i class="fas fa-check-circle"></i>
                <h3>3. Je paie en magasin</h3>
                <p>Vous validez la commande et effectuez le paiement en magasin.</p>
              </div>
              <div class="process-card">
                <i class="fas fa-truck"></i>
                <h3>4. Livraison rapide</h3>
                <p>Livraison sous 3 à 5 jours ouvrés, chez vous, avec le plus grand soin.</p>
              </div>
            </div>
          </section>
          <a href="dashboard.php">
            <button class="btn-noir">
              Commencer un devis
            </button>
          </a>
        </div>
      </section>
      <section class="stats-section transition-boom">
        <h2 class="h2-center">Ils nous font confiance</h2>
        <ul class="stats-list">
          <li><strong data-target="500" data-plus="true">0</strong> canapés personnalisés</li>
          <li><strong data-target="4.8" data-decimal="true">0/5</strong> de satisfaction client</li>
          <li><strong data-target="4">0j</strong> de délai moyen de fabrication</li>
        </ul>
      </section>
      <div class="faq-contact transition-all">
        <div class="faq-contact-icon"><i class="fa-solid fa-comment faq-contact-icon"></i></div>
        <h2 class="h2-center">Une question ? On est là pour vous aider</h2>
        <p>Découvrez les réponses aux questions les plus fréquentes sur la personnalisation, la livraison ou nos engagements.</p>
        <br>
        <a href="../pages/faq.php" class="btn-beige">Voir la FAQ complète</a>
      </div>
    </div>


  </main>
  <!-- Footer -->
  <footer>
    <?php require '../../squelette/footer.php'; ?>
  </footer>

</body>

</html>