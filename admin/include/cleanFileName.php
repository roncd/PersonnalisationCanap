<?php
function cleanFileName($fileName)
{
    // Supprimer les accents
    $fileName = iconv('UTF-8', 'ASCII//TRANSLIT', $fileName);
    // Remplacer les caractères non autorisés par des underscores
    $fileName = preg_replace('/[^A-Za-z0-9.\-_]/', '_', $fileName);
    return $fileName;
}
