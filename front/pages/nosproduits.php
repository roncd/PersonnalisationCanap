<?php
require '../../admin/config.php';
session_start();
require '../../admin/include/session_expiration.php';

// Paramètres URL
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int) $_GET['page'] : 1;
$categorie = $_GET['categorie'] ?? '';

$limit = 6;
$offset = ($page - 1) * $limit;

// Toutes les catégories 
$stmt = $pdo->query("SELECT id, nom FROM categorie WHERE visible = 1");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Affichage des cat dans les produits
$categoriesAssoc = [];
foreach ($categories as $cat) {
    $categoriesAssoc[$cat['id']] = $cat['nom'];
}

// Requête dynamique
$conditions = [];
$params = [];

$sqlBase = "
    FROM vente_produit 
    JOIN categorie ON vente_produit.id_categorie = categorie.id
";

// Filtrage par catégorie
if (!empty($categorie)) {
    // ID de la catégorie à partir de son nom
    foreach ($categoriesAssoc as $id => $nom) {
        if (strtolower($nom) === strtolower($categorie)) {
            $conditions[] = "vente_produit.id_categorie = :categorie_id";
            $params[':categorie_id'] = $id;
            break;
        }
    }
}

// Filtrage par recherche
if (!empty($search)) {
    $conditions[] = "(vente_produit.nom LIKE :search OR categorie.nom LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}
$conditions[] = "vente_produit.visible = 1";

$whereSQL = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Nombre total pour pagination
$sqlCount = "SELECT COUNT(*) $sqlBase $whereSQL";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$totalCommandes = $stmtCount->fetchColumn();

// Requête avec pagination pour les produits
$sqlProduits = "
    SELECT vente_produit.*, categorie.nom AS nom_categorie
    $sqlBase
    $whereSQL
    ORDER BY vente_produit.id DESC
    LIMIT :offset, :limit
";

$stmtProduits = $pdo->prepare($sqlProduits);

// paramètres dynamiques
foreach ($params as $key => $value) {
    $stmtProduits->bindValue($key, $value);
}

$stmtProduits->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtProduits->bindValue(':limit', $limit, PDO::PARAM_INT);

$stmtProduits->execute();
$produits = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);

// Pagination
$totalPages = ceil($totalCommandes / $limit);

// URL pour les liens de pagination
$urlParams = $_GET;
$triURL = '?' . http_build_query($urlParams);


// Gestion de l'ajout au panier
$produitAjoute = null; // Variable pour savoir quel produit a été ajouté

// 1. Si on n'est pas connecté, on ne restaure pas de POST
if (!isset($_SESSION['user_id']) && isset($_GET['post_restore'])) {
    // L'utilisateur n'est toujours pas connecté → on ne restaure pas
    unset($_SESSION['temp_post']);
    header("Location: Connexion.php");
    exit;
}

// 2. Sinon on peut restaurer le POST si c'était prévu
if (isset($_GET['post_restore']) && isset($_SESSION['temp_post'])) {
    $_POST = $_SESSION['temp_post'];
    unset($_SESSION['temp_post']);
    $_SERVER['REQUEST_METHOD'] = 'POST';
}

if (isset($_SESSION['popup_produit'])) {
    $produitAjoute = $_SESSION['popup_produit']['nom'];
    $imageProduitAjoute = $_SESSION['popup_produit']['img'];
    unset($_SESSION['popup_produit']);
}

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
        $stmt = $pdo->prepare("SELECT id, prix, img FROM vente_produit WHERE nom = ?");
        $stmt->execute([$nomProduit]);
        $produit = $stmt->fetch();

        if (!$produit) {
            // Sécurité : produit introuvable (mauvaise saisie ?)
            die("Produit introuvable.");
        }

        $id = $produit['id'];
        $prix = $produit['prix'];
        $img = $produit['img'];

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

        // Stocker le produit dans la session pour le popup après redirection
        $_SESSION['popup_produit'] = [
            'nom' => $nomProduit,
            'img' => $img
        ];

        // Rediriger pour éviter re-post et déclencher le modal
        header("Location: ?post_restore=1");
        exit;
    }
}
if (!empty($produitAjoute)) : ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById("reservation-modal");
            const productNameEl = document.getElementById("product-name");
            const productImgEl = document.getElementById("product-image");

            const lastAddedProduct = {
                name: <?= json_encode($produitAjoute) ?>,
                image: <?= json_encode($imageProduitAjoute ?? '') ?>
            };

            function openReservationModal(productName, productImg) {
                productNameEl.textContent = `Nom du produit : ${productName}`;
                productImgEl.src = `../../admin/uploads/produit/${productImg}`;
                modal.style.display = "flex";
                document.documentElement.classList.add("no-scroll");
                document.body.classList.add("no-scroll");
                console.log("Image du produit :", productImgEl.src);

            }

            function fermerModal() {
                modal.style.display = "none";
                document.documentElement.classList.remove("no-scroll");
                document.body.classList.remove("no-scroll");
            }

            document.querySelector(".close-modal")?.addEventListener("click", fermerModal);

            window.addEventListener("click", (event) => {
                if (event.target === modal) {
                    fermerModal();
                }
            });

            if (lastAddedProduct.name) {
                openReservationModal(lastAddedProduct.name, lastAddedProduct.image);
            }
        });
    </script>
