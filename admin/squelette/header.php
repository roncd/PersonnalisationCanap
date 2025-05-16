<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/squelette-admin.css">
  <script type="module" src="../../script/header-admin.js"></script>
</head>

<body>
  <?php
  // Déterminez la page actuelle
  $currentPage = basename($_SERVER['REQUEST_URI']);
  $currentPath = $_SERVER['REQUEST_URI'];
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
        <a href="../client/index.php" class="menu-link <?= strpos($currentPath, '/client/index.php') !== false ? 'active' : '' ?>" data-icon="client">
          <img src="../../assets/menu/client.svg" alt="" width="20" height="20">
          <span>Clients</span>
        </a>
        <a href="../pages/commande.php" class="menu-link <?= $currentPage == 'commande.php' ? 'active' : '' ?>" data-icon="commande">
          <img src="../../assets/menu/commande.svg" alt="" width="20" height="20">
          <span>Commandes</span>
        </a>

        <div class="menu-group">
          <a href="#" class="menu-link catalogue <?= ($currentPage == 'visualiser.php' || $currentPage == 'ajouter.php') ? 'active' : '' ?>" data-icon="catalogue">
            <img src="../../assets/menu/catalogue.svg" alt="" width="20" height="20">
            <span>Catalogue
            <span class="arrow">▾</span>
          </a>
          <div class="submenu">
            <a href="../pages/ajouter.php" class="<?= $currentPage == 'ajouter.php' ? 'active' : '' ?>">Ajouter des options</a>
            <a href="../pages/visualiser.php" class="<?= $currentPage == 'visualiser.php' ? 'active' : '' ?>">Visualiser des options</a>
          </div>
        </div>

      </nav>

      <nav class="space-nav">
        <span><strong>PARAMÈTRES</strong></span>
        <a href="../utilisateur/index.php" class="menu-link <?= strpos($currentPath, '/utilisateur/index.php') !== false ? 'active' : '' ?>" data-icon="equipe">
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