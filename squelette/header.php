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
        <!-- Bouton hamburger -->
        <button class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Logo à gauche -->
        <div class="logo">
            <a href="../pages/index.php">
                <img src="../../medias/logo_trasparent-decodumonde.png" alt="Logo Decodumonde">
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
                       class="dropdown-toggle <?= strpos($_SERVER['REQUEST_URI'], 'information.php') !== false ||
                                               strpos($_SERVER['REQUEST_URI'], 'commandes.php') !== false ? 'active' : '' ?>">
                        Mon compte
                    </a>
                    <ul class="dropdown-menu">
                        <div class="drop">
                            <li><a href="../pages/commandes.php">Mes commandes</a></li>
                            <li><a href="../pages/information.php">Mes informations</a></li>
                        </div>
                    </ul>
                    </li>
                                <li>
                    <a href="../pages/apropos.php"
                    class="<?= $currentPage == 'apropos.php' ? 'active' : '' ?>">
                        À propos
                    </a>
                </li>
                <li>
                    <a href="../pages/aide.php" 
                       class="<?= $currentPage == 'aide.php' ? 'active' : '' ?>">
                        Besoin d'aides ?
                    </a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="../formulaire/logout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="../formulaire/Connexion.php">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- Script pour le menu mobile et les dropdowns -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const hamburger = document.querySelector('.hamburger');
            const nav = document.querySelector('nav');
            const dropdowns = document.querySelectorAll('.dropdown');

            // Gestion du menu hamburger
            hamburger.addEventListener('click', () => {
                hamburger.classList.toggle('active');
                nav.classList.toggle('active');
                document.body.classList.toggle('menu-open');
            });

            // Gestion des dropdowns
            dropdowns.forEach(dropdown => {
                dropdown.addEventListener('click', (e) => {
                    if (window.innerWidth <= 768) {
                        e.preventDefault();
                        e.stopPropagation(); // Empêche les clics de se propager
                        dropdown.classList.toggle('active');
                    }
                });

                // Gestion hover pour desktop
                if (window.innerWidth > 768) {
                    dropdown.addEventListener('mouseover', () => {
                        const dropdownMenu = dropdown.querySelector('.dropdown-menu');
                        if (dropdownMenu) {
                            dropdownMenu.style.display = 'block';
                        }
                    });
                    dropdown.addEventListener('mouseout', () => {
                        const dropdownMenu = dropdown.querySelector('.dropdown-menu');
                        if (dropdownMenu) {
                            dropdownMenu.style.display = 'none';
                        }
                    });
                }
            });

            // Fermer le menu en cliquant en dehors
            document.addEventListener('click', (e) => {
                if (!nav.contains(e.target) && !hamburger.contains(e.target)) {
                    nav.classList.remove('active');
                    hamburger.classList.remove('active');
                    document.body.classList.remove('menu-open');

                    // Fermer les dropdowns actifs
                    dropdowns.forEach(dropdown => dropdown.classList.remove('active'));
                }
            });
        });
    </script>
</body>

</html>