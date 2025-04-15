<?php
require '../config.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['message'] = 'ID du dossier bois manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: index.php");
    exit();
}

// Supprimer le dossier bois de la base de données
try {
    $stmt = $pdo->prepare("DELETE FROM dossier_bois WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = 'Le dossier bois a été supprimée avec succès !';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'dossier bois introuvable.';
        $_SESSION['message_type'] = 'error';
    }
} catch (Exception $e) {
    $_SESSION['message'] = 'Erreur lors de la suppression du dossier bois : ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
}

header("Location: index.php");
exit();
