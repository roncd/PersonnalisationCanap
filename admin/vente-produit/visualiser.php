<?php
require '../config.php';
session_start();
require '../include/session_expiration.php';


if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}

// Traitement de la recherche
$search = $_GET['search'] ?? '';

$tables = ['categorie'];

function fetchData($pdo, $table)
{
    $stmt = $pdo->prepare("SELECT id, nom FROM $table");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$data = [];
$assocData = [];

foreach ($tables as $table) {
    $data[$table] = fetchData($pdo, $table);
    $assocData[$table] = array_column($data[$table], 'nom', 'id');
}

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

$stmtCount = $pdo->prepare("SELECT COUNT(*) AS total FROM vente_produit");
$stmtCount->execute();
$totalCommandes = $stmtCount->fetchColumn();

$totalPages = ceil($totalCommandes / $limit);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['visible'])) {
    $id = (int) $_POST['id'];
    $visible = (int) $_POST['visible'];

    $stmt = $pdo->prepare("UPDATE vente_produit SET visible = :visible WHERE id = :id");
    $stmt->execute([
        ':visible' => $visible,
        ':id' => $id
    ]);

    echo 'ok';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produits vendu à l'unité</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/admin/tab.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="https://www.decorient.fr/medias/favicon.png">
    <link rel="stylesheet" href="../../styles/message.css">
    <link rel="stylesheet" href="../../styles/pagination.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <link rel="stylesheet" href="../../styles/popup.css">
    <script src="../../script/deleteRow.js"></script>
    <script type="module" src="../../script/visible.js"></script>

</head>

<body>

    <header>
        <?php require '../squelette/header.php'; ?>
    </header>

    <main>
        <div class="container">
            <h2>Produits vendu à l'unité</h2>
            <div class="option">
                <div class="section-button">
                    <div>
                        <button onclick="location.href='../pages/visualiser.php'" class="btn-grey" type="button">Retourner aux options</button>
                    </div>
                    <div>
                        <button onclick="location.href='add.php'" class="btn-noir" type="button">+ Ajouter un produit</button>
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
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th class="btn-order">
                                <a
                                    href="<?= $triURL ?>"
                                    title="Trier <?= $order === 'ASC' ? 'du plus récent au plus ancien' : 'du plus ancien au plus récent' ?>">
                                    <img src="<?= $icon ?>" alt="" width="20" height="20">
                                </a>
                                ID
                            </th>
                            <th>VISIBLE</th>
                            <th>RUPTURE</th>
                            <th>NOM</th>
                            <th>PRIX</th>
                            <th>IMAGE</th>
                            <th>CATÉGORIE</th>
                            <th class="sticky-col">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($search) {
                            $stmt = $pdo->prepare("SELECT * FROM vente_produit WHERE nom LIKE :search ORDER BY id $order");
                            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
                        } else {
                            $stmt = $pdo->prepare("SELECT * FROM vente_produit ORDER BY id $order LIMIT :limit OFFSET :offset");
                            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                        }
                        $stmt->execute();
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>{$row['id']}</td>";
                           $checked = $row['visible'] ? 'checked' : '';
                            echo "<td class='visible'><input type='checkbox' class='toggle-visible' data-id='{$row['id']}' $checked></td>";
                            $rupture = $row['en_rupture'] ? "<span >Oui</span>" : "<span>Non</span>";
                            echo "<td>$rupture</td>";
                            echo "<td>{$row['nom']}</td>";
                            echo "<td>{$row['prix']}</td>";
                            echo "<td><img src='../uploads/produit/{$row['img']}' alt='{$row['nom']}' ></td>";
                            echo "<td>" . htmlspecialchars($assocData['categorie'][$row['id_categorie']]) . "</td>";
                            echo "<td class='actions'>";
                            echo "<a href='edit.php?id={$row['id']}' class='edit-action actions vert' title='Modifier'>EDIT</a>";
                            echo "<a href='delete.php?id={$row['id']}' class='delete-action actions rouge' data-id='{$row['id']}' title='Supprimer'>DELETE</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
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