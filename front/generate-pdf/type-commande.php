<?php
//Passerelle entre fonction getTypeCommande et download.js
require 'getTypeCommande.php';
header('Content-Type: application/json');
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID manquant.']);
    exit;
}

$idCommande = intval($data['id']);
$type = getTypeCommande($idCommande);

if ($type) {
    echo json_encode(['success' => true, 'type' => $type]);
} else {
    echo json_encode(['success' => false, 'message' => 'Commande introuvable.']);
}