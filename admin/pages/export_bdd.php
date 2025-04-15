<?php
require '../config.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

// Nom du fichier d'export
$file_name = "backup_" . date("Y-m-d_H:i:s") . ".sql";

// Exécuter la commande mysqldump
$command = "mysqldump --host=$host --user=$user --password=$pass $dbname > $file_name";
system($command);

// Vérifier si le fichier a bien été créé
if (file_exists($file_name)) {
    // Définir les headers pour le téléchargement
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_name));

    // Envoyer le fichier au navigateur
    readfile($file_name);

    // Supprimer le fichier après téléchargement
    unlink($file_name);
    exit;
} else {
    echo "Erreur lors de l'exportation de la base de données.";
}
