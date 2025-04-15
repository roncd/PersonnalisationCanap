<?php
require '../config.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['message'] = 'ID de la décoration manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: index.php");
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM decoration WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = 'La décoration a été supprimée avec succès !';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Décoration du tissu en tissu introuvable.';
        $_SESSION['message_type'] = 'error';
    }
} catch (Exception $e) {
    $_SESSION['message'] = 'Erreur lors de la suppression de la décoration : ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
}

header("Location: index.php");
exit();
