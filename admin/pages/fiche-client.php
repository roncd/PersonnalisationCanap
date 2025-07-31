<?php
require '../config.php';
session_start();
require '../include/session_expiration.php';

if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php"); // Redirection vers la page de connexion
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['message'] = 'ID client manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: ../client/visualiser.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM client WHERE id = ?");
$stmt->execute([$id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    $_SESSION['message'] = 'Client introuvable.';
    $_SESSION['message_type'] = 'error';
    header("Location: ../client/visualiser.php");
    exit();
}

$limit = 5;

//Calcul age a partir date naissance
function age($date)
{
    if ($date === '0000-00-00' || empty($date)) {
        return null; 
    }
    $timestamp = strtotime($date);
    if (!$timestamp) {
        return null;
    }
    $annee = date('Y', $timestamp);
    $age = date('Y') - $annee;

    if (date('md') < date('md', $timestamp)) {
        $age--;
    }
    return $age;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche Client</title>
    <link rel="icon" type="image/png" href="https://www.decorient.fr/medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../styles/admin/fiche-client.css">

</head>

<body>
    <header>
        <?php require '../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
            <h1>Fiche client</h1>
            <?php if ($client): ?>
                <div class="client-grid">
                    <!-- Bloc identité -->
                    <section class="info-card">
                        <h2>
                            <?= htmlspecialchars($client['nom']) . ' ' . htmlspecialchars($client['prenom']) . ' [' . htmlspecialchars($client['id']) . ']' ?>
                        </h2>
                        <p>
                            Titre de civilité : <?= ($client['civilite']) ?> <br>
                            <?php
                            $age = age($client['date_naissance']);
                            ?>
                            Âge : <?php if ($age !== null) : ?>
                                <?php echo $age; ?> ans (date de naissance : <?= htmlspecialchars($client['date_naissance']) ?>)
                            <?php else: ?>
                                <span>-</span>
                            <?php endif; ?><br>
                            Date d'inscription : <?= htmlspecialchars($client['date_creation']) ?><br>
                        </p>
                    </section>

                    <!-- Bloc commandes -->
                    <section class="info-card commandes-card block-commandes">
                        <h2>5 DERNIÈRES COMMANDES (canapé) <?= "<a href='../commande-detail/index.php?search=" . urlencode($client['id']) . "'>Voir plus</a>"; ?></h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                $stmt = $pdo->prepare("SELECT id, id_client, date, prix, statut FROM commande_detail WHERE id_client = :id_client ORDER BY id DESC LIMIT :limit");
                                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                                $stmt->bindValue(':id_client', $id, PDO::PARAM_INT);
                                $stmt->execute();
                                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                if (!empty($results)) {
                                    foreach ($results as $commande) {
                                        echo "<tr>";
                                        echo "<td '>{$commande['id']}</td>";
                                        echo "<td '>{$commande['date']}</td>";
                                        echo "<td '>{$commande['statut']}</td>";
                                        echo "<td '>{$commande['prix']}€</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>Aucune commande trouvée pour ce client.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </section>

                    <!-- Bloc contact -->
                    <section class="info-card block-contact">
                        <h2>CONTACT</h2>
                        <p>Adresse mail : <?= htmlspecialchars($client['mail']) ?><br>
                            Téléphone : <?= htmlspecialchars($client['tel']) ?></p>
                    </section>

                    <!-- Bloc adresse -->
                    <section class="info-card block-adresse">
                        <h2>ADRESSE</h2>
                        <p>Adresse : <?= htmlspecialchars($client['adresse']) ?><br>
                            Code postale : <?= htmlspecialchars($client['codepostal']) ?><br>
                            Informations supplémentaires : <?= ($client['info']) ?></p>
                    </section>

                     <section class="info-card commandes-card block-panier">
                        <h2>5 DERNIERS PANIERS <?= "<a href='../panier/index.php?search=" . urlencode($client['id']) . "'>Voir plus</a>"; ?></h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                $stmt = $pdo->prepare("SELECT id, id_client, date, prix, statut FROM panier_final WHERE id_client = :id_client ORDER BY id DESC LIMIT :limit");
                                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                                $stmt->bindValue(':id_client', $id, PDO::PARAM_INT);
                                $stmt->execute();
                                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                if (!empty($results)) {
                                    foreach ($results as $commande) {
                                        echo "<tr>";
                                        echo "<td '>{$commande['id']}</td>";
                                        echo "<td '>{$commande['date']}</td>";
                                        echo "<td '>{$commande['statut']}</td>";
                                        echo "<td '>{$commande['prix']}€</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>Aucune commande trouvée pour ce client.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </section>
                <?php else: ?>
                    <p>Utilisateur non trouvé.</p>
                <?php endif; ?>
                </div>
        </div>
    </main>
    <footer>
        <?php require '../squelette/footer.php'; ?>
    </footer>
</body>

</html>