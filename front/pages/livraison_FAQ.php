<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/x-icon" href="../../../medias/favicon.png">
  <link rel="stylesheet" href="../../styles/categories_faq.css">
  <title>Livraison</title>
</head>
<body>

  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <main class="faq-page">
    <div class="faq-page-container">
      <h1>Livraison</h1>

      <!-- Barre de recherche -->
      <input type="text" id="faq-search" placeholder="Rechercher une question..." class="faq-search">

      <!-- Liste des questions -->
      <div class="faq" id="faq-list">
        <div class="accordeon-item">
          <button class="accordeon-header">
            <span class="accordeon-title">Quels sont les délais de livraison ?</span>
            <span class="accordeon-icon">+</span>
          </button>
          <div class="accordeon-content">
            <p>Entre 3 à 5 jours ouvrés pour les produits en stock, jusqu’à 4 semaines pour les personnalisés.</p>
          </div>
        </div>

        <div class="accordeon-item">
          <button class="accordeon-header">
            <span class="accordeon-title">Livrez-vous en dehors de la France ?</span>
            <span class="accordeon-icon">+</span>
          </button>
          <div class="accordeon-content">
            <p>Oui, nous livrons en Europe. Les frais et délais varient selon le pays.</p>
          </div>
        </div>

        <div class="accordeon-item">
          <button class="accordeon-header">
            <span class="accordeon-title">Puis-je suivre ma commande ?</span>
            <span class="accordeon-icon">+</span>
          </button>
          <div class="accordeon-content">
            <p>Un lien de suivi vous est envoyé dès l’expédition de votre commande.</p>
          </div>
        </div>

      <!-- Message aucun résultat -->
      <p id="no-results" style="display:none; text-align:center; color:#a4745a; font-weight:500;">
        Aucune question ne correspond à votre recherche.
      </p>
    </div>
  </main>

  <?php include '../../squelette/footer.php'; ?>

  <script src="../../script/accordeon.js"></script>
  <script src="../../script/search.js"></script>

</body>
</html>
