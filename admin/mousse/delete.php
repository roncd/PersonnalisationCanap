<?php
require '../config.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['message'] = 'ID de la mousse manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: index.php");
    exit();
}

// Supprimer la mousse de la base de données
try {
    $stmt = $pdo->prepare("DELETE FROM mousse WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = 'La mousse a été supprimée avec succès !';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'mousse introuvable.';
        $_SESSION['message_type'] = 'error';
    }
} catch (Exception $e) {
    $_SESSION['message'] = 'Erreur lors de la suppression de la mousse : ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
}

header("Location: index.php");
exit();