<?php endif; ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Découvre notre sélection de produits de qualité vendu à l'unité. Ces produits sont disponibles uniquement sur réservation et pick-up en boutique." />
    <title>Nos Produits</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png" />
    <link rel="stylesheet" href="../../styles/catalogue.css" />
    <link rel="stylesheet" href="../../styles/buttons.css" />
    <link rel="stylesheet" href="../../styles/pagination.css">
    <link rel="stylesheet" href="../../styles/transition.css">
    <script type="module" src="../../script/transition.js"></script>

</head>

<body>
    <?php include '../cookies/index.html'; ?>
    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>

    <section class="hero-section">
        <div class="hero-container">
            <img src="../../medias/hero-banner.jpg" alt="Salon marocain" class="hero-image">
            <div class="hero-content">
                <br /><br /><br />
                <h1 class="hero-title h2">Nos Produits</h1>

                <p class="hero-description">
                    Découvre notre sélection de produits de qualité vendu à l'unité.
                    Ces produits sont disponibles uniquement sur réservation.
                </p>
            </div>
        </div>
    </section>

    <main class="products-container">

        <!-- ------------------- SECTION ARTICLES ASSOCIES ------------------- -->
        <section class="combination-section">

            <!------------ BOUTONS DE FILTRE PAR CATÉGORIE ----------->
            <?php
            $currentCategorie = isset($_GET['categorie']) ? strtolower($_GET['categorie']) : '';
            ?>

            <div class="filters">
                <button class="filter-btn <?= $currentCategorie === '' ? 'active' : '' ?>" data-category="">Tous</button>

                <?php foreach ($categories as $cat): ?>
                    <?php $catNom = strtolower($cat['nom']); ?>
                    <button class="filter-btn <?= $currentCategorie === $catNom ? 'active' : '' ?>" data-category="<?= htmlspecialchars($catNom) ?>">
                        <?= htmlspecialchars($cat['nom']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="search-tri transition-all">
                <!-- Nouveau select de tri prix -->
                <select id="sortPrice">
                    <option value="none">Trier par prix</option>
                    <option value="asc">Prix : du - cher au + cher</option>
                    <option value="desc">Prix : du + cher au - cher</option>
                </select>
                <!--------------------- BARRE DE RECHERCHE EN PHP --------------------->
                <div class="search-bar transition-all">
                    <form method="GET" action="">
                        <input
                            type="text"
                            name="search"
                            id="searchInput"
                            placeholder="Rechercher par nom..."
                            value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES) ?>">
                        <button type="button" id="clearSearch" class="clear-button" style="display: none;">&times;</button>
                    </form>
                </div>
            </div>

            <!-- ------------------- SECTION ARTICLES ASSOCIES ------------------- -->
            <section class="combination-section">
                <div class="combination-container transition-all">
                    <?php foreach ($produits as $produit): ?>
                        <?php
                        $catNom = isset($categoriesAssoc[$produit['id_categorie']])
                            ? strtolower($categoriesAssoc[$produit['id_categorie']])
                            : '';
                        ?>
                        <div class="product-card" data-category="<?= htmlspecialchars($catNom) ?>">
                            <div class="product-image">
                                <img
                                    src="../../admin/uploads/produit/<?= htmlspecialchars($produit['img']) ?>"
                                    alt="<?= htmlspecialchars($produit['nom']) ?>" />
                            </div>
                            <div class="product-content">
                                <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                                <p class="description"> Catégorie : <?= htmlspecialchars(ucfirst($catNom)) ?>
                                </p>
                                <p class="price"><?= number_format($produit['prix'], 2, ',', ' ') ?> €</p>
                                <form method="POST">
                                    <input type="hidden" name="produit" value="<?= htmlspecialchars($produit['nom']) ?>" />
                                    <input type="hidden" name="quantite" value="1" />
                                    <button type="submit" class="btn-beige">Ajouter au panier</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php require '../../admin/include/pagination.php'; ?>
            </section>

            <!-- Modal d'ajout au panier -->
            <div id="reservation-modal" class="modal" style="display:none;">
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <img src="../../assets/check-icone.svg" alt="Image du produit" class="check-icon" />
                    <br />
                    <h2 class="success-message">Ajouté au panier avec succès !</h2>
                    <div class="product-info">
                        <img id="product-image" class="img-panier" />
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
        document.addEventListener('DOMContentLoaded', function() {
            const sortSelect = document.getElementById('sortPrice');
            const productsContainer = document.querySelector('.combination-container'); // conteneur des cartes
            const products = Array.from(document.querySelectorAll('.product-card'));

            sortSelect.addEventListener('change', function() {
                const value = this.value;

                if (value === 'none') {
                    // Remettre dans l'ordre original (par id ou ordre DOM initial)
                    products.forEach(product => productsContainer.appendChild(product));
                    return;
                }

                // Trier en fonction du prix affiché dans chaque carte
                const sorted = products.slice().sort((a, b) => {
                    const priceA = parseFloat(a.querySelector('.price').textContent.replace(/\s/g, '').replace(',', '.').replace('€', '')) || 0;
                    const priceB = parseFloat(b.querySelector('.price').textContent.replace(/\s/g, '').replace(',', '.').replace('€', '')) || 0;

                    return value === 'asc' ? priceA - priceB : priceB - priceA;
                });

                // Réordonner les cartes dans le DOM
                sorted.forEach(product => productsContainer.appendChild(product));
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                toggleClearButton();
                window.location.href = window.location.pathname; // recharge sans la recherche
            });
        });
    </script>

</body>

</html>