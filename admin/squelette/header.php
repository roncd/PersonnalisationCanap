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
      /* Positionne le soulignement juste en dessous du texte */
      width: 0;
      height: 1px;
      background-color: #000000;
      transition: width 0.3s ease;
    }

    nav ul li a:hover::after {
      width: 100%;
      /* Le soulignement prend toute la largeur */
    }

    /* Style pour le lien actif */
    nav ul li a.active::after {
      width: 100%;
      /* Le soulignement reste visible sur l'élément actif */
    }


    nav {
      margin-right: 90px;
      /* Décaler le menu vers la gauche */
    }
  </style>

  <header>
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
        <li><a href="../pages/index.php" class="<?= $currentPage == 'index.php' ? 'active' : '' ?>">Base de données</a></li>
        <li><a href="../pages/ajouter.php" class="<?= $currentPage == 'ajouter.php' ? 'active' : '' ?>">Ajouter</a></li>
        <li><a href="../pages/commande.php" class="<?= $currentPage == 'commande.php' ? 'active' : '' ?>">Commandes</a></li>
        <li><a href="../../front/pages/index.php" target="_blank">Voir le site</a></li>
        <li><a href="../squelette/logout.php">Déconnexion</a></li>
      </ul>
    </nav>
  </header>

</body>

</html>