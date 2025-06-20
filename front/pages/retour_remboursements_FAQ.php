<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/x-icon" href="../../../medias/favicon.png">
  <link rel="stylesheet" href="../../styles/categories_faq.css">
  <title>Commande</title>
</head>
<body>

  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <main class="faq-page">
    <div class="faq-page-container">
      <h1>Retours & remboursements</h1>

      <!-- Barre de recherche -->
      <input type="text" id="faq-search" placeholder="Rechercher une question..." class="faq-search">

      <!-- Liste des questions -->
      <div class="faq" id="faq-list">
        <div class="accordeon-item">
          <button class="accordeon-header">
            <span class="accordeon-title">Puis-je retourner un produit personnalisé ?</span>
            <span class="accordeon-icon">+</span>
          </button>
          <div class="accordeon-content">
            <p>Non, sauf en cas de défaut constaté à la réception.</p>
          </div>
        </div>

        <div class="accordeon-item">
          <button class="accordeon-header">
            <span class="accordeon-title">Quels sont les délais de retour ?</span>
            <span class="accordeon-icon">+</span>
          </button>
          <div class="accordeon-content">
            <p>Vous avez 14 jours à réception pour retourner un article non personnalisé.</p>
          </div>
        </div>

        <div class="accordeon-item">
          <button class="accordeon-header">
            <span class="accordeon-title">Combien de temps pour un remboursement ?</span>
            <span class="accordeon-icon">+</span>
          </button>
          <div class="accordeon-content">
            <p>Sous 7 à 10 jours ouvrés après validation du retour.</p>
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
