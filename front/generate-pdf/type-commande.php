<?php
require '../../admin/config.php'; // Connexion à la base de données

header('Content-Type: application/json');
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID manquant.']);
    exit;
}

$idCommande = intval($data['id']);

// Récupérer le type en fonction de l'ID de la commande
$sql = "SELECT td.nom AS type
        FROM commande_detail c
        INNER JOIN type_banquette td ON c.id_banquette = td.id
        WHERE c.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $idCommande, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    echo json_encode([
        'success' => true,
        'type' => $result['type'] // Renvoie 'bois' ou 'tissu'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Commande introuvable.'
    ]);
}

?>