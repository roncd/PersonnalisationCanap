<?php
require '../../admin/config.php'; // Connexion à la BDD

header('Content-Type: application/json');

try {
    $pdo->beginTransaction();

    // Récupération des données envoyées en JSON
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input['id']) || empty($input['id'])) {
        echo json_encode(["message" => "Erreur: ID de commande manquant"]);
        exit;
    }

    $idCommande = (int) $input['id']; // Sécurisation de l'ID

    // Insérer uniquement la commande avec cet ID
    $sql = "INSERT INTO commande_detail (prix, commentaire, statut, id_client, id_structure, longueurA, longueurB, longueurC, id_banquette, id_mousse, id_accoudoir_tissu, id_dossier_tissu, id_couleur_tissu, id_motif_tissu, id_modele, id_nb_accoudoir)
            SELECT prix, commentaire, statut, id_client, id_structure, longueurA, longueurB, longueurC, id_banquette, id_mousse, id_accoudoir_tissu, id_dossier_tissu, id_couleur_tissu, id_motif_tissu, id_modele, id_nb_accoudoir
            FROM commande_temporaire
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $idCommande]);
    if (!$stmt->execute(['id' => $idCommande])) {
        throw new Exception("Erreur lors de l'insertion dans commande_detail");
    }

    $lastCommandeDetailId = $pdo->lastInsertId();

    // Valider la transaction
    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Commande transférée avec succès.", "new_id" => $lastCommandeDetailId]);
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $pdo->rollBack();
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}

?>
