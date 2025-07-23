<?php
require '../config.php';
session_start();
require '../include/session_expiration.php';


if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['message'] = 'ID de la commande manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: index.php");
    exit();
}

// Récupérer les données actuelles de la commande
$stmt = $pdo->prepare("SELECT * FROM panier_final WHERE  id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$commande) {
    $_SESSION['message'] = 'Commande introuvable.';
    $_SESSION['message_type'] = 'error';
    header("Location: index.php");
    exit();
}

$stmtProduits = $pdo->prepare("SELECT pdf.id_produit, pdf.quantite
    FROM panier_detail_final pdf
    WHERE pdf.id_panier_final = ?");
$stmtProduits->execute([$commande['id']]);
$produitsCommande = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);

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
    $date = trim($_POST['date']);
    $statut = trim($_POST['statut']);
    $idClient = trim($_POST['client']);
    $produitsChoisis = $_POST['produits'] ?? [];

    $idsProduits = [];
    $details = [];

    // Construire les détails produits valides
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

    // Vérifier les champs obligatoires
    if (empty($idClient) || empty($details)) {
        $_SESSION['message'] = 'Tous les champs requis doivent être remplis.';
        $_SESSION['message_type'] = 'error';
        header("Location: edit.php?id=" . $commande['id']);
        exit();
    }

    // Supprimer les anciens produits du panier
    $pdo->prepare("DELETE FROM panier_detail_final WHERE id_panier_final = ?")->execute([$commande['id']]);

    $prixTotal = 0;

    // Si produits à recalculer
    if (!empty($idsProduits)) {
        $placeholders = implode(',', array_fill(0, count($idsProduits), '?'));
        $stmtPrix = $pdo->prepare("SELECT id, prix FROM vente_produit WHERE id IN ($placeholders)");
        $stmtPrix->execute($idsProduits);
        $prixProduits = $stmtPrix->fetchAll(PDO::FETCH_KEY_PAIR); // [id => prix]

        // Réinsertion des produits avec quantités et calcul prix
        foreach ($details as $item) {
            $idProduit = $item['id_produit'];
            $quantite = $item['quantite'];

            if (isset($prixProduits[$idProduit])) {
                $prixUnitaire = $prixProduits[$idProduit];

                // Insertion
                $stmt = $pdo->prepare("INSERT INTO panier_detail_final (id_panier_final, id_produit, quantite) VALUES (?, ?, ?)");
                $stmt->execute([$commande['id'], $idProduit, $quantite]);

                // Ajout au total
                $prixTotal += $prixUnitaire * $quantite;
            }
        }
    }

    // Mise à jour de la commande
    $stmt = $pdo->prepare("UPDATE panier_final SET prix = ?, date = ?, statut = ?, id_client = ? WHERE id = ?");
    if ($stmt->execute([$prixTotal, $date, $statut, $idClient, $commande['id']])) {
        $_SESSION['message'] = 'La commande a été mise à jour avec succès !';
        $_SESSION['message_type'] = 'success';
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['message'] = 'Erreur lors de la mise à jour de la commande.';
        $_SESSION['message_type'] = 'error';
    }
}

