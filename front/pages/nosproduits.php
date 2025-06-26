<?php
require '../../admin/config.php';
session_start();

$sql = "
    SELECT vente_produit.*, categorie.nom AS nom_categorie 
    FROM vente_produit 
    JOIN categorie ON vente_produit.id_categorie = categorie.id
";

// Si une recherche est présente, on ajoute une clause WHERE
if (!empty($search)) {
    $sql .= " WHERE vente_produit.nom LIKE :search OR categorie.nom LIKE :search";
}

$sql .= " ORDER BY vente_produit.id DESC";

$stmt = $pdo->prepare($sql);


if (!empty($search)) {
    $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
}

$stmt->execute();
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);


$sql = "SELECT * FROM categorie";
$stmt = $pdo->query($sql);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gestion de l'ajout au panier
$produitAjoute = null; // Variable pour savoir quel produit a été ajouté

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['produit'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['pending_add_to_cart'] = $_POST;
        header('Location: ../formulaire/Connexion.php');
        exit;
    }

    if (isset($_SESSION['user_id'])) {
        $id_client = $_SESSION['user_id'];

        $nomProduit = $_POST['produit'];
        $quantite = intval($_POST['quantite'] ?? 1);
        // Récupérer l'ID et le prix du produit via son nom
        $stmt = $pdo->prepare("SELECT id, prix FROM vente_produit WHERE nom = ?");
        $stmt->execute([$nomProduit]);
        $produit = $stmt->fetch();

        if (!$produit) {
            // Sécurité : produit introuvable (mauvaise saisie ?)
            die("Produit introuvable.");
        }

        $id = $produit['id'];
        $prix = $produit['prix'];

        // Vérifie s'il y a déjà un panier pour ce client
        $stmt = $pdo->prepare("SELECT id FROM panier WHERE id_client = ?");
        $stmt->execute([$id_client]);
        $panier = $stmt->fetch();

        if (!$panier) {
            // Créer un nouveau panier
            $stmt = $pdo->prepare("INSERT INTO panier (id_client, prix) VALUES (?, ?)");
            $stmt->execute([$id_client, 0]);
            $panier_id = $pdo->lastInsertId();
        } else {
            $panier_id = $panier['id'];
        }

        // Vérifie si le produit est déjà dans le panier
        $stmt = $pdo->prepare("SELECT * FROM panier_detail WHERE id_panier = ? AND id_produit = ?");
        $stmt->execute([$panier_id, $id]);
        $produit_existe = $stmt->fetch();

        if ($produit_existe) {
            $stmt = $pdo->prepare("UPDATE panier_detail SET quantite = quantite + ? WHERE id_panier = ? AND id_produit = ?");
            $stmt->execute([$quantite, $panier_id, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO panier_detail (id_panier, id_produit, quantite) VALUES (?, ?, ?)");
            $stmt->execute([$panier_id, $id, $quantite]);
        }

        // Mettre à jour le prix total dans la table panier
        $stmt = $pdo->prepare("UPDATE panier SET prix = prix + ? WHERE id = ?");
        $stmt->execute([$prix * $quantite, $panier_id]);
        $produitAjoute = $nomProduit;
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

        <!-- ------------------- SECTION ARTICLES ASSOCIES ------------------- -->
        <section class="combination-section">
          <h2>Ces articles peuvent aussi vous intéresser</h2>

          <!------------ BOUTONS DE FILTRE PAR CATÉGORIE ----------->
<div class="filters">
    <button class="filter-btn active" data-category="all">Tous</button>
    <?php foreach ($categories as $cat): ?>
        <button class="filter-btn" data-category="<?= htmlspecialchars(strtolower($cat['nom'])) ?>">
            <?= htmlspecialchars($cat['nom']) ?>
        </button>
    <?php endforeach; ?>
</div>

          <!-- Nouveau select de tri prix -->
  <select id="sortPrice" style="text-align:left; margin: 20px;">
    <option value="none">Trier par prix</option>
    <option value="asc">Prix : du - cher au + cher</option>
    <option value="desc">Prix : du + cher au - cher</option>
  </select>
</div>

               <!--------------------- BARRE DE RECHERCHE EN PHP --------------------->
<div class="search-bar">
    <form method="GET" action="" style="position: relative;">
        <input 
            type="text" 
            name="search" 
            id="searchInput"
            placeholder="Rechercher par nom..." 
            value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES) ?>"
        >
        <button type="button" id="clearSearch" class="clear-button" style="display: none;">&times;</button>
    </form>
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
        document.addEventListener("DOMContentLoaded", function() {
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
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const products = document.querySelectorAll('.product-card');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Enlever la classe active
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                const category = button.getAttribute('data-category');

                products.forEach(product => {
                    const type = product.getAttribute('data-type').toLowerCase();
                    if (category === 'all' || type === category.toLowerCase()) {
                        product.style.display = 'block';
                    } else {
                        product.style.display = 'none';
                    }
                });
            });
        });
    });
    </script>
    
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearch');

    // Fonction pour afficher ou cacher le bouton clear
    function toggleClearButton() {
        if (searchInput.value.trim() !== '') {
            clearBtn.style.display = 'block';
        } else {
            clearBtn.style.display = 'none';
        }
    }

    // Détecte les changements dans le champ de recherche
    searchInput.addEventListener('input', toggleClearButton);

    // Affiche ou masque au chargement
    toggleClearButton();

    // Suppprime la recherche avec le clic sur la croix
    clearBtn.addEventListener('click', function () {
        searchInput.value = '';
        toggleClearButton();
        window.location.href = window.location.pathname; // recharge sans la recherche
    });
});
</script>

</body>

</html>