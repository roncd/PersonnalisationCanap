<?php
require '../../admin/config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Produits</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link rel="stylesheet" href="../../styles/catalogue-prefait.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/header.css">
    <link href="../../dist/output.css" rel="stylesheet">
</head>

<body class="be-vietnam-pro-regular">
    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>


        <section class="hero-section">
            <div class="hero-container">
                <img src="../../medias/salon-marocain.jpg" alt="Salon marocain" class="hero-image">
                <div class="hero-content">
                    <br><br><br>
                    <h1 class="hero-title h2">
                        Nos Canapés Pré-Personnaliser
                    </h1>
                    <p class="hero-description">
                        Découvrez notre sélection de mousses et tissus de qualité. 
                Ces produits sont disponibles uniquement sur réservation et pick-up en boutique !
                    </p>
                </div>
            </div>
        </section>

    <main class="products-container">
        <!-- Filtres -->
        <div class="filters">
             <button class="filter-btn active" data-category="all">Tous</button>
             <button class="filter-btn" data-category="mousse">Bois</button>
             <button class="filter-btn" data-category="tissu">Tissus</button>
             
        </div>

        <!-- Grille de produits -->
        <div class="products-grid">
            <!-- Mousses -->
            <div class="product-card" data-category="mousse">
                <img src="../../medias/mousse-produit.jpg" alt="Mousse haute densité">
                <div class="product-info">
                    <h3>Mousse Haute Densité</h3>
                    <p>Idéale pour l'assise, densité 35kg/m³</p>
                    <p class="price">25€/m²</p>
                    <button class="btn-beige" onclick="openReservationModal('Mousse Haute Densité')">Personnaliser</button>
                </div>
            </div>

            <div class="product-card" data-category="mousse">
                <img src="../../medias/mousse-produit.jpg" alt="Mousse moyenne densité">
                <div class="product-info">
                    <h3>Mousse Medium</h3>
                    <p>Pour dossier, densité 25kg/m³</p>
                    <p class="price">20€/m²</p>
                    <button class="btn-beige" onclick="openReservationModal('Mousse Medium')">Personnaliser</button>
                </div>
            </div>

               <div class="product-card" data-category="mousse">
                <img src="../../medias/mousse-produit.jpg" alt="Mousse moyenne densité">
                <div class="product-info">
                    <h3>Mousse Soft</h3>
                    <p>Pour le couché, densité  20kg/m³</p>
                    <p class="price">18€/m²</p>
                    <button class="btn-beige"  onclick="openReservationModal('Mousse Soft')">Personnaliser</button>
                </div>
            </div>

            <!-- Tissus -->
            <div class="product-card" data-category="tissu">
                <img src="../../medias/velours-produit.jpg" alt="Tissu velours">
                <div class="product-info">
                    <h3>Velours Premium</h3>
                    <p>Velours doux et résistant</p>
                    <p class="price">35€/m²</p>
                    <button class="btn-beige"  onclick="openReservationModal('Velours Premium')">Personnaliser</button>
                </div>
            </div>

            <div class="product-card" data-category="tissu">
                <img src="../../medias/salon-marocain.jpg" alt="Tissu coton">
                <div class="product-info">
                    <h3>Coton Traditionnel</h3>
                    <p>Coton tissé traditionnel marocain</p>
                    <p class="price">1750 €</p>
                    <button class="btn-beige"  onclick="openReservationModal('Coton Traditionnel')">Personnaliser</button>
                </div>
            </div>

            <!-- Section Coussins-->
    <div class="product-card" data-category="accessoire">
        <img src="../../medias/canapekenitra.png" alt="Coussin 40x40">
        <div class="product-info">
            <h3>Coussin 40x40</h3>
            <p>Petit coussin décoratif ou de conforts</p>
            <p class="price">15€ l'unité</p>
            <button class="btn-beige"  onclick="openReservationModal('Coussin 40x40')">Personnaliser</button>
        </div>
    </div>

    <div class="product-card" data-category="accessoire">
        <img src="../../medias/caccoudoirs-produits.jpg" alt="Traversin">
        <div class="product-info">
            <h3>Traversin</h3>
            <p>Coussin cylindrique pour accoudoirs ou dossiers</p>
            <p class="price">20€ l'unité</p>
            <button class="btn-beige"  onclick="openReservationModal('Traversin')">Personnaliser</button>
        </div>
    </div>

        </div>

        <!-- Modal de réservation -->
        <div id="reservation-modal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2>Personnaliser un canapé déjà fait</h2>
                <form id="reservation-form" action="process-reservation.php" method="POST">
                    <input type="hidden" id="product-name" name="product">
                    <div class="form-group">
                        <label for="quantity">Quantité (en m²)</label>
                        <input type="number" id="quantity" name="quantity" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Nom</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Téléphone</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <button type="submit" class="submit-btn">Envoyer la demande</button>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <?php require '../../squelette/footer.php'; ?>
    </footer>

    <script>
        // Filtrage des produits
        const filterBtns = document.querySelectorAll('.filter-btn');
        const products = document.querySelectorAll('.product-card');

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Gestion active state
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                // Filtrage
                const category = btn.dataset.category;
                products.forEach(product => {
                    if (category === 'all' || product.dataset.category === category) {
                        product.style.display = 'block';
                    } else {
                        product.style.display = 'none';
                    }
                });
            });
        });

        // Gestion du modal
        const modal = document.getElementById('reservation-modal');
        const productNameInput = document.getElementById('product-name');

        function openReservationModal(productName) {
            modal.style.display = 'block';
            productNameInput.value = productName;
        }

        document.querySelector('.close-modal').onclick = () => {
            modal.style.display = 'none';
        }

        window.onclick = (event) => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>