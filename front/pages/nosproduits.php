<?php
require '../../admin/config.php';
session_start();

// Requête pour récupérer tous les produits avec le nom de leur catégorie
$sql = "
    SELECT vente_produit.*, categorie.nom AS nom_categorie 
    FROM vente_produit 
    JOIN categorie ON vente_produit.id_categorie = categorie.id
";
$stmt = $pdo->query($sql);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Requête pour récupérer toutes les catégories
$sql = "SELECT * FROM categorie";
$stmt = $pdo->query($sql);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Produits</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link rel="stylesheet" href="../../styles/catalogue.css">
    <link rel="stylesheet" href="../../styles/buttons.css">

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
                    Nos Produits
                </h1>

                <p class="hero-description">
                 Découvrez notre sélection de mousses et tissus de qualité. 
                Ces produits sont disponibles uniquement sur réservation et pick-up en boutique.
                </p>
            </div>
        </div>
    </section>


    <main class="products-container">

        <!-- Filtres -->
<div class="filters">
    <button class="filter-btn active" data-category="all">Tous</button>
    <?php foreach ($categories as $cat): ?>
        <button class="filter-btn" data-category="<?= htmlspecialchars(strtolower($cat['nom'])) ?>">
            <?= htmlspecialchars($cat['nom']) ?>
        </button>
    <?php endforeach; ?>
</div>



<!-- Grille de produits -->
<div class="products-grid">
    <?php foreach ($produits as $produit): ?>
        <div class="product-card" data-category="<?= htmlspecialchars($produit['nom_categorie']) ?>">
            <img src="<?= htmlspecialchars($produit['img']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>">
            <div class="product-info">
                <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                <p class="price"><?= htmlspecialchars($produit['prix']) ?>€</p>
                <button class="btn-beige" onclick="openReservationModal('<?= htmlspecialchars($produit['nom']) ?>')">Ajouter au panier</button>
            </div>
        </div>
    <?php endforeach; ?>
</div>
        </div>


<!-- Modal d'ajout au panier -->
<div id="reservation-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <img src="../../assets/check-icone.svg" alt="Image du produit" class="check-icon">
        <br>
        <h2 class ="success-message" > Ajouté Au panier avec succès !</h2>
        <div class="product-info">
            <img src="../../medias/canapekenitra.png" alt="Image du panier" class="img-panier">
            <p id="product-name">Nom du produit :</p>
            <p>Quantité : <span id="quantity"></span></p>
        </div>
        <div class="modal-buttons">
           <button class="ajt-panier" onclick="fermerModal()">Continuer vos achats</button>
            <button class="btn-noir" onclick="window.location.href='panier.html'">Voir le panier</button>
        </div>
    </div>
</div>

   </main>

    <footer>
        <?php require '../../squelette/footer.php'; ?>
    </footer>
    

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const filterButtons = document.querySelectorAll(".filter-btn");
        const productCards = document.querySelectorAll(".product-card");

        filterButtons.forEach(button => {
            button.addEventListener("click", () => {
                // Récupère la catégorie cliquée
                const selectedCategory = button.getAttribute("data-category").toLowerCase();

                // Met à jour les classes actives
                filterButtons.forEach(btn => btn.classList.remove("active"));
                button.classList.add("active");

                // Affiche ou masque les produits selon la catégorie
                productCards.forEach(card => {
                    const cardCategory = card.getAttribute("data-category").toLowerCase();

                    if (selectedCategory === "all" || cardCategory === selectedCategory) {
                        card.style.display = "block";
                    } else {
                        card.style.display = "none";
                    }
                });
            });
        });
    });
</script>


    <script>
const modal = document.getElementById('reservation-modal');
const productNameEl = document.getElementById('product-name');

function openReservationModal(productName) {
    modal.style.display = 'flex'; // Affiche la modale
    productNameEl.textContent = `Nom du produit : ${productName}`;
    document.documentElement.classList.add('no-scroll'); // <== html
    document.body.classList.add('no-scroll'); // <== body
}

function fermerModal() {
    modal.style.display = 'none';
    document.documentElement.classList.remove('no-scroll');
    document.body.classList.remove('no-scroll');
}


document.querySelector('.close-modal').onclick = fermerModal;

window.onclick = (event) => {
    if (event.target === modal) {
        fermerModal();
    }
};

    </script>
</body>



</html>
