<?php
require '../config.php'; // Inclure votre fichier de configuration

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    $commandId = $data['id'];

    try {
        // Débuter une transaction
        $pdo->beginTransaction();

        // Supprimer les enregistrements liés dans commande_detail_accoudoir
        $stmt1 = $pdo->prepare("DELETE FROM commande_detail_accoudoir WHERE id_commande_detail = :id");
        $stmt1->bindParam(':id', $commandId, PDO::PARAM_INT);
        $stmt1->execute();

        // Supprimer la commande principale dans commande_detail
        $stmt2 = $pdo->prepare("DELETE FROM commande_detail WHERE id = :id");
        $stmt2->bindParam(':id', $commandId, PDO::PARAM_INT);
        $stmt2->execute();

        // Valider la transaction
        $pdo->commit();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Aucun ID reçu.']);
}

?>
