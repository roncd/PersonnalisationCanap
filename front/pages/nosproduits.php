<?php
require '../../admin/config.php';
session_start();

$sql = "
    SELECT vente_produit.*, categorie.nom AS nom_categorie 
    FROM vente_produit 
    JOIN categorie ON vente_produit.id_categorie = categorie.id
    ORDER BY vente_produit.id DESC
";

$stmt = $pdo->query($sql);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Requête pour récupérer toutes les catégories
$sql = "SELECT * FROM categorie";
$stmt = $pdo->query($sql);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul du total du panier
$total = 0;
if (!empty($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $item) {
        $total += $item['prix'] * $item['quantite'];
    }
}

// Gestion de l'ajout au panier
$produitAjoute = null; // Variable pour savoir quel produit a été ajouté

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['produit'])) {
    $nomProduit = $_POST['produit'];
    $quantite = isset($_POST['quantite']) ? intval($_POST['quantite']) : 1;

    foreach ($produits as $produit) {
        if ($produit['nom'] === $nomProduit) {
            $id = $produit['id'];
            $prix = $produit['prix'];

            if (!isset($_SESSION['panier'])) {
                $_SESSION['panier'] = [];
            }

            $trouve = false;
            foreach ($_SESSION['panier'] as &$item) {
                if ($item['id'] === $id) {
                    $item['quantite'] += $quantite;
                    $trouve = true;
                    break;
                }
            }

            if (!$trouve) {
                $_SESSION['panier'][] = [
                    'id' => $id,
                    'nom' => $nomProduit,
                    'prix' => $prix,
                    'quantite' => $quantite
                ];
            }

            $produitAjoute = $nomProduit; // Indique le nom du produit ajouté
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Nos Produits</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png" />
    <link rel="stylesheet" href="../../styles/catalogue.css" />
    <link rel="stylesheet" href="../../styles/buttons.css" />
</head>

<body>
    <?php include '../cookies/index.html'; ?>
    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>

    <section class="hero-section">
        <div class="hero-container">
            <img src="../../medias/salon-marocain.jpg" alt="Salon marocain" class="hero-image" />
            <div class="hero-content">
                <br /><br /><br />
                <h1 class="hero-title h2">Nos Produits</h1>

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

        <!-- ------------------- BARRE DE RECHERCHE ------------------- -->
        <div class="search-bar" style="text-align:center; margin: 20px;">
          <input type="text" id="searchInput" placeholder="Rechercher un produit par nom par catégorie..." style="padding: 10px; width: 300px;">
        </div>

        <!-- ------------------- SECTION ARTICLES ASSOCIES ------------------- -->
        <section class="combination-section">
          <h2>Ces articles peuvent aussi vous intéresser</h2>
          <div class="combination-container">
            <?php foreach ($produits as $produit): ?>
              <div class="product-card" data-category="<?= htmlspecialchars($produit['nom_categorie']) ?>">
                <div class="product-image">
                  <img 
                    src="<?= htmlspecialchars($produit['img']) ?>" 
                    alt="<?= htmlspecialchars($produit['nom']) ?>" />
                </div>
                <div class="product-content">
                  <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                  <p class="description">Catégorie : <?= htmlspecialchars($produit['nom_categorie']) ?></p>
                  <p class="price"><?= number_format($produit['prix'], 2, ',', ' ') ?> €</p>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="produit" value="<?= htmlspecialchars($produit['nom']) ?>" />
                    <input type="hidden" name="quantite" value="1" />
                    <button type="submit" class="btn-beige">Ajouter au panier</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>

        <!-- Modal d'ajout au panier -->
        <div id="reservation-modal" class="modal" style="display:none;">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <img src="../../assets/check-icone.svg" alt="Image du produit" class="check-icon" />
                <br />
                <h2 class="success-message">Ajouté au panier avec succès !</h2>
                <div class="product-info">
                    <img src="../../medias/canapekenitra.png" alt="Image du panier" class="img-panier" />
                    <p id="product-name">Nom du produit :</p>
                    <p>
                        Quantité : <span id="quantity">1</span>
                    </p>
                </div>
                <div class="modal-buttons">
                    <button class="ajt-panier" onclick="fermerModal()">Continuer vos achats</button>
                    <button class="btn-noir" onclick="window.location.href='panier.php'">Voir le panier</button>
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

            filterButtons.forEach((button) => {
                button.addEventListener("click", () => {
                    const selectedCategory = button
                        .getAttribute("data-category")
                        .toLowerCase();

                    filterButtons.forEach((btn) => btn.classList.remove("active"));
                    button.classList.add("active");

                    productCards.forEach((card) => {
                        const cardCategory = card
                            .getAttribute("data-category")
                            .toLowerCase();

                        if (selectedCategory === "all" || cardCategory === selectedCategory) {
                            card.style.display = "block";
                        } else {
                            card.style.display = "none";
                        }
                    });
                });
            });
        });

        const modal = document.getElementById("reservation-modal");
        const productNameEl = document.getElementById("product-name");

        function openReservationModal(productName) {
            modal.style.display = "flex"; // Affiche la modale
            productNameEl.textContent = `Nom du produit : ${productName}`;
            document.documentElement.classList.add("no-scroll");
            document.body.classList.add("no-scroll");
        }

        function fermerModal() {
            modal.style.display = "none";
            document.documentElement.classList.remove("no-scroll");
            document.body.classList.remove("no-scroll");
        }

        document.querySelector(".close-modal").onclick = fermerModal;

        window.onclick = (event) => {
            if (event.target === modal) {
                fermerModal();
            }
        };
    </script>

    <?php if ($produitAjoute): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            openReservationModal("<?= addslashes($produitAjoute) ?>");
        });
    </script>
    <?php endif; ?>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const products = document.querySelectorAll('.product-card');

        searchInput.addEventListener('input', function () {
          const searchTerm = this.value.toLowerCase();

          products.forEach(product => {
            const productName = product.querySelector('h3').textContent.toLowerCase();

            // On récupère le texte de la catégorie et on enlève "Catégorie : " avant la recherche
            const productCategoryElement = product.querySelector('.description');
            const productCategory = productCategoryElement 
              ? productCategoryElement.textContent.toLowerCase().replace('catégorie : ', '').trim() 
              : '';

            if (productName.includes(searchTerm) || productCategory.includes(searchTerm)) {
              product.style.display = 'block';
            } else {
              product.style.display = 'none';
            }
          });
        });
      });
    </script>

</body>

</html>
