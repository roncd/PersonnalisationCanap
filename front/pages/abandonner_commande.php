<?php
require '../../admin/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  http_response_code(401); // Non autorisé
  echo json_encode(["success" => false, "message" => "Utilisateur non connecté"]);
  exit;
}

$id_client = $_SESSION['user_id'];

// Vérifier si une commande temporaire existe déjà pour cet utilisateur
$stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ?");
$stmt->execute([$id_client]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Suppression des accoudoirs associés
$stmt = $pdo->prepare("DELETE FROM commande_temp_accoudoir WHERE id_commande_temporaire = :id");
$stmt->bindValue(':id', $order['id'], PDO::PARAM_INT);
$stmt->execute();
$image = $stmt->fetchColumn();

// Supprimer la commande temporaire liée à l'utilisateur
$stmt = $pdo->prepare("DELETE FROM commande_temporaire WHERE id_client = ?");
$stmt->execute([$id_client]);

echo json_encode(["success" => true]);
exit;
