<?php
require '../config.php';
session_start();
require '../include/session_expiration.php';

if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}

// Récupération de toutes les catégories FAQ
$stmt = $pdo->prepare("SELECT id, nom, icon FROM faq_categorie ORDER BY id DESC");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Paramètres de pagination
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$limit = 10; // Nombre de commandes par page
$offset = ($page - 1) * $limit;

// Tri
$order = (isset($_GET['order']) && $_GET['order'] === 'asc') ? 'ASC' : 'DESC';
$next  = ($order === 'ASC') ? 'desc' : 'asc';
$icon  = ($order === 'ASC') ? '../../assets/sort-dsc.svg' : '../../assets/sort-asc.svg';

$params = $_GET;
$params['order'] = $next;
$triURL = '?' . http_build_query($params);

$stmtCount = $pdo->prepare("SELECT COUNT(*) AS total FROM couleur_tissu_bois");
$stmtCount->execute();
$totalCommandes = $stmtCount->fetchColumn();

$totalPages = ceil($totalCommandes / $limit);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/admin/tab.css">
    <link rel="stylesheet" href="../../styles/message.css">
    <link rel="stylesheet" href="../../styles/pagination.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/popup.css">
    <script src="../../script/deleteRow.js"></script>
</head>

<body>

    <header>
        <?php require '../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
                <h2>Liste des catégories de la FAQ</h2>

            <div class="option">
                <div class="section-button">
                    <div>
                        <button onclick="location.href='../pages/visualiser.php'" class="btn-grey" type="button">Retourner aux options</button>
                    </div>
                    <div>
                        <button onclick="location.href='add.php'" class="btn-noir" type="button">+ Ajouter une catégorie à la FAQ</button>
                    </div>
                </div>
                <div class="search-bar">
                    <form method="GET" action="">
                        <input type="text" name="search" placeholder="Rechercher par nom..." value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn-noir" type="submit">Rechercher</button>
                    </form>
                </div>
            </div>
            <?php require '../include/message.php'; ?>
           <div class="tab-container">
    <?php require '../include/message.php'; ?>
    <table class="styled-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Icône</th>
                <th class="sticky-col">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $categorie): ?>
                <tr>
                    <td><?= htmlspecialchars($categorie['id']) ?></td>
                    <td><?= htmlspecialchars($categorie['nom']) ?></td>
                    <td><?= htmlspecialchars($categorie['icon']) ?></td>
                    <td class="actions">
                        <a href="edit.php?id=<?= $categorie['id'] ?>" class="edit-action actions vert" title="Modifier">EDIT</a>
                        <a href="delete.php?id=<?= $categorie['id'] ?>" class="delete-action actions rouge" title="Supprimer">DELETE</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
            <?php require '../include/pagination.php'; ?>
        </div>
        <div id="supprimer-popup" class="popup">
            <div class="popup-content">
                <h2>Êtes-vous sûr de vouloir supprimer ?</h2>
                <p>(L'élément sera supprimé définitivement)</p>
                <br>
                <button id="confirm-delete" class="btn-beige">Oui</button>
                <button id="cancel-delete" class="btn-noir">Non</button>
            </div>
        </div>
    </main>
    <footer>
        <?php require '../squelette/footer.php'; ?>
    </footer>
</body>

</html>