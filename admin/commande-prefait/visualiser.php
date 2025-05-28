<?php
require '../config.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

$search = $_GET['search'] ?? '';

$tables = [
    'structure',
    'type_banquette',
    'mousse',
    'couleur_bois',
    'accoudoir_bois',
    'dossier_bois',
    'couleur_tissu_bois',
    'motif_bois',
    'modele',
    'couleur_tissu',
    'motif_tissu',
    'accoudoir_tissu',
    'dossier_tissu',
    'decoration'
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
    $assocData[$table] = array_column($data[$table], 'nom', 'id');
}

// Récupération des commandes préfaites avec dimensions
$stmt = $pdo->prepare("SELECT id, nom, longueurA, longueurB, longueurC, prix, prix_dimensions, id_structure FROM commande_prefait ORDER BY id DESC");
$stmt->execute();
$data['commande_prefait'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Association des dimensions
$assocData['commande_prefait'] = [];
foreach ($data['commande_prefait'] as $cmd) {
    $assocData['commande_prefait'][$cmd['id']] = [
        'nom' => $cmd['nom'],
        'prix' => $cmd['prix'],
        'prix_dimensions' => $cmd['prix_dimensions'],
        'dimensions' => trim("{$cmd['longueurA']} x {$cmd['longueurB']} x {$cmd['longueurC']}", " x"),
        'structure' => $assocData['structure'][$cmd['id_structure']] ?? 'N/A',
    ];
}

// Pagination
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Compte total
$stmtCount = $pdo->prepare("SELECT COUNT(*) AS total FROM commande_prefait");
$stmtCount->execute();
$totalCommandes = $stmtCount->fetchColumn();

$totalPages = ceil($totalCommandes / $limit);

// Tri
$order = (isset($_GET['order']) && $_GET['order'] === 'asc') ? 'ASC' : 'DESC';
$next  = ($order === 'ASC') ? 'desc' : 'asc';
$icon  = ($order === 'ASC') ? '../../assets/sort-dsc.svg' : '../../assets/sort-asc.svg';

$params = $_GET;
$params['order'] = $next;
$triURL = '?' . http_build_query($params);

// Chargement des commandes paginées
$stmt = $pdo->prepare("SELECT * FROM commande_prefait ORDER BY id $order LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$pagedCommandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/admin/tab.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link rel="stylesheet" href="../../styles/message.css">
    <link rel="stylesheet" href="../../styles/pagination.css">
</head>

<body>

    <header>
        <?php require '../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
            <h2>Commande</h2>
            <div class="option">
                <div>
                    <button onclick="location.href='add.php'" class="btn" type="button">+ Ajouter une commande préfaite </button>
                </div>
            <div class="search-bar">
                <form method="GET" action="index.php">
                    <input type="text" name="search" placeholder="Rechercher par nom ou ID..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">Rechercher</button>
                </form>
            </div>
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
            <th>NOM</th>
            <th>PRIX</th>
            <th>PRIX DIMENSION</th>
            <th>STRUCTURE</th>
            <th>LONGUEUR_A</th>
            <th>LONGUEUR_B</th>
            <th>LONGUEUR_C</th>
            <th>TYPE_BANQUETTE</th>
            <th>MOUSSE</th>
            <th>COULEUR_BOIS</th>
            <th>DECORATION_BOIS</th>
            <th>ACCOUDOIR_BOIS</th>
            <th>DOSSIER_BOIS</th>
            <th>COULEUR_TISSU_BOIS</th>
            <th>MOTIF_BOIS</th>
            <th>MODELE</th>
            <th>COULEUR_TISSU</th>
            <th>MOTIF_TISSU</th>
            <th>DOSSIER_TISSU</th>
            <th>ACCOUDOIR_TISSU</th>
            <th>NB_ACCOUDOIR</th>

            <th class="sticky-col">ACTION</th>
        </thead>
        <tbody>
            <?php
            if ($search) {
                $stmt = $pdo->prepare("SELECT cp.*
                                        FROM commande_prefait AS cp
                                        WHERE cp.nom LIKE :search 
                                        ORDER BY cp.id $order");
                $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM commande_prefait ORDER BY id $order LIMIT :limit OFFSET :offset");
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
            if (!empty($results)) {
                foreach ($results as $row) { 
                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td>{$row['nom']}</td>";
                    echo "<td>{$row['prix']}</td>";
                    echo "<td>{$row['prix_dimensions']}</td>";
                    echo "<td>" . htmlspecialchars($assocData['structure'][$row['id_structure']] ?? '-') . "</td>";
                    echo "<td>{$row['longueurA']}</td>";
                    echo "<td>{$row['longueurB']}</td>";
                    echo "<td>{$row['longueurC']}</td>";
                    echo "<td>" . htmlspecialchars($assocData['type_banquette'][$row['id_banquette']] ?? '-') . "</td>";
                    echo "<td>" . htmlspecialchars($assocData['mousse'][$row['id_mousse']] ?? '-') . "</td>";
                    echo "<td>" . htmlspecialchars($assocData['couleur_bois'][$row['id_couleur_bois']] ?? '-') . "</td>";
                    echo "<td>" . htmlspecialchars($assocData['decoration'][$row['id_decoration']] ?? '-') . "</td>";
                    echo "<td>" . htmlspecialchars($assocData['accoudoir_bois'][$row['id_accoudoir_bois']] ?? '-') . "</td>";
                    echo "<td>" . htmlspecialchars($assocData['dossier_bois'][$row['id_dossier_bois']] ?? '-') . "</td>";
                    echo "<td>" . htmlspecialchars($assocData['couleur_tissu_bois'][$row['id_couleur_tissu_bois']] ?? '-') . "</td>";
                    echo "<td>" . htmlspecialchars($assocData['motif_bois'][$row['id_motif_bois']] ?? '-') . "</td>";
                    echo "<td>" . htmlspecialchars($assocData['modele'][$row['id_modele']] ?? '-') . "</td>";
                    echo "<td>" . htmlspecialchars($assocData['couleur_tissu'][$row['id_couleur_tissu']] ?? '-') . "</td>";
                    echo "<td>" . htmlspecialchars($assocData['motif_tissu'][$row['id_motif_tissu']] ?? '-') . "</td>";
                    echo "<td>" . htmlspecialchars($assocData['dossier_tissu'][$row['id_dossier_tissu']] ?? '-') . "</td>";
                    echo "<td>" . htmlspecialchars($assocData['accoudoir_tissu'][$row['id_accoudoir_tissu']] ?? '-') . "</td>";
                    echo "<td>{$row['id_nb_accoudoir']}</td>";
                    echo "<td class='actions'>";
                    echo "<a href='edit.php?id={$row['id']}' class='edit-action actions vert' title='Modifier'>EDIT</a>";
                    echo "<a href='delete.php?id={$row['id']}' class='delete-action actions rouge' title='Supprimer' onclick='return confirm(\"Voulez-vous vraiment supprimer cette commande ?\");'>DELETE</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='22' style='text-align:left;'>Aucune commande trouvée.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<?php require '../include/pagination.php'; ?>

        </div>
    </main>
    <footer>
        <?php require '../squelette/footer.php'; ?>
    </footer>
</body>

</html>