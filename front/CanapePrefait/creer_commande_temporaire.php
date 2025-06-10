<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../formulaire/Connexion.php");
    exit;
}

// Vérifier que l'ID de la commande pré-faite est bien reçu
if (!isset($_POST['id_commande_prefait']) || !is_numeric($_POST['id_commande_prefait'])) {
    die("ID invalide.");
}

$id_prefait = intval($_POST['id_commande_prefait']);
$id_client = $_SESSION['user_id'];

// Récupérer les longueurs et le prix des dimensions depuis le formulaire
$longueurA = isset($_POST['longueurA']) ? floatval($_POST['longueurA']) : 0;
$longueurB = isset($_POST['longueurB']) ? floatval($_POST['longueurB']) : 0;
$longueurC = isset($_POST['longueurC']) ? floatval($_POST['longueurC']) : 0;
$prixDimensions = isset($_POST['prix_dimensions']) ? floatval($_POST['prix_dimensions']) : 0;

// Récupérer les données du canapé préfait
$stmt = $pdo->prepare("SELECT * FROM commande_prefait WHERE id = ?");
$stmt->execute([$id_prefait]);
$prefait = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prefait) {
    die("Commande pré-faite introuvable.");
}

// Vérifier si une commande temporaire existe déjà pour cet utilisateur et cette commande pré-faite
$stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ? AND id_commande_prefait = ?");
$stmt->execute([$id_client, $id_prefait]);
$commandeExistante = $stmt->fetch(PDO::FETCH_ASSOC);

if ($commandeExistante) {
    // Mettre à jour la commande temporaire existante
    $stmt = $pdo->prepare("UPDATE commande_temporaire SET 
        longueurA = ?, longueurB = ?, longueurC = ?, prix_dimensions = ?
        WHERE id = ?");
    $stmt->execute([
        $longueurA,
        $longueurB,
        $longueurC,
        $prixDimensions,
        $commandeExistante['id']
    ]);
} else {
    // Insérer une nouvelle commande temporaire si aucune existante
    $stmt = $pdo->prepare("INSERT INTO commande_temporaire (
        commentaire, date, statut, id_client, id_commande_prefait, id_structure, id_banquette, id_mousse, 
        id_mousse_tissu, id_couleur_bois, id_decoration, id_accoudoir_bois, id_dossier_bois, 
        id_couleur_tissu_bois, id_motif_bois, id_modele, id_couleur_tissu, id_motif_tissu, 
        id_dossier_tissu, id_accoudoir_tissu, id_nb_accoudoir, longueurA, longueurB, 
        longueurC, prix_dimensions
    ) VALUES (
        ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )");

    $stmt->execute([
        '', 'en cours', $id_client, $id_prefait, $prefait['id_structure'], $prefait['id_banquette'], 
        $prefait['id_mousse'], $prefait['id_mousse_tissu'], $prefait['id_couleur_bois'], 
        $prefait['id_decoration'], $prefait['id_accoudoir_bois'], $prefait['id_dossier_bois'], 
        $prefait['id_couleur_tissu_bois'], $prefait['id_motif_bois'], $prefait['id_modele'], 
        $prefait['id_couleur_tissu'], $prefait['id_motif_tissu'], $prefait['id_dossier_tissu'], 
        $prefait['id_accoudoir_tissu'], $prefait['id_nb_accoudoir'], $longueurA, 
        $longueurB, $longueurC, $prixDimensions
    ]);
}

// Rediriger vers l'étape suivante
header("Location: choix-mousse.php?id=$id_prefait");
exit;
?>