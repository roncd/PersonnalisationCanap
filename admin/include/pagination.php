<?php
if (!$search) {
    echo '<nav class="nav" aria-label="pagination">';
    echo '<ul class="pagination">';

    if ($page > 1) {
        echo '<li><a href="?page=' . ($page - 1) . '">Précédent</a></li>';
    }

    // Nombre maximal de liens à afficher
    $max_links = 3;
    //Premier lien pagination 
    $start = max(1, $page - floor($max_links / 2));
    // Dernier lien pagination
    $end = min($totalPages, $start + $max_links - 1);

    if ($end - $start + 1 < $max_links) {
        $start = max(1, $end - $max_links + 1);
    }

    // Affichage de première page et de ... entre max_link et première page
    if ($start > 1) {
        echo '<li><a href="?page=1">1</a></li>';
        if ($start > 2) {
            echo '<li><span>…</span></li>';
        }
    }

    // Page active
    for ($i = $start; $i <= $end; $i++) {
        echo '<li>';
        echo '<a class="' . ($i == $page ? 'active' : '') . '" href="?page=' . $i . '">' . $i . '</a>';
        echo '</li>';
    }

    // Affichage de dernière page et de ... entre max_link et dernière page
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            echo '<li><span>…</span></li>';
        }
        echo '<li><a href="?page=' . $totalPages . '">' . $totalPages . '</a></li>';
    }

    if ($page < $totalPages) {
        echo '<li><a href="?page=' . ($page + 1) . '">Suivant</a></li>';
    }

    echo '</ul>';
    echo '</nav>';
}
