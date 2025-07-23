<?php
require '../../admin/config.php';
session_start();
require '../../admin/include/session_expiration.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../formulaire/Connexion.php"); // Redirection vers la page de connexion
    exit();
}

$userId = $_SESSION['user_id'];

// Paramètres de pagination
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$statut = isset($_GET['statut']) && in_array($_GET['statut'], ['validation', 'construction', 'livraison', 'final'])
    ? $_GET['statut']
    : 'validation';

// Récupérer les commandes de l'utilisateur connecté
$stmt = $pdo->prepare("SELECT cd.id, cd.date, cd.statut, cl.id 
    AS id_client, cl.nom, cl.prenom 
    FROM commande_detail cd
    JOIN client cl 
    ON cd.id_client = cl.id 
    WHERE id_client = :id_client AND statut = :statut ORDER BY id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':id_client', $userId, PDO::PARAM_INT);
$stmt->bindValue(':statut', $statut, PDO::PARAM_STR);
$stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
$stmt->execute();
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Compter le nombre total de commandes pour ce statut
$stmtCount = $pdo->prepare("SELECT COUNT(*) AS total FROM commande_detail WHERE id_client = ? AND statut = ?");
$stmtCount->execute([$userId, $statut]);
$totalCommandes = $stmtCount->fetchColumn();

$totalPages = ceil($totalCommandes / $limit);

