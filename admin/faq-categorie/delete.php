<?php
require '../config.php';
session_start();
require '../include/session_expiration.php';

if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['message'] = 'ID de la catégorie manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser-site.php");
    exit();
}

try {
    // Vérifier si la catégorie est utilisée dans des questions FAQ
    $check = $pdo->prepare("SELECT COUNT(*) FROM faq WHERE categorie_id = :id");
    $check->bindValue(':id', $id, PDO::PARAM_INT);
    $check->execute();
    $usedCount = $check->fetchColumn();

    if ($usedCount > 0) {
        $_SESSION['message'] = 'Impossible de supprimer cette catégorie : elle est utilisée dans des questions.';
        $_SESSION['message_type'] = 'error';
    } else {
        // Supprimer la catégorie
        $stmt = $pdo->prepare("DELETE FROM faq_categorie WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['message'] = 'La catégorie a été supprimée avec succès.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Catégorie introuvable.';
            $_SESSION['message_type'] = 'error';
        }
    }
} catch (Exception $e) {
    $_SESSION['message'] = 'Erreur lors de la suppression : ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
}

header("Location: visualiser-site.php");
exit();