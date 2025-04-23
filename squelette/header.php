<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../../styles/header.css">
  <title>Header</title>
</head>

<body>
  <header>
    <script>
      document.querySelectorAll('.dropdown').forEach(function(dropdown) {
        dropdown.addEventListener('mouseover', function() {
          this.querySelector('.dropdown-menu').style.display = 'block';
        });
        dropdown.addEventListener('mouseout', function() {
          this.querySelector('.dropdown-menu').style.display = 'none';
        });
      });
    </script>
    <!-- Logo à gauche -->
    <div class="logo">
      <a href="../pages/index.php"><img src="../../medias/logo_trasparent-decodumonde.png" alt="Logo Decodumonde"></a>
    </div>

    <!-- Menu à droite -->
    <nav>
      <ul>
        <?php
        // Déterminez la page actuelle
        $currentPage = basename($_SERVER['REQUEST_URI']);
        ?>
        <li><a href="../pages/index.php" class="<?= $currentPage == 'index.php' ? 'active' : '' ?>">Accueil</a></li>
        <li><a href="../pages/dashboard.php" class="<?=
                                                    strpos($_SERVER['REQUEST_URI'], 'EtapesPersonnalisation') !== false ||
                                                      strpos($_SERVER['REQUEST_URI'], 'dashboard.php') !== false  ? 'active' : '' ?>">Personalisation</a></li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle <?=
                                              strpos($_SERVER['REQUEST_URI'], 'information.php') !== false ||
                                                strpos($_SERVER['REQUEST_URI'], 'commandes.php') !== false  ? 'active' : '' ?>">Mon compte</a>
          <ul class="dropdown-menu">
            <div class="drop">
              <li><a href="../pages/commandes.php">Mes commandes</a></li>
              <li><a href="../pages/information.php">Mes informations</a></li>
            </div>
          </ul>
        </li>
        <li><a href="../pages/aide.php" class="<?= $currentPage == 'aide.php' ? 'active' : '' ?>">Besoin d'aides ?</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
          <li><a href="../formulaire/logout.php">Déconnexion</a></li>
        <?php else: ?>
          <li><a href="../formulaire/Connexion.php">Connexion</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </header>

</body>

</html>