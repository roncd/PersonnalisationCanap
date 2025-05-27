<?php
require '../../admin/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  http_response_code(401); // Non autorisé
  echo json_encode(["success" => false, "message" => "Utilisateur non connecté"]);
  exit;
}

$id_client = $_SESSION['user_id'];

// Supprimer la commande temporaire liée à l'utilisateur
$stmt = $pdo->prepare("DELETE FROM commande_temporaire WHERE id_client = ?");
$stmt->execute([$id_client]);

echo json_encode(["success" => true]);
exit;
?>
