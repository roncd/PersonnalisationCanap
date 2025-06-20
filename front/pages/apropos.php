<?php
require '../../admin/config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>A propos</title>
  <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/apropos.css">
</head>

<body>
  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <main>


    <div class="banner">
      <img src="../../medias/canapeapropos.jpg" alt="Canapé à propos">
      <h1>A propos</h1>
    </div>
    <div class="about-content">
      <div class="text-content">
        <h2>Déco du Monde</h2>
        <p>
          Chez Déco du Monde, nous vous offrons la liberté de créer un canapé qui vous ressemble.
          Inspirés par l’artisanat marocain et les tendances déco, nos modèles
          sont personnalisables selon vos envies : couleurs, tissus, dimensions, finitions. Chaque pièce est fabriquée avec soin par des artisans locaux,
          pour un rendu unique et chaleureux dans votre intérieur.
        </p>
      </div>
      <div class="image-content">
        <img src="../../medias/canape_intro_apropos.jpg" alt="Présentation Déco du Monde">
      </div>
    </div>
    <div class="values-section">
        <h2>Nos valeurs</h2>
        <div class="values-container">
            <div class="value-item">
                <h3>Artisanat</h3>
                <p>Nous valorisons le savoir-faire artisanal et le travail minutieux de nos artisans locaux.</p>
                <img src="../../medias/icons-authencite.png" alt="Artisanat" class="value-icon">
            </div>
            <div class="value-item">
                <h3>Qualité</h3>
                <p>Nous nous engageons à fournir des produits de haute qualité avec des matériaux soigneusement sélectionnés.</p>
                <img src="../../medias/icon-personnaliser.png" alt="Qualité" class="value-icon">
            </div>
            <div class="value-item">
                <h3>Personnalisation</h3>
                <p>Nous croyons en la création de pièces uniques adaptées aux goûts et besoins de chaque client.</p>
                <img src="../../medias/icon_accessibilite.jpg" alt="Personnalisation" class="value-icon">
            </div>
        </div>
    </div>
    <div class="why-us-section">
      <div class="why-us-content">
        <div class="why-us-image">
          <img src="../../medias/decoration_oriental.jpg" alt="Ambiance orientale">
        </div>
        <div class="why-us-text">
          <h2><span class="highlight">Pourquoi nous </span> ?</h2>
          <p>Notre concept repose sur la personnalisation.</p>
          <p>
            Chaque canapé est fabriqué sur commande, avec le plus grand soin, par des artisans marocains expérimentés.
            Que vous soyez à la recherche d’un modèle élégant pour un salon moderne, ou d’un canapé d’inspiration orientale
            pour une ambiance chaleureuse, vous trouverez sur notre site la solution idéale.
          </p>
          <p>
            Grâce à notre système de configuration facile à utiliser, vous pouvez visualiser votre création en quelques clics.
          </p>
          <p>
            Du canapé d’angle spacieux au petit modèle convertible, tout est pensé pour s’adapter à votre espace et à votre style de vie.
          </p>
          <p>
            Nous mettons un point d’honneur à proposer des produits de qualité à des prix justes, tout en valorisant l’artisanat local.
          </p>
          <p>
            La fabrication est soignée, les matériaux sélectionnés avec exigence, et la livraison est assurée dans les meilleurs délais.
          </p>
        </div>
      </div>
    </div>

<div class="customizer-section">
  <div class="customizer-left">
    <div class="customizer-content">
      <h2><span class="highlight">Un canapé unique</span>, fait pour vous</h2>
      <p>
        Velours, bois, motifs berbères ou couleurs modernes...<br>
        À vous de composer le canapé qui vous ressemble.
      </p>
      <div class="options">
        <div class="option">
          <img src="../../medias/couleur.jpg" alt="Modèle">
          <span>Couleur</span>
        </div>
        <div class="option">
          <img src="../../medias/tissu.jpg" alt="Tissu">
          <span>Tissu</span>
        </div>
        <div class="option">
          <img src="../../medias/boisnoir.jpeg" alt="Matière">
          <span>Matière</span>
        </div>
        <div class="option">
          <img src="../../medias/motif.jpg" alt="Motif">
          <span>Motif</span>
        </div>
      </div>
    <a href="dashboard.php" class="custom-btn">Je crée mon canapé</a>
    </div>
  </div>
  <div class="customizer-right">
    <img src="../../medias/canape_droite.jpg" alt="Canapé personnalisé">
  </div>
</div>
            <section class="ateliers">
                <h2>Nos ateliers en France</h2>
                <p class="intro">
                Au cœur de nos ateliers français, chaque canapé est façonné avec soin par des artisans passionnés.
                Inspirés du savoir-faire marocain, ils marient techniques traditionnelles et exigence moderne
                pour créer des pièces uniques et durables.
                </p>
                <div class="images">
                <img src="../../medias/image-bois-gauche.jpg" alt="Travail du bois à la main">
                <img src="../../medias/tissu_marocain.jpg" alt="Tissu marocain coloré">
                <img src="../../medias/image-bois-droite.jpg" alt="Travail du bois à la main">
                </div>
                <p class="conclusion">
                Chaque tissu est choisi avec minutie, chaque finition est travaillée à la main, pour vous offrir un canapé qui vous ressemble, fabriqué localement avec amour.<br>
                Chez Déco du Monde, nous faisons le choix d’une fabrication française, engagée et de qualité.
                </p>
            </section>


  </main>

  <?php require_once '../../squelette/footer.php' ?>
</body>

</html>