<?php
require_once '../../admin/config.php';

$nombreArticles = 0;

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT SUM(quantite) as total 
        FROM panier_detail pd
        JOIN panier p ON pd.id_panier = p.id
        WHERE p.id_client = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $nombreArticles = $result['total'] ?? 0;
}
?>


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
        <div class="header-content">

        <!-- Bouton hamburger -->
        <button class="hamburger close-btn">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Logo à gauche -->
        <div class="logo">
            <a href="../pages/index.php">
                <img src="../../medias/logo_blanc-decodumonde.png" alt="Logo Decodumonde">
            </a>
        </div>

        <!-- Menu à droite -->
        <nav>
            <ul>
                <?php
                // Déterminez la page actuelle
                $currentPage = basename($_SERVER['REQUEST_URI']);
                ?>
                <li>
                    <a href="../pages/index.php" 
                       class="<?= $currentPage == 'index.php' ? 'active' : '' ?>">
                        Accueil
                    </a>
                </li>
                <li>
                    <a href="../pages/dashboard.php" 
                       class="<?= strpos($_SERVER['REQUEST_URI'], 'EtapesPersonnalisation') !== false ||
                                strpos($_SERVER['REQUEST_URI'], 'dashboard.php') !== false ? 'active' : '' ?>">
                        Personalisation
                    </a>
                </li>
                                <li class="dropdown">
                    <a href="#" 
                       class="dropdown-toggle <?= strpos($_SERVER['REQUEST_URI'], 'noscanapes.php') !== false ||
                                               strpos($_SERVER['REQUEST_URI'], 'nosproduits.php') !== false ? 'active' : '' ?>">
                        Catalogue
                    </a>
                    <ul class="dropdown-menu">
                        <div class="drop">
                            <li><a href="../pages/noscanapes.php">Nos Canapés</a></li>
                            <li><a href="../pages/nosproduits.php">Nos Produits</a></li>

                        </div>
                    </ul>
                </li>
                <li>
                    <a href="../pages/apropos.php" 
                       class="<?= $currentPage == 'apropos.php' ? 'active' : '' ?>">
                        À Propos
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" 
                       class="dropdown-toggle <?= strpos($_SERVER['REQUEST_URI'], 'information.php') !== false ||
                                               strpos($_SERVER['REQUEST_URI'], 'commandes.php') !== false ? 'active' : '' ?>">
                        Mon Compte
                    </a>
                    <ul class="dropdown-menu">
                        <div class="drop">     
                            <li><a href="../pages/commandes.php">Suivis commandes</a></li>
                            <li><a href="../pages/information.php">Mes informations</a></li>
                            <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="../formulaire/logout.php">Déconnexion</a></li>
                            <?php else: ?>
                            <li><a href="../formulaire/Connexion.php">Connexion </a></li>
                            <?php endif; ?>       
                        </div>
                    </ul>
                </li>
                <li>
                    <a href="../pages/aide.php" 
                       class="<?= $currentPage == 'aide.php' ? 'active' : '' ?>">
                        Besoin d'aides ?
                    </a>
                </li>
            </ul>
        </nav>

          <!-- Panier à droite -->
        <div class="panier">
    <a href="../pages/panier.php" style="position: relative; display: inline-block;">
        <img src="../../assets/icone-panier.svg" alt="Panier">
        <?php if ($nombreArticles > 0): ?>
            <span class="panier-count"><?= $nombreArticles ?></span>
        <?php endif; ?>
    </a>
</div>
         
    </div>
    </header>

    <script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".dropdown > a").forEach((dropdownToggle) => {
        dropdownToggle.addEventListener("click", function (event) {
            event.preventDefault(); // Empêcher le changement de page
            event.stopPropagation(); // Empêche la fermeture immédiate
            let parentDropdown = this.parentElement;
            parentDropdown.classList.toggle("active");
        });
    });

    // Fermer le dropdown si on clique ailleurs
    document.addEventListener("click", function (event) {
        document.querySelectorAll(".dropdown").forEach((dropdown) => {
            if (!dropdown.contains(event.target)) {
                dropdown.classList.remove("active");
            }
        });
    });
});
    </script>

<script>
    window.addEventListener('scroll', () => {
        const header = document.querySelector('.header-content');
        const scrollY = window.scrollY;

        // Si on scrolle au moins de 40 pixels (~0.3 cm), on rend translucide
        if (scrollY > 30) {
            header.style.backgroundColor = 'rgba(153, 119, 101, 0.8)';
        } else {
            header.style.backgroundColor = 'rgba(153, 119, 101, 1)';
        }
    });
</script>



    <!-- Script pour le menu mobile  -->
    <script>
document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.querySelector('.hamburger');
    const nav = document.querySelector('nav');

    // Gestion du menu hamburger
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        nav.classList.toggle('active');
        document.body.classList.toggle('menu-open');
    });

    // Fermer le menu en cliquant en dehors
    document.addEventListener('click', (e) => {
        if (!nav.contains(e.target) && !hamburger.contains(e.target)) {
            nav.classList.remove('active');
            hamburger.classList.remove('active');
            document.body.classList.remove('menu-open');
        }
    });
});
    </script>
</body>
</html>