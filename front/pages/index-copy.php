<?php
require '../../admin/config.php';
session_start();

$sql = "SELECT cp.*, tb.nom as type_nom 
        FROM commande_prefait cp
        LEFT JOIN type_banquette tb ON cp.id_banquette = tb.id";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC); 

function calculPrix($commande, &$composition = []) {
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
    <title>Accueil</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link rel="stylesheet" href="../../styles/styles.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/accueil.css">
    <script src="../../node_modules/@preline/carousel/index.js"></script>


    <link rel="stylesheet" href="/styles/styles.css">
<link rel="stylesheet" href="/styles/buttons.css">
<link rel="icon" type="image/x-icon" href="/medias/favicon.png">
<script src="/node_modules/@preline/carousel/index.js"></script>

</head>
       
<body>
    <?php include '../cookies/index.html'; ?>
    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>

    <!-- Main -->
    <main>
        
    <section class="hero-section">
        <div class="hero-container">
            <img src="../../medias/salon-marocain.jpg" alt="Salon marocain" class="hero-image">
            <div class="hero-content">
                <br><br><br>
                <h1 class="hero-title h2">
                    Personnalisez votre salon marocain
                </h1>

                <p class="hero-description">
                Laissez-vous tenter et personnalisez votre salon de A à Z !
                        Du canapé à la table, choisissez les configurations qui vous plaisent le plus.
                        La couleur, le tissu, la forme... faites ce qui vous ressemble pour un prix raisonnable.
                </p>
                 <a href="dashboard.php">
                        <button class="btn-noir">PERSONNALISER</button>
                </a>
            </div>
        </div>
    </section>
<section class="combination-section">
  <h2>Choisissez une combinaison à personnaliser</h2>

  <div class="combination-container">
    <?php foreach (array_slice($commandes, 0, 3) as $commande): ?>
      <?php
        $composition = [];
        $prixDynamique = calculPrix($commande, $composition);
      ?>
      <div class="product-card">
        <div class="product-image">
          <img
            src="../../admin/uploads/canape-prefait/<?php echo htmlspecialchars($commande['img'] ?? 'default.jpg', ENT_QUOTES); ?>"
            alt="<?php echo htmlspecialchars($commande['nom'] ?? 'Canapé préfait', ENT_QUOTES); ?>">
        </div>
        <div class="product-content">
          <h3><?= htmlspecialchars($commande['nom']) ?></h3>
          <p class="price"><?= number_format($prixDynamique, 2, ',', ' ') ?> €</p>
          <button class="btn-beige"
            onclick="window.location.href = '../CanapePrefait/canapPrefait.php?id=<?= (int)$commande['id']; ?>'">
            Personnaliser
          </button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (count($commandes) > 3): ?>
  <h2 style="margin-top: 3rem;">Autres modèles</h2>
  <div class="carousel-container">
    <?php foreach (array_slice($commandes, 3) as $commande): ?>
      <?php
        $composition = [];
        $prixDynamique = calculPrix($commande, $composition);
      ?>
      <div class="product-card">
        <div class="product-image">
          <img
            src="../../admin/uploads/canape-prefait/<?php echo htmlspecialchars($commande['img'] ?? 'default.jpg', ENT_QUOTES); ?>"
            alt="<?php echo htmlspecialchars($commande['nom'] ?? 'Canapé préfait', ENT_QUOTES); ?>">
        </div>
        <div class="product-content">
          <h3><?= htmlspecialchars($commande['nom']) ?></h3>
          <p class="price"><?= number_format($prixDynamique, 2, ',', ' ') ?> €</p>
          <button class="btn-beige"
            onclick="window.location.href = '../CanapePrefait/canapPrefait.php?id=<?= (int)$commande['id']; ?>'">
            Personnaliser
          </button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
   



    </main>
    <!-- Footer -->
    <footer>
        <?php require '../../squelette/footer.php'; ?>
    </footer>

</body>

</html>