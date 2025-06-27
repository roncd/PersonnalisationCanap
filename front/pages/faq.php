<?php
require '../../admin/config.php';
session_start();

$stmt = $pdo->prepare("SELECT id, nom, icon FROM faq_categorie ORDER BY nom ASC");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FAQ - Canapés Marocains</title>
  <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../styles/faq.css">
  <link rel="stylesheet" href="../../styles/buttons.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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
    <?php foreach ($categories as $categorie): ?>
      <a href="faq_details.php?categorie=<?= urlencode($categorie['id']) ?>" class="faq-card">
        <div class="icon">
  <i class="fa <?= htmlspecialchars($categorie['icon'] ?? 'fa-question-circle') ?>"></i>
</div>
        <span><?= htmlspecialchars($categorie['nom']) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

    <div class="faq-contact">
        <div class="faq-contact-icon"><i class="fa-solid fa-comment faq-contact-icon"></i></div>
        <h2>Vous avez d’autres questions ?</h2>
        <p>Contactez-nous, notre équipe se fera un plaisir de vous aider.</p>
        <a href="../pages/aide.php" class="btn-beige">Nous contacter</a>
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
