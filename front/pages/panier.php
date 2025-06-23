<?php
require '../../admin/config.php';
session_start();

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Gestion augmentation/diminution quantité
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Augmenter la quantité
    if (isset($_POST['ajouter_id'])) {
        $id = intval($_POST['ajouter_id']);
        foreach ($_SESSION['panier'] as $index => $item) {
            if ($item['id'] === $id) {
                $_SESSION['panier'][$index]['quantite']++;
                break;
            }
        }
    }

    // Réduire la quantité
    if (isset($_POST['reduire_id'])) {
        $id = intval($_POST['reduire_id']);
        foreach ($_SESSION['panier'] as $index => $item) {
            if ($item['id'] === $id) {
                $_SESSION['panier'][$index]['quantite']--;
                if ($_SESSION['panier'][$index]['quantite'] <= 0) {
                    unset($_SESSION['panier'][$index]);
                    $_SESSION['panier'] = array_values($_SESSION['panier']); // Réindexe
                }
                break;
            }
        }
    }

    // Redirection pour empêcher l’envoi répété du formulaire
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}


// Calcul total
$total = 0;
foreach ($_SESSION['panier'] as $item) {
    $total += $item['prix'] * $item['quantite'];
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon panier</title>
    <link rel="icon" href="../../medias/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="../../styles/panier.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
</head>

<body>
    <?php include '../cookies/index.html'; ?>
    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>

    <main>
        <section class="panier-container">
            <h1>Mon panier</h1>
            <div class="panier-actions">
                <a href="index.php" class="btn-grey">Continuer mes achats</a>
            </div>

            <div class="panier-table">
                <table>
                    <thead>
                        <tr>
                            <th>Produits</th>
                            <th>Quantité</th>
                            <th>Prix</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($_SESSION['panier'])): ?>
                        <?php foreach ($_SESSION['panier'] as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nom']) ?></td>
                            <td><?= $item['quantite'] ?></td> 
                            <td><?= number_format($item['prix'], 2) ?>€</td>
                            <td><?= number_format($item['prix'] * $item['quantite'], 2) ?>€</td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="reduire_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn-grey">-</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="ajouter_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn-grey">+</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; color:#888;">Votre panier est vide.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="total-label">Total du panier :</td>
                            <td class="total-value"><?= number_format($total, 2) ?>€</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="panier-recaps">
                <h2>Récapitulatif devis :</h2>
                <div class="recap-text">
                    <strong>Total : <?= number_format($total, 2) ?>€</strong>
                </div>
                <button class="btn-noir">Générer le devis</button>
            </div>
        </section>
    </main>

    <footer>
        <?php require '../../squelette/footer.php'; ?>
    </footer>
</body>

</html>