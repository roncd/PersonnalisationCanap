<?php
require '../config.php';
session_start();
require '../include/session_expiration.php';


if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}

function fetchData($pdo, $table, $columns = ['id', 'nom'])
{
    $cols = implode(', ', $columns);
    $stmt = $pdo->prepare("SELECT $cols FROM $table");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$clients = fetchData($pdo, 'client');
$produits = fetchData($pdo, 'vente_produit', ['id', 'nom', 'prix']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statut = trim($_POST['statut']);
    $idClient = trim($_POST['client']);
    $produitsChoisis = $_POST['produits'] ?? [];

    $idsProduits = []; 
    $details = [];  

    foreach ($produitsChoisis as $item) {
        $idProduit = $item['id'] ?? null;
        $quantite = $item['quantite'] ?? 0;

        if ($idProduit && $quantite > 0) {
            $idsProduits[] = $idProduit;
            $details[] = [
                'id_produit' => $idProduit,
                'quantite' => $quantite
            ];
        }
    }

    // Vérifie que les détails ont été remplis
    if (empty($idClient) || empty($details)) {
        $_SESSION['message'] = 'Tous les champs sont requis !';
        $_SESSION['message_type'] = 'error';
        header("Location: add.php");
        exit();
    }

    $prixTotal = 0;

    // Ne pas exécuter la requête SQL si aucun ID produit n’est sélectionné
    if (!empty($idsProduits)) {
        $placeholders = implode(',', array_fill(0, count($idsProduits), '?'));
        $stmtPrix = $pdo->prepare("SELECT id, prix FROM vente_produit WHERE id IN ($placeholders)");
        $stmtPrix->execute($idsProduits);
        $prixProduits = $stmtPrix->fetchAll(PDO::FETCH_KEY_PAIR); // [id => prix]

        // Calcul du prix total
        foreach ($details as $item) {
            $idProduit = $item['id_produit'];
            $quantite = $item['quantite'];

            if (isset($prixProduits[$idProduit])) {
                $prixUnitaire = $prixProduits[$idProduit];
                $prixTotal += $prixUnitaire * $quantite;
            }
        }
    }
    try {
        // Insertion dans panier_final
        $stmt = $pdo->prepare("INSERT INTO panier_final (statut, id_client, prix) VALUES (?, ?, ?)");
        $stmt->execute([$statut, $idClient, $prixTotal]);
        $idCommande = $pdo->lastInsertId();

        // Insertion dans panier_detail_final
        $stmtDetail = $pdo->prepare("INSERT INTO panier_detail_final (id_panier_final, id_produit, quantite) VALUES (?, ?, ?)");

        foreach ($produitsChoisis as $item) {
            $idProduit = $item['id'] ?? null;
            $quantite = $item['quantite'] ?? null;

            if (!empty($idProduit) && $quantite > 0) {
                $stmtDetail->execute([$idCommande, intval($idProduit), intval($quantite)]);
            }
        }
        $_SESSION['message'] = 'Panier client ajouté avec succès !';
        $_SESSION['message_type'] = 'success';
        header("Location: index.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur : " . $e->getMessage();
        $_SESSION['message_type'] = 'error';
        header("Location: add.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajoute une commande - Panier</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/admin/ajout.css">
    <link rel="stylesheet" href="../../styles/message.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <script src="../../script/addPanier.js"></script>

</head>

<body>

    <header>
        <?php require '../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
            <h2>Ajoute une commande - Panier</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form class="formulaire-creation-compte" action="" method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="client">Référence Client <span class="required">*</span></label>
                            <select class="input-field" id="client" name="client">
                                <option value="" disabled selected>-- Sélectionnez une option --</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?= htmlspecialchars($client['id']) ?>">
                                        <?= htmlspecialchars($client['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="statut">Statut de la commande</label>
                            <select class="input-field" id="statut" name="statut">
                                <option value="validation">En attente de validation</option>
                                <option value="traitement">En traitement</option>
                                <option value="final">Finalisées</option>
                            </select>
                        </div>
                    </div>

                    <div class="produits-container" id="produits-container">
                        <p for="produits">Produits du panier <span class="required">*</span></p>
                        <div class="form-row produit-row">
                            <div class="form-group">
                                <div>
                                    <select name="produits[0][id]" class="input-field produit-select" onchange="updateOptions()">
                                        <option value="" disabled selected>-- Sélectionnez un produit --</option>
                                        <?php foreach ($produits as $produit): ?>
                                            <option value="<?= $produit['id'] ?>">
                                                <?= htmlspecialchars($produit['nom']) ?> (<?= number_format($produit['prix'], 2, ',', ' ') ?> EUR)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="number" name="produits[0][quantite]" placeholder="Quantité" min="1" value="1" class="input-field" />
                                    <button type="button" class="btn-noir" onclick="removeRow(this)">Supprimer</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn-noir" onclick="addProduit()">+ Ajouter un produit</button>
                    <div class="button-section">
                        <div class="buttons">
                            <button type="button" id="btn-retour" class="btn-beige" onclick="history.go(-1)">Retour</button>
                            <input type="submit" class="btn-noir" value="Ajouter"></input>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
<footer>
    <?php require '../squelette/footer.php'; ?>
</footer>

</html>