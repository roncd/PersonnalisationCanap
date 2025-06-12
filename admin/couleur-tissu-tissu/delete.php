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
    $_SESSION['message'] = 'ID de la couleur du tissu manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}

try {
    // Récupérer le nom du fichier image associé
    $stmt = $pdo->prepare("SELECT img FROM couleur_tissu WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $image = $stmt->fetchColumn();

    $stmt = $pdo->prepare("DELETE FROM couleur_tissu WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Supprimer le fichier image du serveur
        if ($image) {
            $imagePath = '../uploads/couleur-tissu-tissu/' . $image;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $_SESSION['message'] = 'La couleur du tissu a été supprimée avec succès !';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Couleur du tissu introuvable.';
        $_SESSION['message_type'] = 'error';
    }
} catch (Exception $e) {
    $_SESSION['message'] = 'Erreur lors de la suppression de la couleur du tissu en tissu : ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
}

header("Location: visualiser.php");
exit();
