<?php
require '../../admin/config.php';
session_start();

$sql = "SELECT cp.*, 
               tb.nom AS type_nom, 
               s.nom AS structure_nom
        FROM commande_prefait cp
        LEFT JOIN type_banquette tb ON cp.id_banquette = tb.id
        LEFT JOIN structure s ON cp.id_structure = s.id
        ORDER BY cp.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);


$stmt = $pdo->prepare($sql);
$stmt->execute();
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    // 💰 Prix par centimètre (350 € / mètre = 3.5 € / cm)
    $prixParCm = 3.5;

    foreach (['longueurA', 'longueurB', 'longueurC'] as $longueur) {
        if (!empty($commande[$longueur])) {
            $totalPrice += floatval($commande[$longueur]) * $prixParCm;
        }
    }

    // 💡 Bonus : traitement spécifique de certains éléments (optionnel)
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
    <title>Nos Canapés</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link rel="stylesheet" href="../../styles/catalogue.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
</head>

<body>
    <?php include '../cookies/index.html'; ?>
    <header>
        <?php require '../../squelette/header.php'; ?>

    </header>




    <section class="hero-section">
        <div class="hero-container">
            <img src="../../medias/salon-marocain.jpg" alt="Salon marocain" class="hero-image">
            <div class="hero-content">
                <br><br><br>
                <h1 class="hero-title h2">
                    Nos Canapés Pré-Personnalisés
                </h1>

                <p class="hero-description">
                    Nos canapés marocains pré-personnalisés allient tradition et modernité pour sublimer votre salon.
                    Choisissez votre style, réservez votre modèle favoris et récupérez-le directement en boutique.
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

        
<!-- ------------------- SECTION COMBINAISONS ------------------- -->
<section class="combination-section">
  <div class="combination-container">

    <?php foreach ($commandes as $commande): ?>
      <?php
        $composition = []; 
        $prixDynamique = calculPrix($commande, $composition);
        $type = strtolower($commande['type_nom'] ?? 'inconnu');
      ?>
      <div class="product-card" data-type="<?= htmlspecialchars($type, ENT_QUOTES) ?>">
        <div class="product-image">
          <img
            src="../../admin/uploads/canape-prefait/<?php echo htmlspecialchars($commande['img'] ?? 'default.jpg', ENT_QUOTES); ?>"
            alt="<?php echo htmlspecialchars($commande['nom'] ?? 'Canapé préfait', ENT_QUOTES); ?>">
        </div>
        <div class="product-content">
          <h3><?= htmlspecialchars($commande['nom']) ?></h3>
          <p class="description">Type : <?= htmlspecialchars($commande['type_nom']) ?></p>
          <p class="description">Structure : <?= htmlspecialchars($commande['structure_nom'] ?? 'Non défini') ?></p>
          <p class="price"><?= number_format($prixDynamique, 2, ',', ' ') ?> €</p>
          <button class="btn-beige"
            onclick="window.location.href = '../CanapePrefait/canapPrefait.php?id=<?= (int)$commande['id']; ?>'">
            Personnaliser
          </button>
        </div>
      </div>
    <?php endforeach; ?>

  </div> 
</section>


    </main>

    <footer>
        <?php require '../../squelette/footer.php'; ?>
    </footer>
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


</body>

</html>