<?php
session_start();

if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php"); // Redirection vers la page de connexion
    exit();
}

require '../config.php';

$id = $_SESSION['id'];

$stmt = $pdo->prepare("SELECT prenom FROM utilisateur WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $prenom = $user['prenom'];
} else {
    $prenom = 'Utilisateur';
}

$limit = 5;

$table = 'client';

function fetchData($pdo, $table)
{
    $stmt = $pdo->prepare("SELECT id, nom FROM $table");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$assocData = [];
$data[$table] = fetchData($pdo, $table);
// Convertir en tableau associatif clé=id, valeur=nom
$assocData[$table] = array_column($data[$table], 'nom', 'id');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Déco Du Monde</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link rel="stylesheet" href="../../styles/admin/landing-page.css">
</head>

<body>
    <header>
        <?php require '../squelette/header.php'; ?>
    </header>

    <main>
        <div class="container">
            <div>
                <h1>Bonjour <?php echo htmlspecialchars($prenom); ?>,</h1>
                <p>Vous êtes bien connecté à l'administration de Déco du Monde</p>
            </div>
            <div class="tables">
                <div class="table-box">
                    <h2>5 DERNIÈRES COMMANDES (canapé)<a href="commande.php">Voir plus</a></h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Date</th>
                                <th>Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->prepare("SELECT id, id_client, date, prix FROM commande_detail ORDER BY id DESC LIMIT :limit");
                            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                            $stmt->execute();
                            while ($commande = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td style='height: 30px;'>{$commande['id']}</td>";
                                echo "<td>" . htmlspecialchars($assocData['client'][$commande['id_client']] ?? '-') . "</td>";
                                echo "<td style='height: 30px;'>{$commande['date']}</td>";
                                echo "<td style='height: 30px;'>{$commande['prix']}€</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="table-box">
                    <h2>5 DERNIERS PANIERS <a href="panier.php">Voir plus</a></h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Date</th>
                                <th>Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->prepare("SELECT id, id_client, date, prix FROM panier_final ORDER BY id DESC LIMIT :limit");
                            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                            $stmt->execute();
                            while ($commande = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td style='height: 30px;'>{$commande['id']}</td>";
                                echo "<td>" . htmlspecialchars($assocData['client'][$commande['id_client']] ?? '-') . "</td>";
                                echo "<td style='height: 30px;'>{$commande['date']}</td>";
                                echo "<td style='height: 30px;'>{$commande['prix']}€</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>


            </div>

            <div class="tables">
                <div class="table-box client">
                    <h2>5 DERNIERS CLIENTS <a href="../client/visualiser.php">Voir plus</a></h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->prepare("SELECT id, nom, prenom FROM client ORDER BY id DESC LIMIT :limit");
                            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td style='height: 30px;'>{$row['id']}</td>";
                                echo "<td style='height: 30px;'>{$row['nom']}</td>";
                                echo "<td style='height: 30px;'>{$row['prenom']}</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
    </main>
    <footer>
        <?php require '../squelette/footer.php'; ?>
    </footer>
</body>

</html>