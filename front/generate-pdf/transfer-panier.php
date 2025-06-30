<?php
require '../../admin/config.php';

header('Content-Type: application/json');

try {
    // Démarrer la transaction
    $pdo->beginTransaction();

    // Récupération des données envoyées en JSON
    $input = json_decode(file_get_contents("php://input"), true);
    if (!$input || !isset($input['id']) || empty($input['id'])) {
        echo json_encode(["message" => "Erreur : ID de commande manquant"]);
        exit;
    }

    $idCommande = (int) $input['id']; // Sécurisation de l'ID

    // Étape 1 : Insérer dans panier_final
    $sql = "INSERT INTO panier_final (prix, id_client)
            SELECT prix, id_client
            FROM panier
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    if (!$stmt->execute(['id' => $idCommande])) {
        throw new Exception("Erreur lors de l'insertion dans panier_final");
    }

    // Récupérer l'ID de la panier_final nouvellement insérée
    $lastPanierFinalId = $pdo->lastInsertId();

    // Étape 2 : Récupérer les données de panier_detail
    $sql_temp_panier = "SELECT id_panier, id_produit, quantite
                           FROM panier_detail
                           WHERE id_panier = :idPanierTemp";
    $stmt_temp_panier = $pdo->prepare($sql_temp_panier);
    $stmt_temp_panier->execute(['idPanierTemp' => $idCommande]);
    $paniers = $stmt_temp_panier->fetchAll(PDO::FETCH_ASSOC);

    // Étape 3 : Insérer les données dans panier_detail_final
    $sql_panier = "INSERT INTO panier_detail_final (id_panier_final, id_produit, quantite)
                      VALUES (:idPanierFinal, :idProduit, :quantite)";
    $stmt_panier = $pdo->prepare($sql_panier);

    foreach ($paniers as $panier) {
        if (!$stmt_panier->execute([
            'idPanierFinal' => $lastPanierFinalId,
            'idProduit' => $panier['id_produit'],
            'quantite' => $panier['quantite'],
        ])) {
            throw new Exception("Erreur lors de l'insertion dans panier_detail_final");
        }
    }
    // Étape 4 : Supprimer les lignes de panier_detail associées
    $stmt = $pdo->prepare("DELETE FROM panier_detail WHERE id_panier = ?");
    $stmt->execute([$idCommande]);

    // Étape 5 : Supprimer le panier lui-même
    $stmt = $pdo->prepare("DELETE FROM panier WHERE id = ?");
    $stmt->execute([$idCommande]);
    
    // Valider la transaction
    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Commande transférée avec succès.", "new_id" => $lastPanierFinalId]);
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $pdo->rollBack();
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
