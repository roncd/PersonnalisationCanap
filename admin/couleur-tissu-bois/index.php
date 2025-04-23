<?php
require '../config.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}
$search = $_GET['search'] ?? '';

// Paramètres de pagination
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$limit = 10; // Nombre de commandes par page
$offset = ($page - 1) * $limit;

// Compter le nombre total de commandes pour ce statut
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
    <title>Couleur tissu bois</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/tab.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link rel="stylesheet" href="../../styles/message.css">
</head>

<body>

    <header>
        <?php require '../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
            <h2>Couleur tissu bois</h2>
            <div class="search-bar">
                <form method="GET" action="index.php">
                    <input type="text" name="search" placeholder="Rechercher par nom..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">Rechercher</button>
                </form>
            </div>
            <?php require '../include/message.php'; ?>
            <div class="tab-container">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NOM</th>
                            <th>PRIX</th>
                            <th>IMAGE</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($search) {
                            $stmt = $pdo->prepare("SELECT * FROM couleur_tissu_bois WHERE nom LIKE :search ORDER BY id DESC");
                            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
                        } else {
                            $stmt = $pdo->prepare("SELECT * FROM couleur_tissu_bois ORDER BY id DESC LIMIT :limit OFFSET :offset");
                            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                        }
                        $stmt->execute();
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>{$row['id']}</td>";
                            echo "<td>{$row['nom']}</td>";
                            echo "<td>{$row['prix']}</td>";
                            echo "<td><img src='../uploads/couleur-tissu-bois/{$row['img']}' alt='{$row['nom']}' style='width:50px; height:auto;'></td>";
                            echo "<td class='actions'>";
                            echo "<a href='edit.php?id={$row['id']}' class='edit-action actions vert' title='Modifier'>EDIT</a>";
                            echo "<a href='delete.php?id={$row['id']}' class='delete-action actions rouge' title='Supprimer' onclick='return confirm(\"Voulez-vous vraiment supprimer cette couleur de tissu ?\");'>DELETE</a>";
                            echo "</td>";
                            echo "</tr>";
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