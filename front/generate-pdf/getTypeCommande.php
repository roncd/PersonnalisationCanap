<?php
require '../../admin/config.php'; 

//fonction pour récupéré le type de commande
function getTypeCommande($idCommande) {
    global $pdo;

    $sql = "SELECT td.nom AS type
            FROM commande_detail c
            INNER JOIN type_banquette td ON c.id_banquette = td.id
            WHERE c.id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $idCommande, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        return $result['type'];
    } else {
        return null;
    }
}