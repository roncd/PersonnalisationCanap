<?php
require '../../admin/config.php';
session_start();
require '../../admin/include/session_expiration.php';

if (!isset($_SESSION['user_id'])) {
  $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
  header("Location: ../formulaire/Connexion.php");
  exit;
}

$id_client = $_SESSION['user_id'];

if (isset($_GET['reset']) && $_GET['reset'] == 1) {
  $stmt = $pdo->prepare("DELETE FROM commande_temporaire WHERE id_client = ?");
  $stmt->execute([$id_client]);
}

$stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ?");
$stmt->execute([$id_client]);
$existing_order = $stmt->fetch(PDO::FETCH_ASSOC);

//commande existante
$show_commencer = !$existing_order;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
  <title>Tableau de bord</title>
  <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
  <link rel="stylesheet" href="../../styles/processus.css">
  <link rel="stylesheet" href="../../styles/dashboard.css">
  <link rel="stylesheet" href="../../styles/popup.css">
  <link rel="stylesheet" href="../../styles/buttons.css">

  <script type="module" src="../../script/popup.js"></script>

</head>

<body>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>
  <main>

    <!-- SECTION HERO BANNIÈRE -->
    <section class="hero-banner">
      <div class="hero-banner-content">
        <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
        <p>
          Testez notre <strong>configurateur de canapé</strong> et imaginez le meuble qui répond à vos goûts et à l’aménagement de votre salon.
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
    </section>

    <!-- SECTION PERSONNALISATION -->
    <section class="customize-section">
      <div class="customize-text">
        <h2>Créez vous-même votre canapé marocain idéal</h2>
        <ul class="customize-features">
          <li><img src="../../medias/canape_icon.png" alt="Forme" class="feature-icon">Choisissez la forme du canapé</li>
          <li><img src="../../medias/couleurs_icon.png" alt="Couleurs" class="feature-icon">Sélectionnez les couleurs & matières</li>
          <li><img src="../../medias/coussin_icon.png" alt="Coussins" class="feature-icon">Ajoutez vos coussins préférés</li>
          <li><img src="../../medias/artiste_icon.png" alt="Aperçu" class="feature-icon">Aperçu en temps réel de votre création</li>
        </ul>
        <a href="../EtapesPersonnalisation/etape1-1-structure.php" class="btn-beige">Commencer la personnalisation</a>
      </div>
      <div class="customize-image">
        <!-- Blob de fond (SVG ou PNG) -->
        <img class="blob" src="../../medias/blob.png" alt="forme décorative">
        <!-- Image du canapé (taille réduite, devant le blob) -->
        <img class="sofa" src="../../medias/sofa.png" alt="Canapé personnalisé">
      </div>
    </section>


    <!-- ------------------- SECTION COMBINAISONS ------------------- -->
    <section class="combination-section">
      <h2>Choisissez une combinaison à personnaliser</h2>
      <div class="combination-container">

      <div class="product-card">
          <img src="../../medias/sofa.png" alt="SÖDERHAMN Canapé 3 places">
          <h3>SÖDERHAMN</h3>
          <p>Canapé 3 places</p>
          <span class="price">649,00 €</span>
          <div class="eco-tax">dont Éco-part. Mobilier 17,10 €</div>
          <div class="options">
            <img src="../../medias/sofa.png" alt="option coussin">
            <img src="../../medias/sofa.png" alt="option coussin">
            <img src="../../medias/sofa.png" alt="option coussin">
            <span class="more">+11</span>
          </div>
        </div>

        <div class="product-card">
          <img src="../../medias/sofa.png" alt="SÖDERHAMN Canapé 3 places">
          <h3>SÖDERHAMN</h3>
          <p>Canapé 3 places</p>
          <span class="price">649,00 €</span>
          <div class="eco-tax">dont Éco-part. Mobilier 17,10 €</div>
          <div class="options">
            <img src="../../medias/sofa.png" alt="option coussin">
            <img src="../../medias/sofa.png" alt="option coussin">
            <img src="../../medias/sofa.png" alt="option coussin">
            <span class="more">+11</span>
          </div>
        </div>


        <div class="product-card">
          <img src="../../medias/sofa.png" alt="SÖDERHAMN Canapé 3 places">
          <h3>SÖDERHAMN</h3>
          <p>Canapé 3 places</p>
          <span class="price">649,00 €</span>
          <div class="eco-tax">dont Éco-part. Mobilier 17,10 €</div>
          <div class="options">
            <img src="../../medias/sofa.png" alt="option coussin">
            <img src="../../medias/sofa.png" alt="option coussin">
            <img src="../../medias/sofa.png" alt="option coussin">
            <span class="more">+11</span>
          </div>
        </div>


        <div class="product-card">
          <img src="../../medias/sofa.png" alt="SÖDERHAMN Canapé 3 places">
          <h3>SÖDERHAMN</h3>
          <p>Canapé 3 places</p>
          <span class="price">649,00 €</span>
          <div class="eco-tax">dont Éco-part. Mobilier 17,10 €</div>
          <div class="options">
            <img src="../../medias/sofa.png" alt="option coussin">
            <img src="../../medias/sofa.png" alt="option coussin">
            <img src="../../medias/sofa.png" alt="option coussin">
            <span class="more">+11</span>
          </div>
        </div>
      </div>
    </section>


    <section class="interest-section">
      <h2>Ces articles peuvent aussi vous intéresser</h2>
      <div class="interest-container">

      <div class="interest-card">
          <img src="../../medias/coussin.png" alt="HANNELISE Coussin">
          <h3>HANNELISE</h3>
          <p>Coussin, 50x50 cm</p>
          <span class="price">8,99€</span>
          <div class="rating">
            ★★★★★
            <span class="count">(94)</span>
          </div>
          <div class="availability">
            <span class="status available">● Disponible pour la livraison</span>
            <span class="status in-stock">● En stock à Paris Nord</span>
          </div>
          <div class="actions">
            <button class="btn-basket"><i class="fas fa-shopping-cart"></i></button>
            <button class="btn-favorite"><i class="fas fa-heart"></i></button>
          </div>
        </div>

        <div class="interest-card">
          <img src="../../medias/coussin.png" alt="HANNELISE Coussin">
          <h3>HANNELISE</h3>
          <p>Coussin, 50x50 cm</p>
          <span class="price">8,99€</span>
          <div class="rating">
            ★★★★★
            <span class="count">(94)</span>
          </div>
          <div class="availability">
            <span class="status available">● Disponible pour la livraison</span>
            <span class="status in-stock">● En stock à Paris Nord</span>
          </div>
          <div class="actions">
            <button class="btn-basket"><i class="fas fa-shopping-cart"></i></button>
            <button class="btn-favorite"><i class="fas fa-heart"></i></button>
          </div>
        </div>

        <div class="interest-card">
          <img src="../../medias/coussin.png" alt="HANNELISE Coussin">
          <h3>HANNELISE</h3>
          <p>Coussin, 50x50 cm</p>
          <span class="price">8,99€</span>
          <div class="rating">
            ★★★★★
            <span class="count">(94)</span>
          </div>
          <div class="availability">
            <span class="status available">● Disponible pour la livraison</span>
            <span class="status in-stock">● En stock à Paris Nord</span>
          </div>
          <div class="actions">
            <button class="btn-basket"><i class="fas fa-shopping-cart"></i></button>
            <button class="btn-favorite"><i class="fas fa-heart"></i></button>
          </div>
        </div>

        <div class="interest-card">
          <img src="../../medias/coussin.png" alt="HANNELISE Coussin">
          <h3>HANNELISE</h3>
          <p>Coussin, 50x50 cm</p>
          <span class="price">8,99€</span>
          <div class="rating">
            ★★★★★
            <span class="count">(94)</span>
          </div>
          <div class="availability">
            <span class="status available">● Disponible pour la livraison</span>
            <span class="status in-stock">● En stock à Paris Nord</span>
          </div>
          <div class="actions">
            <button class="btn-basket"><i class="fas fa-shopping-cart"></i></button>
            <button class="btn-favorite"><i class="fas fa-heart"></i></button>
          </div>
        </div>

        <div class="interest-card">
          <img src="../../medias/coussin.png" alt="HANNELISE Coussin">
          <h3>HANNELISE</h3>
          <p>Coussin, 50x50 cm</p>
          <span class="price">8,99€</span>
          <div class="rating">
            ★★★★★
            <span class="count">(94)</span>
          </div>
          <div class="availability">
            <span class="status available">● Disponible pour la livraison</span>
            <span class="status in-stock">● En stock à Paris Nord</span>
          </div>
          <div class="actions">
            <button class="btn-basket"><i class="fas fa-shopping-cart"></i></button>
            <button class="btn-favorite"><i class="fas fa-heart"></i></button>
          </div>
        </div>
      </div>
    </section>


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
</body>

<footer>
  <?php require '../../squelette/footer.php'; ?>
</footer>

</html>