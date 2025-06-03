<?php
require '../../admin/config.php';
session_start();

// Vérifie si utilisateur(trice) connecté(é)
if (!isset($_SESSION['user_id'])) {
  $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
  header("Location: ../formulaire/Connexion.php");
  exit;
}

// Récupère l’ID du canapé préfait
if (!isset($_POST['id_commande_prefait']) || !is_numeric($_POST['id_commande_prefait'])) {
  die("ID invalide.");
}

$id_prefait = intval($_POST['id_commande_prefait']);
$id_client = $_SESSION['user_id'];

// Récupère les longueurs et le prix des dimensions depuis le formulaire
$longueurA = isset($_POST['longueurA']) ? floatval($_POST['longueurA']) : 0;
$longueurB = isset($_POST['longueurB']) ? floatval($_POST['longueurB']) : 0;
$longueurC = isset($_POST['longueurC']) ? floatval($_POST['longueurC']) : 0;
$prixDimensions = isset($_POST['prix_dimensions']) ? floatval($_POST['prix_dimensions']) : 0;

// Récupère les données du canapé préfait
$stmt = $pdo->prepare("SELECT * FROM commande_prefait WHERE id = ?");
$stmt->execute([$id_prefait]);
$prefait = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prefait) {
  die("Commande pré-faite introuvable.");
}

// Insère dans commande_temporaire
$stmt = $pdo->prepare("INSERT INTO commande_temporaire (
  commentaire, date, statut, id_client,
  id_structure, id_banquette, id_mousse, id_mousse_tissu,
  id_couleur_bois, id_decoration, id_accoudoir_bois, id_dossier_bois,
  id_couleur_tissu_bois, id_motif_bois,
  id_modele, id_couleur_tissu, id_motif_tissu,
  id_dossier_tissu, id_accoudoir_tissu, id_nb_accoudoir,
  longueurA, longueurB, longueurC, prix_dimensions
) VALUES (
  ?, NOW(), ?, ?,
  ?, ?, ?, ?,
  ?, ?, ?, ?,
  ?, ?,
  ?, ?, ?,
  ?, ?, ?,
  ?, ?, ?, ?
)");

$stmt->execute([
  '', // commentaire
  'en cours', // statut
  $id_client,
  $prefait['id_structure'],
  $prefait['id_banquette'],
  $prefait['id_mousse'],
  $prefait['id_mousse_tissu'],
  $prefait['id_couleur_bois'],
  $prefait['id_decoration'],
  $prefait['id_accoudoir_bois'],
  $prefait['id_dossier_bois'],
  $prefait['id_couleur_tissu_bois'],
  $prefait['id_motif_bois'],
  $prefait['id_modele'],
  $prefait['id_couleur_tissu'],
  $prefait['id_motif_tissu'],
  $prefait['id_dossier_tissu'],
  $prefait['id_accoudoir_tissu'],
  $prefait['id_nb_accoudoir'],
  $longueurA,
  $longueurB,
  $longueurC,
  $prixDimensions
]);

// Récupère l’ID de la commande temporaire insérée
$id_commande_temp = $pdo->lastInsertId();

// Redirige vers choix des dimensions (ou prochaine étape)
header("Location: choix-mousse.php?id=$id_prefait");
exit;
?>
