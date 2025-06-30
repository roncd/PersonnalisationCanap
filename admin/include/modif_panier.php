<?php
session_start();
require '../../admin/config.php';

if (!isset($_SESSION['user_id'], $_POST['id_produit'], $_POST['action'])) {
    header('Location: ../../front/pages/panier.php');
    exit;
}

$id_client = $_SESSION['user_id'];
$id_produit = intval($_POST['id_produit']);
$action = $_POST['action'];

// Récupère le panier du client
$stmt = $pdo->prepare("SELECT * FROM panier WHERE id_client = ?");
$stmt->execute([$id_client]);
$panier = $stmt->fetch();

if (!$panier) {
    header('Location: ../../front/pages/panier.php');
    exit;
}


$panier_id = $panier['id'];

$stmt = $pdo->prepare("SELECT * FROM panier_detail WHERE id_panier = ? AND id_produit = ?");
$stmt->execute([$panier_id, $id_produit]);
$detail = $stmt->fetch();

if ($detail) {
    switch ($action) {
        case 'increment':
            $stmt = $pdo->prepare("UPDATE panier_detail SET quantite = quantite + 1 WHERE id_panier = ? AND id_produit = ?");
            $stmt->execute([$panier_id, $id_produit]);
            break;
        case 'decrement':
            if ($detail['quantite'] > 1) {
                $stmt = $pdo->prepare("UPDATE panier_detail SET quantite = quantite - 1 WHERE id_panier = ? AND id_produit = ?");
                $stmt->execute([$panier_id, $id_produit]);
            } else {
                $stmt = $pdo->prepare("DELETE FROM panier_detail WHERE id_panier = ? AND id_produit = ?");
                $stmt->execute([$panier_id, $id_produit]);

                $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM panier_detail WHERE id_panier = ?");
                $stmt_check->execute([$panier_id]);
                if ($stmt_check->fetchColumn() == 0) {
                    $stmt_panier = $pdo->prepare("DELETE FROM panier WHERE id = ?");
                    $stmt_panier->execute([$panier_id]);
                }
            }
            break;

        case 'remove':
            $stmt = $pdo->prepare("DELETE FROM panier_detail WHERE id_panier = ? AND id_produit = ?");
            $stmt->execute([$panier_id, $id_produit]);

            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM panier_detail WHERE id_panier = ?");
            $stmt_check->execute([$panier_id]);
            if ($stmt_check->fetchColumn() == 0) {
                $stmt_panier = $pdo->prepare("DELETE FROM panier WHERE id = ?");
                $stmt_panier->execute([$panier_id]);
            }
            break;
    }
}

if ($panier) {
    $stmt = $pdo->prepare("
        SELECT pd.quantite, vp.prix
        FROM panier_detail pd
        JOIN vente_produit vp ON pd.id_produit = vp.id
        WHERE pd.id_panier = ?
    ");
    $stmt->execute([$panier_id]);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recalculer le total
    $nouveau_total = 0;
    foreach ($articles as $article) {
        $nouveau_total += $article['quantite'] * $article['prix'];
    }

    // Mettre à jour le prix total dans la table panier
    $stmt = $pdo->prepare("UPDATE panier SET prix = ? WHERE id = ?");
    $stmt->execute([$nouveau_total, $panier_id]);
}
// Redirection
header('Location: ../../front/pages/panier.php');
exit;
