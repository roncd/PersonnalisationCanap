<?php
require '../../admin/config.php';
session_start();
$sql = "SELECT cp.*, tb.nom as type_nom 
        FROM commande_prefait cp
        LEFT JOIN type_banquette tb ON cp.type = tb.id";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Canapés</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link rel="stylesheet" href="../../styles/catalogue-prefait.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
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
                        Nos Canapés Pré-Personnaliser !   
                    </h1>
                   
                    <p class="hero-description">   
                    Nos canapés marocains pré-personnalisés allient tradition et modernité pour sublimer votre salon.
Choisissez votre style, réservez votre modèle et récupérez-le directement en boutique.
                    </p>
                </div>
            </div>
        </section>

    <main class="products-container">
        <!-- Filtres -->
        <div class="filters">
             <button class="filter-btn active" data-category="all">Tous</button>
             <button class="filter-btn" data-category="bois">Bois</button>
             <button class="filter-btn" data-category="tissu">Tissus</button>
             
        </div>

 <div class="products-grid">
    <?php foreach ($commandes as $commande): ?>
        <div class="product-card" data-type="<?php echo htmlspecialchars($commande['type_nom'] ?? 'inconnu', ENT_QUOTES); ?>">
            <img 
                src="../../admin/uploads/canape-prefait/<?php echo htmlspecialchars($commande['img'] ?? 'default.jpg', ENT_QUOTES); ?>" 
                alt="<?php echo htmlspecialchars($commande['nom'] ?? 'Canapé préfait', ENT_QUOTES); ?>"
            >
            <div class="product-info">
                <h3><?php echo htmlspecialchars($commande['nom'] ?? 'Nom non disponible', ENT_QUOTES); ?></h3>
                <p class="price"><?php echo htmlspecialchars($commande['prix'] ?? '0', ENT_QUOTES); ?> €</p>
                <button class="btn-beige" onclick="openReservationModal('<?php echo htmlspecialchars($commande['nom'] ?? 'Produit', ENT_QUOTES); ?>')">
                    Personnaliser
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>


    </main>

    <footer>
        <?php require '../../squelette/footer.php'; ?>
    </footer>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const products = document.querySelectorAll('.product-card');

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Enlever la classe active de tous les boutons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Ajouter la classe active au bouton cliqué
            button.classList.add('active');

            const category = button.getAttribute('data-category');

            products.forEach(product => {
                if (category === 'all') {
                    product.style.display = 'block';
                } else {
                    // Récupère la data-type du produit
                    const type = product.getAttribute('data-type').toLowerCase();
                    if (type === category.toLowerCase()) {
                        product.style.display = 'block';
                    } else {
                        product.style.display = 'none';
                    }
                }
            });
        });
    });
});
</script>

</body>
</html>