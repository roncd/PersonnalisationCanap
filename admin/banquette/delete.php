<?php
require '../config.php';
session_start();


if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['message'] = 'ID de la banquette manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}

try {
    // Récupérer le nom du fichier image associé
    $stmt = $pdo->prepare("SELECT img FROM type_banquette WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $image = $stmt->fetchColumn();

    $stmt = $pdo->prepare("DELETE FROM type_banquette WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Supprimer le fichier image du serveur
        if ($image) {
            $imagePath = '../uploads/banquette/' . $image;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $_SESSION['message'] = 'La banquette a été supprimée avec succès !';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Banquette introuvable.';
        $_SESSION['message_type'] = 'error';
    }
} catch (Exception $e) {
    $_SESSION['message'] = 'Erreur lors de la suppression de la banquette : ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
}

header("Location: visualiser.php");
exit();