$valeurs = [
    "validation"   => "En attente de validation",
    "traitement" => "En cours de traitement",
    "livraison" => "En cours de livraison",
    "final"        => "Commande finalisée"
];
$selected = (string) $commande['statut'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifie une commande - Panier</title>
    <link rel="icon" type="image/png" href="https://www.decorient.fr/medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/admin/form.css">
    <link rel="stylesheet" href="../../styles/message.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
</head>

<body>

    <header>
        <?php require '../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
            <h2>Modifie une commande - Panier</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form action="edit.php?id=<?php echo $commande['id']; ?>" method="POST" enctype="multipart/form-data" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="client">Référence Client <span class="required">*</span></label>
                            <select class="input-field" id="client" name="client" required>
                                <option value="">-- Sélectionnez une option --</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?= htmlspecialchars($client['id']) ?>"
                                        <?= ($client['id'] == $commande['id_client']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($client['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="date">Date de création <span class="required">*</span></label>
                            <input type="datetime-local" id="date" name="date" value="<?php echo htmlspecialchars($commande['date']); ?>" class="input-field" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="statut">Statut de la commande</label>
                            <select class="input-field" id="statut" name="statut">
                                <?php foreach ($valeurs as $valeur => $libelle): ?>
                                    <option value="<?php echo $valeur; ?>" <?php if ($selected === $valeur) echo 'selected'; ?>>
                                        <?php echo $libelle; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="produits-container" id="produits-container">
                        <p for="produits">Produits du panier <span class="required">*</span></p>
                        <?php if (!empty($produitsCommande)): ?>
                            <?php foreach ($produitsCommande as $index => $prod): ?>
                                <div class="form-row produit-row">
                                    <div class="form-group">
                                        <div>
                                            <select name="produits[<?= $index ?>][id]" class="input-field produit-select" onchange="updateOptions()">
                                                <option value="">-- Sélectionnez un produit --</option>
                                                <?php foreach ($produits as $produit): ?>
                                                    <option value="<?= $produit['id'] ?>"
                                                        <?= ($produit['id'] == $prod['id_produit']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($produit['nom']) ?> (<?= number_format($produit['prix'], 2, ',', ' ') ?> EUR)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="number" name="produits[<?= $index ?>][quantite]" placeholder="Quantité" min="1"
                                                value="<?= htmlspecialchars($prod['quantite']) ?>" class="input-field" />
                                            <button type="button" class="btn-noir" onclick="removeRow(this)">Supprimer</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                        <?php else: ?>
                            <!-- Fallback si aucun produit trouvé -->
                            <div class="form-row produit-row">
                                <div class="form-group">
                                    <div>
                                        <select name="produits[0][id]" class="input-field produit-select" onchange="updateOptions()">
                                            <option value="">-- Sélectionnez un produit --</option>
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
                        <?php endif; ?>
                    </div>
                    <button type="button" id="add-product-button" class="btn-noir" onclick="addProductRow()">Ajouter un produit</button>

                    <div class="button-section">
                        <div class="buttons">
                            <button type="button" id="btn-retour" class="btn-beige" onclick="history.go(-1)">Retour</button>
                            <input type="submit" class="btn-noir" value="Mettre à jour"></input>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <script>
            let produitIndex = <?= count($produitsCommande) ?>;

            function addProductRow() {
                const container = document.getElementById('produits-container');

                const row = document.createElement('div');
                row.classList.add('form-row', 'produit-row');

                const formGroup = document.createElement('div');
                formGroup.classList.add('form-group');

                const wrapper = document.createElement('div');

                const select = document.createElement('select');
                select.name = `produits[${produitIndex}][id]`;
                select.className = 'input-field produit-select';
                select.innerHTML = `<option value="">-- Sélectionnez un produit --</option>`;
                <?php foreach ($produits as $produit): ?>
                    select.innerHTML += `<option value="<?= $produit['id'] ?>">
            <?= htmlspecialchars($produit['nom']) ?> (<?= number_format($produit['prix'], 2, ',', ' ') ?> EUR)
        </option>`;
                <?php endforeach; ?>
                select.addEventListener('change', updateOptions);

                const input = document.createElement('input');
                input.type = 'number';
                input.name = `produits[${produitIndex}][quantite]`;
                input.placeholder = 'Quantité';
                input.min = '1';
                input.value = '1';
                input.className = 'input-field';

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'btn-noir';
                button.textContent = 'Supprimer';
                button.addEventListener('click', function() {
                    removeRow(button);
                });

                wrapper.appendChild(select);
                wrapper.appendChild(input);
                wrapper.appendChild(button);
                formGroup.appendChild(wrapper);
                row.appendChild(formGroup);
                container.appendChild(row);

                produitIndex++;
                updateOptions();

                if (document.querySelectorAll('.produit-select').length >= <?= count($produits) ?>) {
                    document.getElementById('add-product-button').disabled = true;
                }
            }


            function removeRow(button) {
                const row = button.closest('.form-row');
                row.remove();
                updateOptions();
                document.getElementById('add-product-button').disabled = false;

            }

            function updateOptions() {
                setTimeout(() => {
                    const selects = document.querySelectorAll('.produit-select');
                    const selectedValues = Array.from(selects).map(s => s.value).filter(Boolean);

                    selects.forEach(select => {
                        const currentValue = select.value;

                        Array.from(select.options).forEach(option => {
                            if (option.value === "" || option.value === currentValue) {
                                option.disabled = false;
                            } else {
                                option.disabled = selectedValues.includes(option.value);
                            }
                        });
                    });
                }, 0);
            }

            // Attacher les événements onchange aux selects existants au chargement
            document.querySelectorAll('.produit-select').forEach(select => {
                select.addEventListener('change', updateOptions);
            });
            updateOptions();
        </script>

    </main>
    <footer>
        <?php require '../squelette/footer.php'; ?>
    </footer>
</body>

</html