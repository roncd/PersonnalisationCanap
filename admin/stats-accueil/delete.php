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
    $_SESSION['message'] = 'ID de la statistique manquante.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}

try {
    // Supprimer la question depuis la table FAQ
    $stmt = $pdo->prepare("DELETE FROM stats_accueil WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = 'La statistique a été supprimée avec succès.';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Statistique introuvable.';
        $_SESSION['message_type'] = 'error';
    }
} catch (Exception $e) {
    $_SESSION['message'] = 'Erreur lors de la suppression : ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
}

header("Location: visualiser-site.php");
exit();