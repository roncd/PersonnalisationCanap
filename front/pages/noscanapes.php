<?php
require '../../admin/config.php';
session_start();
require '../../admin/include/session_expiration.php';

// Paramètres URL
$search = $_GET['search'] ?? '';
$page = (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) ? (int) $_GET['page'] : 1;
$type = $_GET['type'] ?? '';
$limit = 6;
$offset = ($page - 1) * $limit;

// Tous les types 
$stmt = $pdo->query("SELECT id, nom FROM type_banquette WHERE visible = 1");
$types = $stmt->fetchAll(PDO::FETCH_ASSOC);

//Récupération des canapés avec leurs types et structures associés
$sql = "SELECT cp.*, 
               tb.nom AS type_nom, 
               s.nom AS structure_nom
        FROM commande_prefait cp 
         JOIN type_banquette tb ON cp.id_banquette = tb.id
         JOIN structure s ON cp.id_structure = s.id";

$conditions = [];
$params = [];

$conditions[] = "cp.visible = 1";

// recherche
if (!empty($search)) {
    $conditions[] = "(cp.nom LIKE :search OR tb.nom LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

// filtre type de banquette
if (!empty($type)) {
    $conditions[] = "LOWER(tb.nom) = :type";
    $params[':type'] = strtolower($type);
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY cp.id DESC LIMIT :offset, :limit";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);


$sqlCount = "SELECT COUNT(*) FROM commande_prefait cp
             JOIN type_banquette tb ON cp.id_banquette = tb.id";

if (!empty($conditions)) {
    $sqlCount .= " WHERE " . implode(" AND ", $conditions);
}

$stmtCount = $pdo->prepare($sqlCount);
foreach ($params as $key => $value) {
    $stmtCount->bindValue($key, $value, PDO::PARAM_STR);
}
$stmtCount->execute();
$totalCommandes = $stmtCount->fetchColumn();

$totalPages = ceil($totalCommandes / $limit);

// URL pour les liens de pagination
$urlParams = $_GET;
$triURL = '?' . http_build_query($urlParams);


function calculPrix($commande, &$composition = [])
{
    global $pdo;

    $composition = [];
    $totalPrice = 0;
    $id_commande = $commande['id'];

    // Liste des éléments simples
    $elements = [
        'id_structure' => 'structure',
        'id_banquette' => 'type_banquette',
        'id_mousse' => 'mousse',
        'id_couleur_bois' => 'couleur_bois',
        'id_decoration' => 'decoration',
        'id_accoudoir_bois' => 'accoudoir_bois',
        'id_dossier_bois' => 'dossier_bois',
        'id_couleur_tissu_bois' => 'couleur_tissu_bois',
        'id_motif_bois' => 'motif_bois',
        'id_modele' => 'modele',
        'id_couleur_tissu' => 'couleur_tissu',
        'id_motif_tissu' => 'motif_tissu',
        'id_dossier_tissu' => 'dossier_tissu',
        'id_accoudoir_tissu' => 'accoudoir_tissu',
    ];

    foreach ($elements as $colonne => $table) {
        if (!empty($commande[$colonne])) {
            $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
            $stmt->execute([$commande[$colonne]]);
            $detail = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($detail) {
                $composition[$table] = $detail;
                if (!empty($detail['prix'])) {
                    $totalPrice += floatval($detail['prix']);
                }
            }
        }
    }

    // Accoudoirs multiples (bois)
    $stmt = $pdo->prepare("SELECT ab.*, cpa.nb_accoudoir
                           FROM commande_prefait_accoudoir cpa
                           JOIN accoudoir_bois ab ON cpa.id_accoudoir_bois = ab.id
                           WHERE cpa.id_commande_prefait = ?");
    $stmt->execute([$id_commande]);
    $accoudoirs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($accoudoirs) {
        $composition['accoudoirs_bois_multiples'] = $accoudoirs;
        foreach ($accoudoirs as $acc) {
            if (!empty($acc['prix']) && !empty($acc['nb_accoudoir'])) {
                $totalPrice += floatval($acc['prix']) * intval($acc['nb_accoudoir']);
            }
        }
    }

    // Prix par centimètre 
    $prixParCm = 3.5;

    foreach (['longueurA', 'longueurB', 'longueurC'] as $longueur) {
        if (!empty($commande[$longueur])) {
            $totalPrice += floatval($commande[$longueur]) * $prixParCm;
        }
    }

    // Bonus : traitement spécifique de certains éléments (optionnel)
    if (!empty($composition)) {
        foreach ($composition as $nomTable => $details) {
            if ($nomTable === 'accoudoirs_bois_multiples') continue; // déjà traité
            if ($nomTable === 'accoudoir_tissu') {
                if (!empty($details['prix'])) {
                    $totalPrice += floatval($details['prix']); // tu peux multiplier par 2 si besoin
                }
            }
        }
    }

    return $totalPrice;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Choisis ton style, réservez ton modèle favoris et récupére-le directement en boutique ou fait toi livrer." />
    <title>Nos Canapés</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="https://www.decorient.fr/medias/favicon.png">
    <link rel="stylesheet" href="../../styles/catalogue.css">
    <link rel="stylesheet" href="../../styles/transition.css">
    <script type="module" src="../../script/transition.js"></script>
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/pagination.css">
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
                <br><br><br>
                <h1 class="hero-title h2">
                    Nos Canapés Pré-fait
                </h1>

                <p class="hero-description">
                    Choisis ton style, réservez ton modèle favoris et récupére-le directement en boutique ou fait toi livrer.
                </p>
            </div>
        </div>
    </section>

    <main class="products-container">
        <!-- Filtres -->
        <?php
        $currentType = isset($_GET['type']) ? strtolower($_GET['type']) : '';
        ?>

        <div class="filters">
            <button class="filter-btn <?= $currentType === '' ? 'active' : '' ?>" data-type="">Tous</button>
            <?php foreach ($types as $t): ?>
                <?php $typeNom = strtolower($t['nom']); ?>
                <button class="filter-btn <?= $currentType === $typeNom ? 'active' : '' ?>" data-type="<?= htmlspecialchars($typeNom) ?>">
                    <?= htmlspecialchars($t['nom']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="search-tri transition-all">
            <!-- Nouveau select de tri prix -->
            <select id="sortPrice" style="text-align:left; margin: 20px;">
                <option value="none">Trier par prix</option>
                <option value="asc">Prix : du - cher au + cher</option>
                <option value="desc">Prix : du + cher au - cher</option>
            </select>

            <!-- ------------------- BARRE DE RECHERCHE EN PHP ------------------- -->
            <div class="search-bar transition-all">
                <form method="GET" action="" style="position: relative;">
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

        <!-- ------------------- SECTION COMBINAISONS ------------------- -->
        <section class="combination-section">
            <div class="combination-container transition-all">

                <?php foreach ($commandes as $commande): ?>
                    <?php
                    $composition = [];
                    $prixDynamique = calculPrix($commande, $composition);
                    ?>
                    <div class="product-card" data-type="<?= htmlspecialchars(strtolower($commande['type_nom'] ?? '')) ?>">
                        <div class="product-image">
                            <?php
                            $imgName = $commande['img'] ?? 'canapePrefait.jpg';
                            $imgPathPrimary = "../../admin/uploads/canape-prefait/" . $imgName;
                            $imgPathFallback = "../../medias/canapePrefait.jpg";

                            // Choisir le chemin selon l'existence du fichier
                            if (!empty($imgName) && file_exists($imgPathPrimary)) {
                                $imgSrc = $imgPathPrimary;
                            } else {
                                $imgSrc = $imgPathFallback;
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($imgSrc, ENT_QUOTES); ?>"
                                alt="<?php echo htmlspecialchars($commande['nom'] ?? 'Canapé pré-fait', ENT_QUOTES); ?>">
                        </div>
                        <div class="product-content">
                            <h3><?= htmlspecialchars($commande['nom']) ?></h3>
                            <p class="description">Type : <?= htmlspecialchars($commande['type_nom'] ?? '') ?></p>
                            <p class="description">Structure :
                                <?= htmlspecialchars($commande['structure_nom'] ?? 'Non défini') ?></p>
                            <p class="price"><?= number_format($prixDynamique, 2, ',', ' ') ?> €</p>
                            <button class="btn-beige"
                                onclick="window.location.href = '../CanapePrefait/canapPrefait.php?id=<?= (int)$commande['id']; ?>'">
                                Personnaliser
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php require '../../admin/include/pagination.php'; ?>
        </section>


    </main>

    <footer>
        <?php require '../../squelette/footer.php'; ?>
    </footer>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const filterButtons = document.querySelectorAll(".filter-btn");

            filterButtons.forEach((button) => {
                button.addEventListener("click", () => {
                    const selectedType = button.getAttribute("data-type").toLowerCase();
                    window.location.href = `?type=${encodeURIComponent(selectedType)}&page=1`;
                });
            });
        });
    </script>

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

            // Repère les changements dans le champ de recherche
            searchInput.addEventListener('input', toggleClearButton);

            // Affiche ou masque au chargement
            toggleClearButton();

            // Supprime la recherche au clic sur la croix
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                toggleClearButton();
                window.location.href = window.location.pathname; // recharge sans la recherche
            });
        });
    </script>

</body>

</html>