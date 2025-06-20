<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/x-icon" href="../../../medias/favicon.png">
  <link rel="stylesheet" href="../../styles/categories_faq.css">
  <title>Garantie & Assurance</title>
</head>
<body>

  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <main class="faq-page">
    <div class="faq-page-container">
      <h1>Garantie & Assurance</h1>

      <!-- Barre de recherche -->
      <input type="text" id="faq-search" placeholder="Rechercher une question..." class="faq-search">

      <!-- Liste des questions -->
      <div class="faq" id="faq-list">
        <div class="accordeon-item">
          <button class="accordeon-header">
            <span class="accordeon-title">Vos produits sont-ils garantis ?</span>
            <span class="accordeon-icon">+</span>
          </button>
          <div class="accordeon-content">
            <p>Oui, tous nos produits bénéficient d'une garantie de 2 ans.</p>
          </div>
        </div>

        <div class="accordeon-item">
          <button class="accordeon-header">
            <span class="accordeon-title">Que couvre la garantie ?</span>
            <span class="accordeon-icon">+</span>
          </button>
          <div class="accordeon-content">
            <p>La garantie couvre tout défaut de fabrication ou de matériaux.</p>
          </div>
        </div>

        <div class="accordeon-item">
          <button class="accordeon-header">
            <span class="accordeon-title">Proposez-vous une assurance contre les dommages ?</span>
            <span class="accordeon-icon">+</span>
          </button>
          <div class="accordeon-content">
            <p>Une assurance transport est incluse. Une extension peut être ajoutée lors du paiement.</p>
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
