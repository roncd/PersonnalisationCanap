<?php
require '../config.php';
session_start();


if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}

$search = $_GET['search'] ?? '';

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

$stmtCount = $pdo->prepare("SELECT COUNT(*) AS total FROM client");
$stmtCount->execute();
$totalCommandes = $stmtCount->fetchColumn();

$totalPages = ceil($totalCommandes / $limit);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client</title>
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
            <h2>Client</h2>

            <div class="option">
                <div>
                    <button onclick="location.href='add.php'" class="btn-noir" type="button">+ Ajouter un client</button>
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
                            <th>CIVILITÉ</th>
                            <th>NOM</th>
                            <th>PRENOM</th>
                            <th>MAIL</th>
                            <th>TELEPHONE</th>
                            <th>DATE_NAISSANCE</th>
                            <th>ADRESSE</th>
                            <th>INFO_SUP</th>
                            <th>CODE_POSTAL</th>
                            <th>VILLE</th>
                            <th>DATE_CREATION</th>
                            <th class="sticky-col">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($search) {
                            $stmt = $pdo->prepare("SELECT * FROM client WHERE nom LIKE :search ORDER BY id $order");
                            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
                        } else {
                            $stmt = $pdo->prepare("SELECT * FROM client ORDER BY id $order LIMIT :limit OFFSET :offset");
                            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                        }
                        $stmt->execute();
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td><a class='id' href='../pages/fiche-client.php?id={$row['id']}'>{$row['id']}</a></td>";
                            echo "<td>{$row['civilite']}</td>";
                            echo "<td>{$row['nom']}</td>";
                            echo "<td>{$row['prenom']}</td>";
                            echo "<td>{$row['mail']}</td>";
                            echo "<td>{$row['tel']}</td>";
                            echo "<td>{$row['date_naissance']}</td>";
                            echo "<td>{$row['adresse']}</td>";
                            echo "<td>{$row['info']}</td>";
                            echo "<td>{$row['codepostal']}</td>";
                            echo "<td>{$row['ville']}</td>";
                            echo "<td>{$row['date_creation']}</td>";
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
</body>
<footer>
    <?php require '../squelette/footer.php'; ?>
</footer>

</html>