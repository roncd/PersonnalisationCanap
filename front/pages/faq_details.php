<?php
require '../../admin/config.php';
session_start();

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
$stmtFaq = $pdo->prepare("SELECT question, reponse FROM faq WHERE categorie_id = ? ORDER BY id ASC");
$stmtFaq->execute([$categorie_id]);
$questions = $stmtFaq->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/x-icon" href="../../../medias/favicon.png">
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
      <!--<h1>Livraison ici on met le nom de la categorie</h1>

       Barre de recherche
      <input type="text" id="faq-search" placeholder="Rechercher une question..." class="faq-search">
 et tu rend la liste de questoin dynamique en fonction des des categorie genre 
      Liste des questions
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



      Message aucun résultat
      <p id="no-results" style="display:none; text-align:center; color:#a4745a; font-weight:500;">
        Aucune question ne correspond à votre recherche.
      </p>
    </div>
     -->
  </main>

  <?php include '../../squelette/footer.php'; ?>

  <script src="../../script/accordeon.js"></script>

</body>
</html>
