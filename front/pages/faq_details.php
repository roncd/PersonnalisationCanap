<?php
require '../../admin/config.php';
session_start();
require '../../admin/include/session_expiration.php';

$categorie_id = $_GET['categorie'] ?? null;

if (!$categorie_id) {
  header("Location: ../pages/categories_faq.php");
  exit();
}

// Récupère le nom de la catégorie
$stmtCat = $pdo->prepare("SELECT nom FROM faq_categorie WHERE id = ?");
$stmtCat->execute([$categorie_id]);
$categorie = $stmtCat->fetch(PDO::FETCH_ASSOC);

// Si la catégorie n’existe pas
if (!$categorie) {
  header("Location: ../pages/categories_faq.php");
  exit();
}

// Récupère toutes les questions associées à cette catégorie
$stmtFaq = $pdo->prepare("SELECT question, reponse FROM faq WHERE categorie_id = ? AND visible = 1 ORDER BY id ASC");
$stmtFaq->execute([$categorie_id]);
$questions = $stmtFaq->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Tu as des questions sur la livraison, les moyens de paiement, les produits, le processuss de personnalisation, la garantit... Ici tu trouveras tes réponses." />
  <link rel="icon" type="image/png" href="https://www.decorient.fr/medias/favicon.png">
  <link rel="stylesheet" href="../../styles/transition.css">
  <link rel="stylesheet" href="../../styles/buttons.css">
  <script type="module" src="../../script/transition.js"></script>
  <link rel="stylesheet" href="../../styles/categories_faq.css">
  <title>FAQ - <?= htmlspecialchars($categorie['nom']) ?> </title>
</head>

<body>

  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <main class="faq-page transition-all">
    <div class="faq-page-container">
      <a href="../pages/faq.php" class="btn-beige retour">Retour</a>
      <h1><?= htmlspecialchars($categorie['nom']) ?></h1>
      <input type="text" id="faq-search" placeholder="Rechercher une question..." class="faq-search">

      <!-- Liste des questions dynamiques -->
      <div class="faq" id="faq-list">
        <?php if (count($questions) > 0): ?>
          <?php foreach ($questions as $faq): ?>
            <div class="accordeon-item">
              <button class="accordeon-header">
                <span class="accordeon-title"><?= htmlspecialchars($faq['question']) ?></span>
                <span class="accordeon-icon">+</span>
              </button>
              <div class="accordeon-content">
                <p><?= nl2br(htmlspecialchars($faq['reponse'])) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="text-align:center; font-weight:500; color:#a4745a;">Aucune question trouvée pour cette catégorie.</p>
        <?php endif; ?>
      </div>
  </main>

  <?php include '../../squelette/footer.php'; ?>

  <script src="../../script/accordeon.js"></script>

</body>

</html>