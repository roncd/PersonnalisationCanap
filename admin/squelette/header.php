<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/admin/squelette-admin.css">
  <script type="module" src="../../script/header-admin.js"></script>
</head>

<body>
  <?php
  $currentPage = basename($_SERVER['REQUEST_URI']);
  $currentPath = $_SERVER['REQUEST_URI'];

  // Active si on est dans ajouter, editer ou visualiser hors client/equipe
  $isCataloguePage = (
    strpos($currentPage, 'add.php') !== false &&
    strpos($currentPath, '/client/') === false &&
    strpos($currentPath, '/utilisateur/') === false ||

    strpos($currentPage, 'visualiser.php') !== false &&
    strpos($currentPath, '/client/') === false &&
    strpos($currentPath, '/utilisateur/') === false ||

    strpos($currentPage, 'edit.php') !== false &&
    strpos($currentPath, '/client/') === false &&
    strpos($currentPath, '/utilisateur/') === false
  );

  // Cas spécifiques pour les autres menus
  $isClientPage = strpos($currentPath, '/client/') !== false;
  $isEquipePage = strpos($currentPath, '/utilisateur/') !== false;

  $isSitePage = (
    strpos($currentPage, 'add-site.php') !== false &&
    strpos($currentPath, '/client/') === false &&
    strpos($currentPath, '/utilisateur/') === false ||

    strpos($currentPage, 'visualiser-site.php') !== false &&
    strpos($currentPath, '/client/') === false &&
    strpos($currentPath, '/utilisateur/') === false ||

    strpos($currentPage, 'edit-site.php') !== false &&
    strpos($currentPath, '/client/') === false &&
    strpos($currentPath, '/utilisateur/') === false || 

    strpos($currentPage, 'site.php') !== false &&
    strpos($currentPath, '/client/') === false &&
    strpos($currentPath, '/utilisateur/') === false   
);
  ?>
  <aside>
    <div class="menu-section">
      <div class="close-icone">
        <img src="../../assets/menu/closeMenu.svg" alt="" width="20" height="20">
      </div>
      <nav>
        <a href="../pages/index.php" class="menu-link <?= strpos($currentPath, '/pages/index.php') !== false ? 'active' : '' ?>" data-icon="dashboard">
          <img src="../../assets/menu/dashboard.svg" alt="" width="20" height="20">
          <span>Tableau de bord</span>
        </a>
      </nav>
      <nav class="space-nav">
        <span><strong>ADMINISTRATION</strong></span>

        <a href="../pages/visualiser.php" class="menu-link catalogue <?= $isCataloguePage ? 'active' : '' ?>" data-icon="catalogue">
          <img src="../../assets/menu/catalogue.svg" alt="" width="20" height="20">
          <span>Catalogue</span>
        </a>

        <a href="../client/visualiser.php" class="menu-link <?= (strpos($currentPage, 'fiche-client') !== false || $isClientPage  || strpos($currentPath, '/commande-detail/index.php') !== false)  ? 'active' : '' ?>" data-icon="client">
          <img src="../../assets/menu/client.svg" alt="" width="20" height="20">
          <span>Clients</span>
        </a>

        <div class="menu-group">
          <a href="#" class="menu-link commande-link <?= strpos($currentPage, 'commande.php') !== false  || strpos($currentPage, 'panier.php') !== false  ? 'active' : '' ?>" data-icon="commande">
            <img src="../../assets/menu/commande.svg" alt="" width="20" height="20">
            <span>Commandes</span>
            <span class="arrow">▾</span>
          </a>
          <div class="submenu">
            <a href="../pages/commande.php" class="<?= strpos($currentPage, 'commande.php') !== false ? 'active' : '' ?>">Canapé marocain</a>
            <a href="../pages/panier.php" class="<?= strpos($currentPage, 'panier.php') !== false ? 'active' : '' ?>">Panier</a>
          </div>
        </div>

      </nav>

      <nav class="space-nav">
        <span><strong>SITE WEB</strong></span>
        <a href="../pages/site.php" class="menu-link <?= $isSitePage ? 'active' : '' ?>" data-icon="website">
          <img src="../../assets/menu/website.svg" alt="" width="20" height="20"> 
          <span>Pages</span>
        </a>
      </nav>

      

      <nav class="space-nav">
        <span><strong>PARAMÈTRES</strong></span>
        <a href="../utilisateur/visualiser.php" class="menu-link <?= $isEquipePage ? 'active' : '' ?>" data-icon="equipe">
          <img src="../../assets/menu/equipe.svg" alt="" width="20" height="20">
          <span>Équipe</span>
        </a>
        <a href="../pages/account.php" class="menu-link <?= $currentPage == 'account.php' ? 'active' : '' ?>" data-icon="account">
          <img src="../../assets/menu/account.svg" alt="" width="20" height="20">
          <span>Mon compte</span>
        </a>
      </nav>
      <nav class="space-nav">
        <a href="../include/export_bdd.php" class="menu-link" data-icon="download">
          <img src="../../assets/menu/download.svg" alt="" width="20" height="20">
          <span>Télécharger la base de données</span>
        </a>
        <a href="../include/logout.php" class="menu-link" data-icon="logout">
          <img src="../../assets/menu/logout.svg" alt="" width="20" height="20">
          <span>Déconnexion</span>
        </a>
      </nav>
    </div>
  </aside>

  <div class="head">
    <a href="../../front/pages/index.php" target="_blank">
      <span>Voir le site</span>
      <img src="../../assets/extern-link.svg" alt="" width="13" height="13">
    </a>
  </div>


</html>