<?php
require '../config.php'; // Inclure votre fichier de configuration

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    $commandId = $data['id'];

    try {
        // Débuter une transaction
        $pdo->beginTransaction();

        // Supprimer les enregistrements liés dans panier_detail_final
        $stmt1 = $pdo->prepare("DELETE FROM panier_detail_final WHERE id_panier_final = :id");
        $stmt1->bindParam(':id', $commandId, PDO::PARAM_INT);
        $stmt1->execute();

        // Supprimer la commande principale dans panier_final
        $stmt2 = $pdo->prepare("DELETE FROM panier_final WHERE id = :id");
        $stmt2->bindParam(':id', $commandId, PDO::PARAM_INT);
        $stmt2->execute();

        // Valider la transaction
        $pdo->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Commande supprimé avec succès !'
        ]);
    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Une erreur est survenue, veuillez réessayer.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Aucun ID reçu.'
    ]);
}