// Organiser les commandes par statut
foreach ($commandes as $commande) {
    $statuts[$commande['statut']][] = $commande;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta name="description" content="Retrouve ton suivis et historique de commande de canapé marocain." />
    <title>Suivis des commandes - canapés marocains</title>
    <link rel="icon" type="image/png" href="https://www.decorient.fr/medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../styles/commandes.css">
    <link rel="stylesheet" href="../../styles/pagination.css">
    <script type="module" src="../../script/download.js"></script>
    <link rel="stylesheet" href="../../styles/transition.css">
    <script type="module" src="../../script/transition.js"></script>
    <script type="module" src="../../script/commandes.js"></script>
</head>

<body>
    <?php include '../cookies/index.html'; ?>
    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
            <!-- Colonne de gauche -->
            <h2 class="h2-commande">Suivis des commandes - canapés marocains</h2>
            <div class="content">
                <div class="tableau">
                    <div class="tabs">
                        <button onclick="location.href='?statut=validation'" class="tab <?= ($statut === 'validation') ? 'active' : '' ?>">En attente de validation</button>
                        <button onclick="location.href='?statut=construction'" class="tab <?= ($statut === 'construction') ? 'active' : '' ?>">En cours de construction</button>
                        <button onclick="location.href='?statut=livraison'" class="tab <?= ($statut === 'livraison') ? 'active' : '' ?>">En cours de livraison</button>
                        <button onclick="location.href='?statut=final'" class="tab <?= ($statut === 'final') ? 'active' : '' ?>">Commandes finalisées</button>
                    </div>
                    <div class="tab-content <?= $statut === 'validation' ? 'active' : '' ?>" id="validation">
                        <div id="commandes-container">
                            <?php if (!empty($commandes)): ?>
                                <?php foreach ($commandes as $commande): ?>
                                    <div class="commande transition-all" data-id="<?= htmlspecialchars($commande['id']) ?>" data-statut="<?= htmlspecialchars($commande['statut']) ?>">
                                        <div class="info">
                                            <p><strong>Nom :</strong> <?= htmlspecialchars($commande['nom']) ?></p>
                                            <p><strong>Prénom :</strong> <?= htmlspecialchars($commande['prenom']) ?></p>
                                            <p><strong>Date :</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($commande['date']))) ?></p>
                                            <p><strong>N° commande :</strong> <?= htmlspecialchars($commande['id']) ?></p>
                                        </div>
                                        <div class="actions">
                                            <i title="Téléchargez le devis" class="bx bxs-file-pdf" data-id="<?= htmlspecialchars($commande['id']) ?>"></i>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>Aucune commande trouvée pour ce statut.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="tab-content <?= $statut === 'construction' ? 'active' : '' ?>" id="construction">
                        <div id="commandes-container">
                            <?php if (!empty($commandes)): ?>
                                <?php foreach ($commandes as $commande): ?>
                                    <div class="commande transition-all" data-id="<?= htmlspecialchars($commande['id']) ?>" data-statut="<?= htmlspecialchars($commande['statut']) ?>">
                                        <div class="info">
                                            <p><strong>Nom :</strong> <?= htmlspecialchars($commande['nom']) ?></p>
                                            <p><strong>Prénom :</strong> <?= htmlspecialchars($commande['prenom']) ?></p>
                                            <p><strong>Date :</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($commande['date']))) ?></p>
                                            <p><strong>N° commande :</strong> <?= htmlspecialchars($commande['id']) ?></p>
                                        </div>
                                        <div class="actions">
                                            <i title="Téléchargez le devis" class="bx bxs-file-pdf" data-id="<?= htmlspecialchars($commande['id']) ?>"></i>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>Aucune commande trouvée pour ce statut.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="tab-content <?= $statut === 'livraison' ? 'active' : '' ?>" id="livraison">
                        <div id="commandes-container">
                            <?php if (!empty($commandes)): ?>
                                <?php foreach ($commandes as $commande): ?>
                                    <div class="commande transition-all" data-id="<?= htmlspecialchars($commande['id']) ?>" data-statut="<?= htmlspecialchars($commande['statut']) ?>">
                                        <div class="info">
                                            <p><strong>Nom :</strong> <?= htmlspecialchars($commande['nom']) ?></p>
                                            <p><strong>Prénom :</strong> <?= htmlspecialchars($commande['prenom']) ?></p>
                                            <p><strong>Date :</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($commande['date']))) ?></p>
                                            <p><strong>N° commande :</strong> <?= htmlspecialchars($commande['id']) ?></p>
                                        </div>
                                        <div class="actions">
                                            <i title="Téléchargez le devis" class="bx bxs-file-pdf" data-id="<?= htmlspecialchars($commande['id']) ?>"></i>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>Aucune commande trouvée pour ce statut.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="tab-content <?= $statut === 'final' ? 'active' : '' ?>" id="final">
                        <div id="commandes-container">
                            <?php if (!empty($commandes)): ?>
                                <?php foreach ($commandes as $commande): ?>
                                    <div class="commande transition-all" data-id="<?= htmlspecialchars($commande['id']) ?>" data-statut="<?= htmlspecialchars($commande['statut']) ?>">
                                        <div class="info">
                                            <p><strong>Nom :</strong> <?= htmlspecialchars($commande['nom']) ?></p>
                                            <p><strong>Prénom :</strong> <?= htmlspecialchars($commande['prenom']) ?></p>
                                            <p><strong>Date :</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($commande['date']))) ?></p>
                                            <p><strong>N° commande :</strong> <?= htmlspecialchars($commande['id']) ?></p>
                                        </div>
                                        <div class="actions">
                                            <i title="Téléchargez le devis" class="bx bxs-file-pdf" data-id="<?= htmlspecialchars($commande['id']) ?>"></i>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>Aucune commande trouvée pour ce statut.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <nav class="nav" aria-label="pagination">
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                                <li><a href="?page=<?= $page - 1 ?>&statut=<?= $statut ?>">Précédent</a></li>
                            <?php endif; ?>

                            <?php
                            $max_links = 3;
                            $start = max(1, $page - floor($max_links / 2));
                            $end = min($totalPages, $start + $max_links - 1);

                            if ($end - $start + 1 < $max_links) {
                                $start = max(1, $end - $max_links + 1);
                            }

                            if ($start > 1):
                            ?>
                                <li><a href="?page=1&statut=<?= $statut ?>">1</a></li>
                                <?php if ($start > 2): ?>
                                    <li><span>…</span></li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <li>
                                    <a class="<?= $i == $page ? 'active' : '' ?>" href="?page=<?= $i ?>&statut=<?= $statut ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end < $totalPages): ?>
                                <?php if ($end < $totalPages - 1): ?>
                                    <li><span>…</span></li>
                                <?php endif; ?>
                                <li><a href="?page=<?= $totalPages ?>&statut=<?= $statut ?>"><?= $totalPages ?></a></li>
                            <?php endif; ?>

                            <?php if ($page < $totalPages): ?>
                                <li><a href="?page=<?= $page + 1 ?>&statut=<?= $statut ?>">Suivant</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

    </main>
    <?php require_once '../../squelette/footer.php' ?>
</body>

</html>