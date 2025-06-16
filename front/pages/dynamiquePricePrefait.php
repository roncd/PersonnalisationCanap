<?php
function calculPrix($commande, $composition = []) {
    $totalPrice = 0;
    $prixParCm = 3.5;

    if (!empty($commande['longueurA'])) $totalPrice += (float)$commande['longueurA'] * $prixParCm;
    if (!empty($commande['longueurB'])) $totalPrice += (float)$commande['longueurB'] * $prixParCm;
    if (!empty($commande['longueurC'])) $totalPrice += (float)$commande['longueurC'] * $prixParCm;

    if (!empty($composition)) {
        foreach ($composition as $nomTable => $details) {
            if ($nomTable === 'accoudoirs_bois_multiples') {
                foreach ($details as $accoudoir) {
                    if (!empty($accoudoir['prix'])) {
                        $totalPrice += (float)$accoudoir['prix'];
                    }
                }
            } elseif ($nomTable === 'accoudoir_tissu') {
                if (!empty($details['prix'])) {
                    $totalPrice += (float)$details['prix'] * 2;
                }
            } else {
                if (!empty($details['prix'])) {
                    $totalPrice += (float)$details['prix'];
                }
            }
        }
    }

    return $totalPrice;
}
