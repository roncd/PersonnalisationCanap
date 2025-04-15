<?php
require '../config.php'; // Connexion à la base de données

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !isset($data['statut'])) {
    echo json_encode(['success' => false, 'error' => 'Paramètres manquants.']);
    exit;
}

$id = (int) $data['id']; // Sécuriser l'entrée
$statut = htmlspecialchars($data['statut']); // Sécuriser l'entrée

try {
    $stmt = $pdo->prepare("UPDATE commande_detail SET statut = ? WHERE id = ?");
    $stmt->execute([$statut, $id]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
