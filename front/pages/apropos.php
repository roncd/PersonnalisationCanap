<?php
require '../../admin/config.php';
session_start();
require '../../admin/include/session_expiration.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>À propos</title>
  <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/apropos.css">
  <link rel="stylesheet" href="../../styles/buttons.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../styles/transition.css">
  <script type="module" src="../../script/transition.js"></script>
</head>

<body>
  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <main>


    <div class="banner">
      <img src="../../medias/apropos-banner.jpg" alt="Canapé à propos">
      <h1>À propos</h1>
    </div>
    <div class="about-content ">
      <div class="text-content transition-all">
        <h2>Déco du Monde</h2>
        <p>
          Chez Déco du Monde, nous vous offrons la liberté de créer un canapé qui vous ressemble.
          Inspirés par l’artisanat marocain et les tendances déco, nos modèles
          sont personnalisables selon vos envies : couleurs, tissus, dimensions, finitions. Chaque pièce est fabriquée avec soin par des artisans locaux,
          pour un rendu unique et chaleureux dans votre intérieur.
        </p>
      </div>
      <div class="image-content transition-boom">
        <img src="../../medias/apropos-presentation.jpg" alt="Présentation Déco du Monde">
      </div>
    </div>
    <div class="values-section transition-all">
        <h2>Nos valeurs</h2>
        <div class="values-container">
    <div class="value-item">
        <i class="fa-solid fa-hands" aria-hidden="true"></i>
        <h3>Artisanat</h3>
        <p>Nous valorisons le savoir-faire artisanal et le travail minutieux de nos artisans locaux.</p>
    </div>
    <div class="value-item">
        <i class="fa-solid fa-thumbs-up" aria-hidden="true"></i>
        <h3>Qualité</h3>
        <p>Nous nous engageons à fournir des produits de haute qualité avec des matériaux soigneusement sélectionnés.</p>
    </div>
    <div class="value-item">
        <i class="fa-solid fa-paint-brush" aria-hidden="true"></i>
        <h3>Personnalisation</h3>
        <p>Nous croyons en la création de pièces uniques adaptées aux goûts et besoins de chaque client.</p>
    </div>
</div>

    </div>
    <div class="why-us-section">
      <div class="why-us-content">
        <div class="why-us-image transition-boom">
          <img src="../../medias/apropos-whyus.jpg" alt="Ambiance orientale">
        </div>
        <div class="why-us-text transition-all">
          <h2><span class="highlight">Pourquoi nous </span> ?</h2>
          <p>Notre concept repose sur la personnalisation.</p>
          <p>
            Chaque canapé est fabriqué sur commande, avec le plus grand soin, par des artisans expérimentés.
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

<div class="customizer-section transition-all">
  <div class="customizer-left ">
    <div class="customizer-content ">
      <h2><span class="highlight">Un canapé unique</span>, fait pour vous</h2>
      <p>
        Velours, bois, motifs berbères ou couleurs modernes...<br>
        À vous de composer le canapé qui vous ressemble.
      </p>
      <div class="options">
        <div class="option">
          <img src="../../medias/apropos-decoration.jpg" alt="Modèle">
          <span>Décoration</span>
        </div>
        <div class="option">
          <img src="../../medias/apropos-tissu.jpg" alt="Tissu">
          <span>Tissu</span>
        </div>
        <div class="option">
          <img src="../../medias/apropos-matiere.jpg" alt="Matière">
          <span>Matière</span>
        </div>
        <div class="option">
          <img src="../../medias/apropos-motif.JPG" alt="Motif">
          <span>Motif</span>
        </div>
      </div>
    <a href="dashboard.php" class="btn-beige">Je crée mon canapé</a>
    </div>
  </div>
  <div class="customizer-right">
    <img src="../../medias/apropos-customizer.jpg" alt="Canapé personnalisé">
  </div>
</div>
           <section class="ateliers">
  <h2 class="transition-all">Notre atelier</h2>
  <p class="intro transition-all">
    Au cœur de nos ateliers français, chaque canapé est façonné avec soin par des artisans passionnés.
    Inspirés du savoir-faire marocain, ils marient techniques traditionnelles et exigence moderne
    pour créer des pièces uniques et durables.
  </p>

  <div class="carousel-container" id="carousel-ateliers">
  <div class="atelier-card">
    <img src="../../medias/atelier.jpg" alt="Travail du bois à la main">
  </div>
  <div class="atelier-card">
    <img src="../../medias/atelier1.jpg" alt="Tissu marocain coloré">
  </div>
  <div class="atelier-card">
    <img src="../../medias/atelier2.jpg" alt="Travail du bois à la main">
  </div>
  <div class="atelier-card">
    <img src="../../medias/atelier4.jpg" alt="Travail du bois à la main">
  </div>
  <div class="atelier-card">
    <img src="../../medias/atelier5.jpg" alt="Tissu marocain coloré">
  </div>
  <div class="atelier-card">
    <img src="../../medias/atelier6.jpg" alt="Travail du bois à la main">
  </div>
