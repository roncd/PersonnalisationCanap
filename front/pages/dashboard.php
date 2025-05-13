<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../formulaire/Connexion.php"); // Redirection vers la page de connexion
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <title>Dashboard</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link rel="stylesheet" href="../../styles/dashboard.css">

</head>

<body>
    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>
    <main >
        <!-- SECTION HERO BANNIÈRE -->
        <section class="hero-banner">
            <div class="hero-banner__content">
            <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>                
            <p>
                Testez notre <strong>configurateur de canapé</strong> et imaginez le meuble qui répond à vos goûts et à l’aménagement de votre salon. De la couleur à la texture du revêtement, vous pouvez faire votre choix parmi des dizaines d’options.
                </p>
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
    <a href="../EtapesPersonnalisation/etape1-1-structure.php" class="customize-button">Commencer la personnalisation</a>
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
                <!-- Exemple de product-card, dupliquez-le 4 fois -->
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
                <!-- Répétez product-card pour chaque modèle -->

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
    <!-- Dupliquez cette card pour chaque produit -->
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
    <!-- fin de interest-card -->
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



</body>

<footer>
    <?php require '../../squelette/footer.php'; ?>
</footer>

</html>