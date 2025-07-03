<?php
require '../../admin/config.php';
session_start();
require '../../admin/include/session_expiration.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../formulaire/Connexion.php");
    exit();
}

$id_client = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM panier WHERE id_client = ?");
$stmt->execute([$id_client]);
$panier = $stmt->fetch();

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
    <link rel="stylesheet" href="../../styles/popup.css">
    <link rel="stylesheet" href="../../styles/transition.css">
    <script type="module" src="../../script/transition.js"></script>
    <script type="module" src="../../script/popup-panier.js"></script>
</head>


<body>
    <?php include '../cookies/index.html'; ?>
    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>
    <main>
        <section class="panier-container">
            <h1 class="transition-all">Mon panier</h1>
            <div class="panier-actions transition-all">
                <a href="nosproduits.php" class="btn-beige">Continuer mes achats</a>
            </div>
            <?php if (!$panier): ?>
                <p style="text-align:center;">Votre panier est vide.</p>
            <?php else: $sql = "SELECT * FROM client WHERE id = :id_client";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id_client', $panier['id_client'], PDO::PARAM_INT);
                $stmt->execute();
                $client = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($client) {
                    $assocMail['client'][$panier['id_client']] = $client;
                } else {
                    $assocMail['client'][$panier['id_client']] = ['mail' => '-'];
                }
                $panier_id = $panier['id'];

                $stmt = $pdo->prepare("
                SELECT 
                    vp.id AS id_produit,
                    vp.nom,
                    vp.prix,
                    pd.quantite
                FROM panier_detail pd
                JOIN vente_produit vp ON pd.id_produit = vp.id
                WHERE pd.id_panier = ?
            ");
                $stmt->execute([$panier_id]);
                $produits = $stmt->fetchAll(PDO::FETCH_ASSOC); ?>
           
            <div class="panier-info">
                <div class="panier-table transition-all">
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
                            <?php if (!empty($produits)): ?>
                                <?php foreach ($produits as $produit): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($produit['nom']) ?></td>
                                        <td><?= intval($produit['quantite']) ?></td>
                                        <td><?= number_format($produit['prix'], 2) ?> €</td>
                                        <td><?= number_format($produit['prix'] * $produit['quantite'], 2) ?> €</td>
                                        <td>
                                            <div class="actions">
                                                <div class="quantity-selector">
                                                    <form action="../../admin/include/modif_panier.php" method="post">
                                                        <input type="hidden" name="action" value="decrement">
                                                        <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
                                                        <button type="submit">-</button>
                                                    </form>
                                                    <form action="../../admin/include/modif_panier.php" method="post">
                                                        <input type="hidden" name="action" value="increment">
                                                        <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
                                                        <button type="submit">+</button>
                                                    </form>
                                                </div>
                                                <form action="../../admin/include/modif_panier.php" method="post">
                                                    <input type="hidden" name="action" value="remove">
                                                    <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">
                                                    <button class="btn-noir" type="submit">Supprimer</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align:center;">Votre panier est vide.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="total-label">Total du panier :</td>
                                <td class="total-value"><?= number_format($panier['prix']) ?> €</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="panier-devis">
                    <div class="panier-recaps transition-boom">
                        <h2>Récapitulatif panier :</h2>
                        <div class="recap-text">
                            <strong>Total : <?= number_format($panier['prix']) ?> €</strong>
                        </div>
                        <button id="btn-generer" class="btn-noir">Générer le devis</button>
                    </div>
                </div>
            </div>
             <?php endif; ?>
        </section>
        <!-- Popup validation generation -->
        <div id="generer-popup" class="popup">
            <div class="popup-content">
                <h2>Êtes vous sûr de vouloir générer un devis ?</h2>
                <p>Vous ne pourrez plus effectuer de modifictions sur votre commande</p>
                <button id="btn-oui" class="btn-beige" name="envoyer" data-id="<?= htmlspecialchars($panier_id) ?>">Oui</button>
                <button id="btn-close" class="btn-noir">Non</button>
            </div>
        </div>

        <!-- Popup devis -->
        <div id="pdf-popup" class="popup">
            <div class="popup-content">
                <h2>Commande finalisé !</h2>
                <p>Votre devis a été créé et envoyé à l'adresse suivante :
                    </br><?php echo "<strong>" . htmlspecialchars($assocMail['client'][$panier['id_client']]['mail'] ?? '-') . "</strong>"; ?>
                </p>
                <br>
                <button onclick="location.href='../pages/paniers.php'" class="btn-beige">Voir mes commandes</button>
                <button id="pdf-btn" class="btn-noir">Voir le devis</button>
            </div>
        </div>
        </div>
    </main>

    <footer>
        <?php require '../../squelette/footer.php'; ?>
    </footer>
</body>

</html>