</div>


  <div class="carousel-container" id="carousel-ateliers2">
  <div class="atelier-card">
    <img src="../../medias/atelier8.jpg" alt="Travail du bois à la main">
  </div>
  <div class="atelier-card">
    <img src="../../medias/atelier9.jpg" alt="Tissu marocain coloré">
  </div>
  <div class="atelier-card">
    <img src="../../medias/atelier10.jpg" alt="Travail du bois à la main">
  </div>
  <div class="atelier-card">
    <img src="../../medias/atelier11.jpg" alt="Travail du bois à la main">
  </div>
  <div class="atelier-card">
    <img src="../../medias/atelier12.jpg" alt="Tissu marocain coloré">
  </div>
  <div class="atelier-card">
    <img src="../../medias/atelier7.jpg" alt="Travail du bois à la main">
  </div>
</div>
<!-- Premier carrousel -->
<script>
  const carousel1 = document.getElementById('carousel-ateliers');
  let scroll1 = 0;
  let speed1 = 2;
  let pause1 = false;
  let userScroll1 = false;
  let timeout1;

  function scrollCarousel1() {
    if (!pause1 && !userScroll1) {
      scroll1 += speed1;
      if (scroll1 >= carousel1.scrollWidth - carousel1.clientWidth) {
        scroll1 = 0;
      }
      carousel1.scrollLeft = scroll1;
    }
    requestAnimationFrame(scrollCarousel1);
  }

  scrollCarousel1();

  carousel1.addEventListener('mouseenter', () => pause1 = true);
  carousel1.addEventListener('mouseleave', () => pause1 = false);
  carousel1.addEventListener('scroll', () => {
    userScroll1 = true;
    clearTimeout(timeout1);
    timeout1 = setTimeout(() => {
      userScroll1 = false;
      scroll1 = carousel1.scrollLeft;
    }, 1000);
  });
</script>

<!-- Deuxième carrousel (sens inverse) -->
<script>
  const carousel2 = document.getElementById('carousel-ateliers2');
  let scroll2 = carousel2.scrollWidth; // Commence à droite
  let speed2 = -2;
  let pause2 = false;
  let userScroll2 = false;
  let timeout2;

  function scrollCarousel2() {
    if (!pause2 && !userScroll2) {
      scroll2 += speed2;
      if (scroll2 <= 0) {
        scroll2 = carousel2.scrollWidth - carousel2.clientWidth;
      }
      carousel2.scrollLeft = scroll2;
    }
    requestAnimationFrame(scrollCarousel2);
  }

  scrollCarousel2();

  carousel2.addEventListener('mouseenter', () => pause2 = true);
  carousel2.addEventListener('mouseleave', () => pause2 = false);
  carousel2.addEventListener('scroll', () => {
    userScroll2 = true;
    clearTimeout(timeout2);
    timeout2 = setTimeout(() => {
      userScroll2 = false;
      scroll2 = carousel2.scrollLeft;
    }, 1000);
  });
</script>


<section class="galerie">
  <h2 class="transition-all h2">Souvenirs de nos Ateliers</h2>
  <div class="container">
    <!-- Répète pour 9 images -->
    <input type="checkbox" id="item1">
    <label for="item1" class="item1" style="background-image:url('../../medias/apropos-galerie1.jpg');"></label>
    
    <input type="checkbox" id="item2">
    <label for="item2" class="item2" style="background-image:url(../../medias/apropos-galerie2.jpg);"></label>
    
          <!-- Répète pour 9 images -->
    <input type="checkbox" id="item3">
    <label for="item3" class="item3" style="background-image:url(../../medias/apropos-galerie3.jpg);"></label>
    
    <input type="checkbox" id="item4">
    <label for="item4" class="item4" style="background-image:url(../../medias/apropos-galerie4.jpg);"></label>
    
          <!-- Répète pour 9 images -->
    <input type="checkbox" id="item5">
    <label for="item5" class="item5" style="background-image:url(../../medias/apropos-galerie5.jpg);"></label>
    
    <input type="checkbox" id="item6">
    <label for="item6" class="item6" style="background-image:url(../../medias/apropos-galerie6.png);"></label>
    

      
          <!-- Répète pour 9 images -->
    <input type="checkbox" id="item7">
    <label for="item7" class="item7" style="background-image:url(../../medias/apropos-galerie7.png);"></label>
    
    <input type="checkbox" id="item8">
    <label for="item8" class="item8" style="background-image:url(../../medias/apropos-galerie8.jpg);"></label>
    
          <!-- Répète pour 9 images -->
    <input type="checkbox" id="item9">
    <label for="item9" class="item9" style="background-image:url(../../medias/apropos-galerie9.png);"></label>
    <!-- ...jusqu'à item9 -->
  </div>
</section>

  </main>

  <?php require_once '../../squelette/footer.php' ?>
</body>

</html>