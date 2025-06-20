<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/x-icon" href="../../../medias/favicon.png">
  <link rel="stylesheet" href="../../styles/categories_faq.css">
  <title>Informations produits</title>
</head>
<body>

  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <main class="faq-page">
    <div class="faq-page-container">
      <h1>Moyens de paiements</h1>

      <!-- Barre de recherche -->
      <input type="text" id="faq-search" placeholder="Rechercher une question..." class="faq-search">

      <!-- Liste des questions -->
      <div class="faq" id="faq-list">
        <div class="accordeon-item">
          <button class="accordeon-header">
            <span class="accordeon-title">Quels moyens de paiement acceptez-vous ?</span>
            <span class="accordeon-icon">+</span>
          </button>
          <div class="accordeon-content">
            <p>Carte bancaire, PayPal, Apple Pay, et virement.</p>
          </div>
        </div>

        <div class="accordeon-item">
          <button class="accordeon-header">
            <span class="accordeon-title">Le paiement est-il sécurisé ?</span>
            <span class="accordeon-icon">+</span>
          </button>
          <div class="accordeon-content">
            <p>Oui, notre plateforme utilise le protocole SSL et un cryptage 3D Secure.</p>
          </div>
        </div>

        <div class="accordeon-item">
          <button class="accordeon-header">
            <span class="accordeon-title">Puis-je payer en plusieurs fois ?</span>
            <span class="accordeon-icon">+</span>
          </button>
          <div class="accordeon-content">
            <p>Oui, le paiement en 3x ou 4x est proposé via Alma à partir de 100€.</p>
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
