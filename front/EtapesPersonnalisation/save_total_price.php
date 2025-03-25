<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté.']);
    exit;
}

// Récupérer les données envoyées par la requête POST
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id']) || !isset($data['total_price'])) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

$user_id = $data['user_id'];
$total_price = $data['total_price'];

// Mettre à jour la table commande_temporaire
$stmt = $pdo->prepare("UPDATE commande_temporaire SET prix = ? WHERE id_client = ?");
$success = $stmt->execute([$total_price, $user_id]);

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour de la base de données.']);
}
?>
