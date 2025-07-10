<?php
require '../config.php';
session_start();


if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}

$search = $_GET['search'] ?? '';

$tables = [
    'client',
    'vente_produit',
];

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
    // Convertir en tableau associatif clé=id, valeur=nom
    $assocData[$table] = array_column($data[$table], 'nom', 'id');
}


// Paramètres de pagination
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$limit = 10; // Nombre de commandes par page
$offset = ($page - 1) * $limit;

// Compter le nombre total de commandes pour ce statut
$stmtCount = $pdo->prepare("SELECT COUNT(*) AS total FROM panier_final");
$stmtCount->execute();
$totalCommandes = $stmtCount->fetchColumn();

$totalPages = ceil($totalCommandes / $limit);
$order = (isset($_GET['order']) && $_GET['order'] === 'asc') ? 'ASC' : 'DESC';
$next  = ($order === 'ASC') ? 'desc' : 'asc';
$icon  = ($order === 'ASC')
    ? '../../assets/sort-dsc.svg'
    : '../../assets/sort-asc.svg';

$params = $_GET;
$params['order'] = $next;

$triURL = '?' . http_build_query($params);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande - Paniers</title>
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
            <h2>Commande - Paniers</h2>
            <div class="search-bar">
                <form method="GET" action="index.php">
                    <input type="text" name="search" placeholder="Rechercher par ID client..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn-noir" type="submit">Rechercher</button>
                </form>
            </div>
            <?php require '../include/message.php'; ?>
            <div class="tab-container">
                <table class="styled-table">
                    <thead>
                        <th class="btn-order">
                            <a
                                href="<?= $triURL ?>"
                                title="Trier <?= $order === 'ASC' ? 'du plus récent au plus ancien' : 'du plus ancien au plus récent' ?>">
                                <img src="<?= $icon ?>" alt="" width="20" height="20">
                            </a>
                            ID
                        </th>
                        <th>CLIENT</th>
                        <th>PRIX (TOTAL)</th>
                        <th>DATE</th>
                        <th>STATUT</th>
                        <th>PRODUITS</th>
                        <th class="sticky-col">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($search) {
                            $stmt = $pdo->prepare("SELECT pf.*, c.id AS id_client, c.nom, c.prenom
                                                    FROM panier_final AS pf
                                                    INNER JOIN client AS c ON pf.id_client = c.id
                                                    WHERE c.nom LIKE :search OR c.id LIKE :search
                                                    ORDER BY id $order
                                                    ");
                            $stmt->bindValue(':search', $search, PDO::PARAM_STR);
                        } else {
                            $stmt = $pdo->prepare("SELECT * FROM panier_final ORDER BY id $order LIMIT :limit OFFSET :offset");
                            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                        }
                        $stmt->execute();
                        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        // Récupérer les accoudoirs bois associés à la commande detail
                        foreach ($results as &$commande) {
                            $stmt = $pdo->prepare("SELECT vp.nom, SUM(pdf.quantite) as quantite
                            FROM panier_detail_final pdf
                            JOIN vente_produit vp ON pdf.id_produit = vp.id
                            WHERE pdf.id_panier_final = ?
                            GROUP BY pdf.id_produit
                        ");
                            $stmt->execute([$commande['id']]);
                            $commande['produits'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        }
                        if (!empty($results)) {
                            foreach ($results as $row) {
                                echo "<tr>";
                                echo "<td>{$row['id']}</td>";
                                echo "<td>" . htmlspecialchars($assocData['client'][$row['id_client']] ?? '-') . "</td>";
                                echo "<td>{$row['prix']}</td>";
                                echo "<td>{$row['date']}</td>";
                                echo "<td>{$row['statut']}</td>";

                                echo "<td>";
                                if (!empty($row['produits'])) {
                                    foreach ($row['produits'] as $produit) {
                                        echo htmlspecialchars($produit['nom']) . " (x" . $produit['quantite'] . ")<br>";
                                    }
                                } else {
                                    echo "-";
                                }
                                echo "</td>";

                                echo "<td class='actions'>";
                                echo "<a href='edit.php?id={$row['id']}' class='edit-action actions vert' title='Modifier'>EDIT</a>";
                                echo "<a href='delete.php?id={$row['id']}' class='delete-action actions rouge' data-id='{$row['id']}' title='Supprimer'>DELETE</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='27' style='text-align:left;'>Aucune commande trouvée.</td></tr>";
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