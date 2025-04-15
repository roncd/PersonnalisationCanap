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

    // Étape 1 : Insérer dans commande_detail
    $sql = "INSERT INTO commande_detail (prix, commentaire, statut, id_client, id_structure, longueurA, longueurB, longueurC, id_banquette, id_mousse, id_accoudoir_bois, id_dossier_bois, id_couleur_bois, id_motif_bois, id_couleur_tissu_bois, id_decoration, id_nb_accoudoir)
            SELECT prix, commentaire, statut, id_client, id_structure, longueurA, longueurB, longueurC, id_banquette, id_mousse, id_accoudoir_bois, id_dossier_bois, id_couleur_bois, id_motif_bois, id_couleur_tissu_bois, id_decoration, id_nb_accoudoir
            FROM commande_temporaire
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    if (!$stmt->execute(['id' => $idCommande])) {
        throw new Exception("Erreur lors de l'insertion dans commande_detail");
    }

    // Récupérer l'ID de la commande_detail nouvellement insérée
    $lastCommandeDetailId = $pdo->lastInsertId();

    // Étape 2 : Récupérer les données de commande_temp_accoudoir
    $sql_temp_accoudoir = "SELECT id_accoudoir_bois, nb_accoudoir
                           FROM commande_temp_accoudoir
                           WHERE id_commande_temporaire = :idCommandeTemp";
    $stmt_temp_accoudoir = $pdo->prepare($sql_temp_accoudoir);
    $stmt_temp_accoudoir->execute(['idCommandeTemp' => $idCommande]);
    $accoudoirs = $stmt_temp_accoudoir->fetchAll(PDO::FETCH_ASSOC);

    // Étape 3 : Insérer les données dans commande_detail_accoudoir
    $sql_accoudoir = "INSERT INTO commande_detail_accoudoir (id_commande_detail, id_accoudoir_bois, nb_accoudoir)
                      VALUES (:idCommandeDetail, :idAccoudoirBois, :nbAccoudoir)";
    $stmt_accoudoir = $pdo->prepare($sql_accoudoir);

    foreach ($accoudoirs as $accoudoir) {
        if (!$stmt_accoudoir->execute([
            'idCommandeDetail' => $lastCommandeDetailId,
            'idAccoudoirBois' => $accoudoir['id_accoudoir_bois'],
            'nbAccoudoir' => $accoudoir['nb_accoudoir'],
        ])) {
            throw new Exception("Erreur lors de l'insertion dans commande_detail_accoudoir");
        }
    }

    // Valider la transaction
    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Commande transférée avec succès.", "new_id" => $lastCommandeDetailId]);
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $pdo->rollBack();
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
?>
