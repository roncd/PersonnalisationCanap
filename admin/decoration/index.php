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
$stmtCount = $pdo->prepare("SELECT COUNT(*) AS total FROM decoration");
$stmtCount->execute();
$totalCommandes = $stmtCount->fetchColumn();

$totalPages = ceil($totalCommandes / $limit);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Décoration</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/tab.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/commandes.css">
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <style>
        /* Styles pour la barre de recherche et les messages */
        .search-bar {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: rgba(227, 209, 200, 0.8);
            padding: 10px;
            border-radius: 5px;
        }

        .search-bar input {
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 10px;
            width: 300px;
        }

        .search-bar button {
            padding: 8px 12px;
            font-size: 16px;
            color: white;
            background-color: #000;
            border: none;
            border-radius: 10px;
            margin-left: 8px;
            cursor: pointer;
        }

        .search-bar button:hover {
            background-color: #333;
        }

        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>

    <header>
        <?php require '../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
            <h2>Décoration</h2>
            <?php
            if (isset($_SESSION['message'])) {
                echo '<div class="message ' . htmlspecialchars($_SESSION['message_type']) . '">';
                echo htmlspecialchars($_SESSION['message']);
                echo '</div>';
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            }
            ?>
            <div class="search-bar">
                <form method="GET" action="index.php">
                    <input type="text" name="search" placeholder="Rechercher par nom..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">Rechercher</button>
                </form>
            </div>
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
                            $stmt = $pdo->prepare("SELECT * FROM decoration WHERE nom LIKE :search ORDER BY id DESC");
                            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
                        } else {
                            $stmt = $pdo->prepare("SELECT * FROM decoration ORDER BY id DESC LIMIT :limit OFFSET :offset");
                            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                        }
                        $stmt->execute();
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>{$row['id']}</td>";
                            echo "<td>{$row['nom']}</td>";
                            echo "<td>{$row['prix']}</td>";
                            echo "<td><img src='../uploads/decoration/{$row['img']}' alt='{$row['nom']}' style='width:50px; height:auto;'></td>";
                            echo "<td class='actions'>";
                            echo "<a href='edit.php?id={$row['id']}' class='edit-action actions vert' title='Modifier'>EDIT</a>";
                            echo "<a href='delete.php?id={$row['id']}' class='delete-action actions rouge' title='Supprimer' onclick='return confirm(\"Voulez-vous vraiment supprimer cette structure ?\");'>DELETE</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php
            if (!$search) { 
                echo '<nav class="nav" aria-label="pagination">';
                echo '<ul class="pagination">';

                if ($page > 1) {
                    echo '<li><a href="?page=' . ($page - 1) . '">Précédent</a></li>';
                }

                for ($i = 1; $i <= $totalPages; $i++) {
                    echo '<li>';
                    echo '<a class="' . ($i === $page ? 'active' : '') . '" href="?page=' . $i . '">' . $i . '</a>';
                    echo '</li>';
                }

                if ($page < $totalPages) {
                    echo '<li><a href="?page=' . ($page + 1) . '">Suivant</a></li>';
                }

                echo '</ul>';
                echo '</nav>';
            }
            ?>
        </div>
    </main>
    <footer>
        <?php require '../squelette/footer.php'; ?>
    </footer>
</body>

</html>