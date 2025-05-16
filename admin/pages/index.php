<!DOCTYPE html>
<html lang="en">
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
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Déco Du Monde</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link rel="stylesheet" href="../../styles/admin/landing-page.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
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
                <div class="table-box client">
                    <h2>5 DERNIER CLIENTS</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="3" style="height: 30px;"></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="height: 30px;"></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="height: 30px;"></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="height: 30px;"></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="height: 30px;"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="table-box">
                    <h2>5 DERNIÈRE COMMANDES</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Date</th>
                                <th>Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" style="height: 30px;"></td>
                            </tr>
                            <tr>
                                <td colspan="5" style="height: 30px;"></td>
                            </tr>
                            <tr>
                                <td colspan="5" style="height: 30px;"></td>
                            </tr>
                            <tr>
                                <td colspan="5" style="height: 30px;"></td>
                            </tr>
                            <tr>
                                <td colspan="5" style="height: 30px;"></td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tables">
                <a href="../pages/visualiser.php">
                    <div class="table-box img1">
                        <h2>VISUALISER DES DONNÉES</h2>
                    </div>
                </a>
                <a href="../pages/ajouter.php">
                    <div class="table-box img2">
                        <h2>AJOUTER DES DONNÉES</h2>
                    </div>
                </a>
            </div>
        </div>
    </main>
    <footer>
        <?php require '../squelette/footer.php'; ?>
    </footer>
</body>

</html>