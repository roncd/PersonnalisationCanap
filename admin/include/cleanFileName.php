<?php
// function cleanFileName($fileName)
// {
//     // Supprimer les accents
//     $fileName = iconv('UTF-8', 'ASCII//TRANSLIT', $fileName);
//     // Remplacer les caractères non autorisés par des underscores
//     $fileName = preg_replace('/[^A-Za-z0-9.\-_]/', '_', $fileName);
//     return $fileName;
// }

function cleanFileName($filename)
{
    // Supprimer le chemin éventuel
    $filename = basename($filename);

    // Si l'extension intl est disponible, on utilise Normalizer (plus fiable que iconv)
    if (class_exists('Normalizer')) {
        $filename = Normalizer::normalize($filename, Normalizer::FORM_D);
        $filename = preg_replace('/\p{Mn}/u', '', $filename); // supprime les accents
    } else {
        // Sinon fallback avec iconv
        $filename = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $filename);
    }

    // Nettoyage des caractères spéciaux
    $filename = preg_replace('/[^a-zA-Z0-9\.\-_]/', '-', $filename);

    // Minuscule
    $filename = strtolower($filename);

    return $filename;
}
