<?php
require '../../admin/config.php';
session_start();

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

$statut = isset($_GET['statut']) && in_array($_GET['statut'], ['validation', 'construction', 'final'])
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
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <title>Mes Commandes</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/commandes.css">    
</head>
<body>
    <header>
    <?php require '../../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
            <!-- Colonne de gauche -->
            <h2>Mes commandes</h2>
            <div class="tableau">
                <div class="tabs">
                <button onclick="location.href='?statut=validation'" class="tab <?= ($statut === 'validation') ? 'active' : '' ?>">En attente de validation</button>
                <button onclick="location.href='?statut=construction'" class="tab <?= ($statut === 'construction') ? 'active' : '' ?>">En cours de construction</button>
                <button onclick="location.href='?statut=final'" class="tab <?= ($statut === 'final') ? 'active' : '' ?>">Commandes finalisées</button>
                </div>
                <div class="tab-content <?= $statut === 'validation' ? 'active' : '' ?>" id="validation">
                <div id="commandes-container">
                <?php if (!empty ($commandes)): ?>
                  <?php foreach ($commandes as $commande): ?>                  
                    <div class="commande" data-id="<?= htmlspecialchars($commande['id']) ?>" data-statut="<?= htmlspecialchars($commande['statut']) ?>">
                        <div class="info">
                            <p><strong>Nom :</strong> <?= htmlspecialchars($commande['nom']) ?></p>
                            <p><strong>Prénom :</strong> <?= htmlspecialchars($commande['prenom']) ?></p>
                            <p><strong>Date :</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($commande['date']))) ?></p>
                            <p><strong>N° commande :</strong> <?= htmlspecialchars($commande['id']) ?></p>
                        </div>
                        <div class="actions">
                            <i class="fa-solid fa-file-pdf"></i>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucune commande trouvée pour ce statut.</p>
                    <?php endif; ?>
                </div>
                <nav class="nav" aria-label="pagination">
                                    <ul class="pagination">
                                        <?php if ($page > 1): ?>
                                            <li><a href="?page=<?= $page - 1 ?>&statut=<?= $statut ?>">Précédent</a></li>
                                        <?php endif; ?>

                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li>
                                                <a class="<?= $i === $page ? 'active' : '' ?>" href="?page=<?= $i ?>&statut=<?= $statut ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($page < $totalPages): ?>
                                            <li><a href="?page=<?= $page + 1 ?>&statut=<?= $statut ?>">Suivant</a></li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>

                            <div class="tab-content <?= $statut === 'construction' ? 'active' : '' ?>" id="construction">
                                <div id="commandes-container">
                                <?php if (!empty ($commandes)): ?>
                                    <?php foreach ($commandes as $commande): ?>           
                                            <div class="commande" data-id="<?= htmlspecialchars($commande['id']) ?>" data-statut="<?= htmlspecialchars($commande['statut']) ?>">
                                                <div class="info">
                                                    <p><strong>Nom :</strong> <?= htmlspecialchars($commande['nom']) ?></p>
                                                    <p><strong>Prénom :</strong> <?= htmlspecialchars($commande['prenom']) ?></p>
                                                    <p><strong>Date :</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($commande['date']))) ?></p>
                                                    <p><strong>N° commande :</strong> <?= htmlspecialchars($commande['id']) ?></p>
                                                </div>
                                                <div class="actions">
                            <i class="fa-solid fa-file-pdf"></i>
                        </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p>Aucune commande trouvée pour ce statut.</p>
                                    <?php endif; ?>
                                </div>
                                <nav class="nav" aria-label="pagination">
                                    <ul class="pagination">
                                        <?php if ($page > 1): ?>
                                            <a href="?page=<?= $page - 1 ?>&statut=<?= $statut ?>">Précédent</a>
                                        <?php endif; ?>
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li>
                                                <a class="<?= $i === $page ? 'active' : '' ?>" href="?page=<?= $i ?>&statut=<?= $statut ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($page < $totalPages): ?>
                                            <li><a href="?page=<?= $page + 1 ?>&statut=<?= $statut ?>">Suivant</a></li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>

                            <div class="tab-content <?= $statut === 'final' ? 'active' : '' ?>" id="final">
                            <div id="commandes-container">
                            <?php if (!empty ($commandes)): ?>
                                <?php foreach ($commandes as $commande): ?>
                                            <div class="commande" data-id="<?= htmlspecialchars($commande['id']) ?>" data-statut="<?= htmlspecialchars($commande['statut']) ?>">
                                                <div class="info">
                                                    <p><strong>Nom :</strong> <?= htmlspecialchars($commande['nom']) ?></p>
                                                    <p><strong>Prénom :</strong> <?= htmlspecialchars($commande['prenom']) ?></p>
                                                    <p><strong>Date :</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($commande['date']))) ?></p>
                                                    <p><strong>N° commande :</strong> <?= htmlspecialchars($commande['id']) ?></p>
                                                </div>
                                                <div class="actions">
                            <i class="fa-solid fa-file-pdf"></i>
                        </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p>Aucune commande trouvée pour ce statut.</p>
                                    <?php endif; ?>
                                </div>
                                <nav class="nav" aria-label="pagination">
                                    <ul class="pagination">
                                        <?php if ($page > 1): ?>
                                            <li><a href="?page=<?= $page - 1 ?>&statut=<?= $statut ?>">Précédent</a></li>
                                        <?php endif; ?>
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li>
                                                <a class="<?= $i === $page ? 'active' : '' ?>" href="?page=<?= $i ?>&statut=<?= $statut ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <?php if ($page < $totalPages): ?>
                                            <li><a href="?page=<?= $page + 1 ?>&statut=<?= $statut ?>">Suivant</a></li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
            </div>         
        </div>
        
</main>
<script src="../../scrpit/commandes.js"></script> 
<?php require_once '../../squelette/footer.php'?>
</body>
</html>

