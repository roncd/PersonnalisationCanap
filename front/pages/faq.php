<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FAQ - Canapés Marocains</title>
  <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
  <link rel="stylesheet" href="../../styles/faq.css">
</head>
<body>
  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <main class="faq-container">

   <section class="faq-categories">
  <h1>FAQ</h1>
  <p>Votre question concerne quel sujet ?</p>

 <div class="faq-grid">
    <a href="../pages/informations_produit_FAQ.php" class="faq-card">
      <div class="icon">
        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 12h16M4 8h16M4 16h16"/></svg>
      </div>
      <span>Informations produits</span>
    </a>
    <a href="../pages/livraison_FAQ.php" class="faq-card">
      <div class="icon">
        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12h13v8H3z"/><path d="M16 16h5l-1.5-4.5L16 9z"/></svg>
      </div>
      <span>Livraison</span>
    </a>
    <a href="../pages/assurance_FAQ.php" class="faq-card">
      <div class="icon">
        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2l7 4v6c0 5.25-7 10-7 10S5 17.25 5 12V6z"/></svg>
      </div>
      <span>Garantie & assurance</span>
    </a>
    <a href="../pages/commande_FAQ.php" class="faq-card">
      <div class="icon">
        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/></svg>
      </div>
      <span>Commande</span>
    </a>
    <a href="../pages/compte_client_FAQ.php" class="faq-card">
      <div class="icon">
        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a7.5 7.5 0 0 1 13 0"/></svg>
      </div>
      <span>Compte client</span>
    </a>
    <a href="../pages/paiement_FAQ.php" class="faq-card">
      <div class="icon">
        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
      </div>
      <span>Moyens de paiements</span>
    </a>
    <a href="../pages/retour_remboursements_FAQ.php" class="faq-card">
      <div class="icon">
        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 20H5a2 2 0 0 1-2-2V8"/><polyline points="3 6 5 8 7 6"/></svg>
      </div>
      <span>Retours & remboursements</span>
    </a>
    <a href="../pages/offre_promo_FAQ.php" class="faq-card">
      <div class="icon">
        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 8V7a2 2 0 0 0-2-2h-1M3 8v1a2 2 0 0 0 2 2h1m16 5v1a2 2 0 0 1-2 2h-1m-16-5v-1a2 2 0 0 1 2-2h1"/><circle cx="12" cy="12" r="3"/></svg>
      </div>
      <span>Offres promotionnelles</span>
    </a>
  </div>
</section>

    <div class="faq-contact">
        <div class="faq-contact-icon">💬</div>
        <h2>Vous avez d’autres questions ?</h2>
        <p>Contactez-nous, notre équipe se fera un plaisir de vous aider.</p>
        <a href="../pages/aide.php" class="faq-contact-button">Nous contacter</a>
    </div>

  </main>

  <?php require_once '../../squelette/footer.php'; ?>

  <script>
    // Toggle des réponses
    document.querySelectorAll('.faq-question').forEach((question) => {
      question.addEventListener('click', () => {
        const item = question.parentElement;
        item.classList.toggle('active');

        const toggle = question.querySelector('.faq-toggle');
        toggle.textContent = item.classList.contains('active') ? '−' : '+';
      });
    });
  </script>
</body>
</html>
