<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Header</title>
</head>

<body>
  <style>
    /* Importation de la police */
    @import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@700&display=swap');

    /* Supprime les marges et le padding par défaut */
    html,
    body {
      margin: 0;
      padding: 0;
    }

    /* Style de base pour le header */
    header {
      width: 100%;
      /* Prend toute la largeur de la page */
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-bottom: -20px;
      /* Ajustez le padding pour la hauteur souhaitée */
      background-color: #E3D1C8;
      box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* Style pour le logo */
    .logo {
      font-size: 10px;
      font-weight: bold;
      font-family: 'Be Vietnam Pro', sans-serif;
    }

    /* Redimensionner l'image du logo */
    .logo img {
      width: 150px;
      /* Ajustez la taille selon vos besoins */
      height: auto;
    }

    /* Style pour le menu */
    nav ul {
      list-style-type: none;
      margin: 0;
      padding: 0;
      display: flex;
    }

    /* Espacement des éléments du menu */
    nav ul li {
      margin-left: 70px;
    }

    /* Style pour les liens du menu */
    nav ul li a {
      color: #000000;
      text-decoration: none;
      font-family: 'Be Vietnam Pro', sans-serif;
      font-weight: 700;
      font-size: 14px;
      position: relative;
    }

    nav ul li a::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: -2px;
      width: 0;
      height: 1px;
      background-color: #000000;
      transition: width 0.3s ease;
    }

    nav ul li a:hover::after {
      width: 100%;
    }

    /* Style pour le lien actif */
    nav ul li a.active::after {
      width: 100%;
    }

    nav {
      margin-right: 90px;
    }

    .dropdown-menu {
      display: none;
      /* Masquer le menu déroulant par défaut */
      position: absolute;
      /* Positionné par rapport au parent */
      list-style-type: none;
      margin: 0;
      padding: 0;
      z-index: 1000;
    }

    .dropdown-menu .drop {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.2);
      margin: 15px 0px 0px 0px;
    }

    .dropdown-menu li {
      border-bottom: 15px;
      padding: 15px 30px 15px 0px;
      margin-left: 5px;

    }

    .dropdown-menu li:last-child {
      border-bottom: none;
    }

    .dropdown-menu a {
      color: #000000;
      text-decoration: none;
      font-family: 'Be Vietnam Pro', sans-serif;
      font-weight: 700;
      font-size: 14px;
      position: relative;
      display: block;
    }

    .dropdown-menu a::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: -2px;
      width: 0;
      height: 1px;
      background-color: #000000;
      transition: width 0.3s ease;
    }

    .dropdown-menu a:hover::after {
      width: 100%;
    }

    nav ul li.dropdown:hover>.dropdown-menu {
      display: block !important;
    }
  </style>

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