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
    $_SESSION['message'] = 'ID du dossier manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}

// Supprimer le dossier tissu de la base de données
try {
    // Récupérer le nom du fichier image associé
    $stmt = $pdo->prepare("SELECT img FROM dossier_tissu WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $image = $stmt->fetchColumn();

    $stmt = $pdo->prepare("DELETE FROM dossier_tissu WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Supprimer le fichier image du serveur
        if ($image) {
            $imagePath = '../uploads/dossier-tissu/' . $image;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $_SESSION['message'] = 'Le dossier a été supprimé avec succès !';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Dossier introuvable.';
        $_SESSION['message_type'] = 'error';
    }
} catch (Exception $e) {
    $_SESSION['message'] = 'Erreur lors de la suppression du dossier : ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
}

header("Location: visualiser.php");
exit();
