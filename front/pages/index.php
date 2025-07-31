<?php
require '../../admin/config.php';
session_start();
require '../../admin/include/session_expiration.php';


// 1. Canapés préfaits
$sqlCommandes = "SELECT cp.*, tb.nom AS type_nom 
    FROM commande_prefait cp
    JOIN type_banquette tb ON cp.id_banquette = tb.id
    WHERE cp.visible = 1";
$stmtCommandes = $pdo->prepare($sqlCommandes);
$stmtCommandes->execute();
$commandes = $stmtCommandes->fetchAll(PDO::FETCH_ASSOC);


// 2. Articles à l’unité (vente_produit)
$sqlProduits = "SELECT vente_produit.*, categorie.nom AS nom_categorie 
    FROM vente_produit 
    JOIN categorie ON vente_produit.id_categorie = categorie.id 
    WHERE vente_produit.visible = 1
    ORDER BY vente_produit.id DESC";
$stmtProduits = $pdo->prepare($sqlProduits);
$stmtProduits->execute();
$produits = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);

function getSectionAccueil($slug)
{
  global $pdo;
  $stmt = $pdo->prepare("SELECT * FROM sections_accueil WHERE slug = ? AND visible = 1 LIMIT 1");
  $stmt->execute([$slug]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

$etapes = $pdo->query("SELECT * FROM etapes_accueil WHERE visible = 1 ORDER BY numero_etape ASC")->fetchAll();
$stats = $pdo->query("SELECT * FROM stats_accueil WHERE visible = 1")->fetchAll();

function calculPrix($commande, &$composition = [])
{
  global $pdo;

  $composition = [];
  $totalPrice = 0;
  $id_commande = $commande['id'];

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

  // rix par centimètre (350 € / mètre = 3.5 € / cm)
  $prixParCm = 3.5;

  foreach (['longueurA', 'longueurB', 'longueurC'] as $longueur) {
    if (!empty($commande[$longueur])) {
      $totalPrice += floatval($commande[$longueur]) * $prixParCm;
    }
  }

  // traitement spécifique de certains éléments (optionnel)
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

// Gestion de l'ajout au panier
$produitAjoute = null; 

// 1. Si on n'est pas connecté, on ne restaure pas de POST
if (!isset($_SESSION['user_id']) && isset($_GET['post_restore'])) {
  unset($_SESSION['temp_post']);
  header("Location: Connexion.php");
  exit;
}

// 2. Sinon on peut restaurer le POST si c'était prévu
if (isset($_GET['post_restore']) && isset($_SESSION['temp_post'])) {
  $_POST = $_SESSION['temp_post'];
  unset($_SESSION['temp_post']);
  $_SERVER['REQUEST_METHOD'] = 'POST';
}

if (isset($_SESSION['popup_produit'])) {
  $produitAjoute = $_SESSION['popup_produit']['nom'];
  $imageProduitAjoute = $_SESSION['popup_produit']['img'];
  unset($_SESSION['popup_produit']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['produit'])) {
  if (!isset($_SESSION['user_id'])) {
    $_SESSION['pending_add_to_cart'] = $_POST;
    header('Location: ../formulaire/Connexion.php');
    exit;
  }

  if (isset($_SESSION['user_id'])) {
    $id_client = $_SESSION['user_id'];

    $nomProduit = $_POST['produit'];
    $quantite = intval($_POST['quantite'] ?? 1);
    $stmt = $pdo->prepare("SELECT id, prix, img FROM vente_produit WHERE nom = ?");
    $stmt->execute([$nomProduit]);
    $produit = $stmt->fetch();

    if (!$produit) {
      die("Produit introuvable.");
    }

    $id = $produit['id'];
    $prix = $produit['prix'];
    $img = $produit['img'];

    // Vérifie s'il y a déjà un panier pour ce client
    $stmt = $pdo->prepare("SELECT id FROM panier WHERE id_client = ?");
    $stmt->execute([$id_client]);
    $panier = $stmt->fetch();

    if (!$panier) {
      // Créer un nouveau panier
      $stmt = $pdo->prepare("INSERT INTO panier (id_client, prix) VALUES (?, ?)");
      $stmt->execute([$id_client, 0]);
      $panier_id = $pdo->lastInsertId();
    } else {
      $panier_id = $panier['id'];
    }

    // Vérifie si le produit est déjà dans le panier
    $stmt = $pdo->prepare("SELECT * FROM panier_detail WHERE id_panier = ? AND id_produit = ?");
    $stmt->execute([$panier_id, $id]);
    $produit_existe = $stmt->fetch();

    if ($produit_existe) {
      $stmt = $pdo->prepare("UPDATE panier_detail SET quantite = quantite + ? WHERE id_panier = ? AND id_produit = ?");
      $stmt->execute([$quantite, $panier_id, $id]);
    } else {
      $stmt = $pdo->prepare("INSERT INTO panier_detail (id_panier, id_produit, quantite) VALUES (?, ?, ?)");
      $stmt->execute([$panier_id, $id, $quantite]);
    }

    // Mettre à jour le prix total dans la table panier
    $stmt = $pdo->prepare("UPDATE panier SET prix = prix + ? WHERE id = ?");
    $stmt->execute([$prix * $quantite, $panier_id]);
    $produitAjoute = $nomProduit;

    // Stocker le produit dans la session pour le popup après redirection
    $_SESSION['popup_produit'] = [
      'nom' => $nomProduit,
      'img' => $img
    ];

    // Rediriger pour éviter re-post et déclencher le modal
    header("Location: ?post_restore=1");
    exit;
  }
}
if (!empty($produitAjoute)) : ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const modal = document.getElementById("reservation-modal");
      const productNameEl = document.getElementById("product-name");
      const productImgEl = document.getElementById("product-image");

      const lastAddedProduct = {
        name: <?= json_encode($produitAjoute) ?>,
        image: <?= json_encode($imageProduitAjoute ?? '') ?>
      };

      function openReservationModal(productName, productImg) {
        productNameEl.textContent = `Nom du produit : ${productName}`;
        productImgEl.src = `../../admin/uploads/produit/${productImg}`;
        modal.style.display = "flex";
        document.documentElement.classList.add("no-scroll");
        document.body.classList.add("no-scroll");
        console.log("Image du produit :", productImgEl.src);
      }

      function fermerModal() {
        modal.style.display = "none";
        document.documentElement.classList.remove("no-scroll");
        document.body.classList.remove("no-scroll");
      }

      document.querySelector(".close-modal")?.addEventListener("click", fermerModal);

      window.addEventListener("click", (event) => {
        if (event.target === modal) {
          fermerModal();
        }
      });

      if (lastAddedProduct.name) {
        openReservationModal(lastAddedProduct.name, lastAddedProduct.image);
      }
    });
  </script>
<?php endif; ?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Personnalise ton salon marocain selon tes goûts : canapé, tissus, couleurs et configurations à prix raisonnables avec Déco du Monde.">
  <title>Déco du Monde - Personnalisation de canapé marocain</title>
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="https://www.decorient.fr/medias/favicon.png">
  <link rel="stylesheet" href="../../styles/styles.css">
  <link rel="stylesheet" href="../../styles/buttons.css">
  <link rel="stylesheet" href="../../styles/accueil.css">
  <link rel="stylesheet" href="../../styles/transition.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script type="module" src="../../script/animate-value.js"></script>
  <script type="module" src="../../script/transition.js"></script>
</head>

<body>
  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <!-- Main -->
  <main>
    <?php $hero = getSectionAccueil('hero'); ?>
    <?php if ($hero && $hero['visible']): ?>
      <section class="hero-section">
        <div class="hero-container">
          <img src="../../medias/<?= htmlspecialchars($hero['img']) ?>" alt="Salon marocain" class="hero-image">
          <div class="hero-content">
            <h1 class="hero-title h2"><?= htmlspecialchars($hero['titre']) ?></h1>
            <p class="hero-description"><?= nl2br(htmlspecialchars($hero['description'])) ?></p>
            <?php if ($hero['bouton_texte'] && $hero['bouton_lien']): ?>
              <a href="<?= htmlspecialchars($hero['bouton_lien']) ?>">
                <button class="btn-noir"><?= htmlspecialchars($hero['bouton_texte']) ?></button>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </section>
    <?php endif; ?>
    <section class="banner-artisanal">
      <div class="banner-track">
        <span>100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal •100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal •100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal •100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal • 100% artisanal •</span>
      </div>
    </section>

    <div class="accueil">
      <?php $apropos = getSectionAccueil('apropos'); ?>
      <?php if ($apropos && $apropos['visible']): ?>
        <section class="avantages-card transition-boom">
          <div class="histoire-img">
            <img src="../../medias/<?= htmlspecialchars($apropos['img']) ?>" alt="Aperçu canapé personnalisé">
          </div>
          <div class="avantages-text">
            <h2><?= htmlspecialchars($apropos['titre']) ?></h2>
            <p class="histoire-description">
              <?= nl2br(htmlspecialchars($apropos['description'])) ?></p>
            <?php if ($apropos['bouton_texte'] && $apropos['bouton_lien']): ?>
              <a href="<?= htmlspecialchars($apropos['bouton_lien']) ?>">
                <button class="btn-beige histoire-btn"><?= htmlspecialchars($apropos['bouton_texte']) ?></button>
              </a>
            <?php endif; ?>
          </div>
        </section>
      <?php endif; ?>

      <section class="combination-section transition-all">
        <h2>Laisse-toi inspirer par nos salons marocains</h2>
        <div class="carousel-container" id="carousel">
          <?php foreach ($commandes as $commande): ?>
            <?php
            $composition = [];
            $prixDynamique = calculPrix($commande, $composition);
            ?>
            <div class="product-card">
              <div class="product-image">
                <?php
                $imgName = $commande['img'] ?? 'canapePrefait.jpg';
                $imgPathPrimary = "../../admin/uploads/canape-prefait/" . $imgName;
                $imgPathFallback = "../../medias/canapePrefait.jpg";

                // Choisir le chemin selon l'existence du fichier
                if (!empty($imgName) && file_exists($imgPathPrimary)) {
                  $imgSrc = $imgPathPrimary;
                } else {
                  $imgSrc = $imgPathFallback;
                }
                ?>
                <img src="<?php echo htmlspecialchars($imgSrc, ENT_QUOTES); ?>"
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

        <div class="voir-plus">
          <button class="btn-noir"
            onclick="window.location.href = 'noscanapes.php';">
            Voir plus +
          </button>
        </div>
      </section>

      <?php $avantage = getSectionAccueil('avantage'); ?>
      <?php if ($avantage && $avantage['visible']): ?>
        <section class="avantages-card transition-boom">
          <div class="avantages-text">
            <h2><?= htmlspecialchars($avantage['titre']) ?></h2>
            <?php
            $lines = explode("\n", trim($avantage['description']));
            echo '<ul>';
            foreach ($lines as $line) {
              echo '<li>' . htmlspecialchars($line) . '</li>';
            }
            echo '</ul>';
            ?>
          </div>
          <div class="avantages-img">
            <img src="../../medias/<?= htmlspecialchars($avantage['img']) ?>" alt="Aperçu canapé personnalisé">
          </div>
        </section>
      <?php endif; ?>

      <section class="combination-section transition-all">
        <h2>Les indispensables à l’unité</h2>
        <div class="carousel-container" id="carousel-unitaires">
          <?php foreach ($produits as $produit): ?>
            <?php
            $catNom = isset($categoriesAssoc[$produit['id_categorie']])
              ? strtolower($categoriesAssoc[$produit['id_categorie']])
              : '';
            ?>
            <div class="product-card">
              <div class="product-image">
                <?php
                $imgName = $produit['img'] ?? 'produit.jpg';
                $imgPathPrimary = "../../admin/uploads/produit/" . $imgName;
                $imgPathFallback = "../../medias/produit.jpg";

                // Choisir le chemin selon l'existence du fichier
                if (!empty($imgName) && file_exists($imgPathPrimary)) {
                  $imgSrc = $imgPathPrimary;
                } else {
                  $imgSrc = $imgPathFallback;
                }
                ?>
                <img src="<?php echo htmlspecialchars($imgSrc, ENT_QUOTES); ?>"
                  alt="<?php echo htmlspecialchars($produit['nom'] ?? 'Canapé pré-fait', ENT_QUOTES); ?>">
              </div>
              <div class="product-content">
                <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                <p class="price"><?= number_format($produit['prix'], 2, ',', ' ') ?> €</p>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="produit" value="<?= htmlspecialchars($produit['nom']) ?>" />
                  <input type="hidden" name="quantite" value="1" />
                  <button type="submit" class="btn-beige btn-fullwidth">Ajouter au panier</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="voir-plus">
          <button class="btn-noir"
            onclick="window.location.href = 'nosproduits.php';">
            Voir plus +
          </button>
        </div>
      </section>

      <?php
      $etapesVisibles = array_filter($etapes, fn($etape) => $etape['visible']);
      ?>
      <?php if (!empty($etapesVisibles)): ?>
        <section class="devis-section transition-all">
          <div class="devis-container">
            <section class="process-section">
              <h2 class="h2-center">Personnalise ton salon en 4 étapes</h2>
              <div class="process-cards">
                <?php foreach ($etapes as $etape): ?>
                  <div class="process-card">
                    <i class="<?= htmlspecialchars($etape['icon']) ?>"></i>
                    <h3><?= htmlspecialchars($etape['numero_etape']) ?>. <?= htmlspecialchars($etape['titre']) ?></h3>
                    <p><?= htmlspecialchars($etape['description']) ?></p>
                  </div>
                <?php endforeach; ?>
              </div>
            </section>
            <a href="dashboard.php">
              <button class="btn-noir">
                Commencer un devis
              </button>
            </a>
          </div>
        </section>
      <?php endif; ?>

      <?php
      $statsVisibles = array_filter($stats, fn($s) => $s['visible']);
      ?>
      <?php if (!empty($statsVisibles)): ?>
        <section class="stats-section transition-boom">
          <h2 class="h2-center">Ils nous font confiance</h2>
          <ul class="stats-list">
            <?php foreach ($stats as $stat): ?>
              <?php
              $attrs = [];
              $attrs[] = 'data-target="' . htmlspecialchars($stat['valeur']) . '"';
              if ($stat['plus']) $attrs[] = 'data-plus="true"';
              if ($stat['decimal']) $attrs[] = 'data-decimal="true"';
              if ($stat['pourcentage']) $attrs[] = 'data-percent="true"';
              if ($stat['notation']) $attrs[] = 'data-notation="true"';
              ?>
              <li>
                <strong <?= implode(' ', $attrs) ?>>0</strong> <?= htmlspecialchars($stat['label']) ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </section>
      <?php endif; ?>


      <div class="faq-contact transition-all">
        <div class="faq-contact-icon"><i class="fa-solid fa-comment faq-contact-icon"></i></div>
        <h2 class="h2-center">Une question ? On est là pour t'aider</h2>
        <p>Découvrez les réponses aux questions les plus fréquentes sur la personnalisation, la livraison ou nos engagements.</p>
        <br>
        <a href="../pages/faq.php" class="btn-beige">Voir la FAQ complète</a>
      </div>
    </div>

    <!-- Modal d'ajout au panier -->
    <div id="reservation-modal" class="modal" style="display:none;">
      <div class="modal-content">
        <span class="close-modal">&times;</span>
        <img src="../../assets/check-icone.svg" alt="Image du produit" class="check-icon" />
        <br />
        <h2 class="success-message">Ajouté au panier avec succès !</h2>
        <div class="product-info">
          <img id="product-image" class="img-panier" />
          <p id="product-name">Nom du produit :</p>
          <p>
            Quantité : <span id="quantity">1</span>
          </p>
        </div>
        <div class="modal-buttons">
          <button class="ajt-panier" onclick="window.location.href='index.php'">Continuer vos achats</button>
          <button class="btn-noir" onclick="window.location.href='panier.php'">Voir le panier</button>
        </div>
      </div>
    </div>

  </main>
  <script>
    const carousel = document.getElementById('carousel');
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
  <!-- Footer -->
  <footer>
    <?php require '../../squelette/footer.php'; ?>
  </footer>

</body>

</html>