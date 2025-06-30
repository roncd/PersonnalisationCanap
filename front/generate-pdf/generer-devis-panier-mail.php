<?php
require('fpdf.php');
require('../../admin/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $idCommande = isset($input['id']) ? (int) $input['id'] : 0;
} else {
    http_response_code(400);
    die("Requête invalide.");
}


// Récupération des informations du client
$query_client = $pdo->prepare("SELECT nom, prenom, adresse, codepostal, ville, mail, tel FROM client WHERE id IN (SELECT id_client FROM panier_final WHERE id = ?)");
$query_client->execute([$idCommande]);
$client = $query_client->fetch(PDO::FETCH_ASSOC);

// Récupération des détails de la commande
$query_paniers = $pdo->prepare("SELECT * FROM panier_final WHERE id = ?");
$query_paniers->execute([$idCommande]);
$paniers = $query_paniers->fetchAll(PDO::FETCH_ASSOC);

if (!$paniers) {
    http_response_code(404);
    error_log("Erreur : Commande introuvable pour ID $idCommande.");
    die("Commande introuvable.");
}

$stmt = $pdo->prepare("SELECT date FROM panier_final WHERE id = ?");
$stmt->execute([$idCommande]);
$date_panier = $stmt->fetch(PDO::FETCH_ASSOC);

$stmtProduits = $pdo->prepare("SELECT pdf.id_produit, pdf.quantite, vp.nom, vp.prix
FROM panier_detail_final pdf
JOIN vente_produit vp ON pdf.id_produit = vp.id
WHERE pdf.id_panier_final = ?");
$stmtProduits->execute([$idCommande]);
$produits = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);


// Infos de l'entreprise
$entreprise = [
    "nom" => "DECO DU MONDE",
    "adresse" => "76/78 Avenue Lenine",
    "codepostal" => "93380, Pierrefitte-Sur-Seine",
    "email" => "decorient@gmail.com",
    "telephone" => "0148229805"
];

// Création du PDF
class PDF extends FPDF
{
    function Header()
    {
        $this->Image('../../medias/logo_trasparent-decodumonde.png', 10, 6, 40);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(30, 10, 'DEVIS', 1, 0, 'C');
        $this->Ln(20);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Largeur des colonnes
$colWidth = 90;
$lineHeight = 5;

// Informations Client / Entreprise
$pdf->Cell($colWidth, $lineHeight, mb_convert_encoding($client['prenom'] . " " . $client['nom'], "ISO-8859-1", "UTF-8"), 0, 0, 'L');
$pdf->Cell(10);
$pdf->Cell($colWidth, $lineHeight, $entreprise['nom'], 0, 1, 'R');

$pdf->Cell($colWidth, $lineHeight, mb_convert_encoding($client['adresse'], "ISO-8859-1", "UTF-8"), 0, 0, 'L');
$pdf->Cell(10);
$pdf->Cell($colWidth, $lineHeight, $entreprise['adresse'], 0, 1, 'R');

$pdf->Cell($colWidth, $lineHeight, $client['codepostal'] . ", " . $client['ville'], 0, 0, 'L');
$pdf->Cell(10);
$pdf->Cell($colWidth, $lineHeight, $entreprise['codepostal'], 0, 1, 'R');

$pdf->Cell($colWidth, $lineHeight, $client['mail'], 0, 0, 'L');
$pdf->Cell(10);
$pdf->Cell($colWidth, $lineHeight, $entreprise['email'], 0, 1, 'R');

$pdf->Cell($colWidth, $lineHeight, $client['tel'], 0, 0, 'L');
$pdf->Cell(10);
$pdf->Cell($colWidth, $lineHeight, $entreprise['telephone'], 0, 1, 'R');

$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 13);
$pdf->Cell(65, 10, mb_convert_encoding("NUMÉRO DE COMMANDE :", "ISO-8859-1", "UTF-8"), 0, 0);
$pdf->Cell(110, 10, $idCommande, 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(12, 10, "Date :", 0, 0);
$date = htmlspecialchars($date_panier['date'] ?? '-');
$pdf->Cell(40, 10, $date, 0, 1);
$pdf->Ln();

// Table des produits commandés
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(60, 10, mb_convert_encoding("Produit", "ISO-8859-1", "UTF-8"), 1);
$pdf->Cell(60, 10, mb_convert_encoding("Référence", "ISO-8859-1", "UTF-8"), 1);
$pdf->Cell(20, 10, mb_convert_encoding("Quantité", "ISO-8859-1", "UTF-8"), 1);
$pdf->Cell(50, 10, "Prix", 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);

if (!empty($produits)) {
    // Boucle sur chaque produit 
    foreach ($produits as $produit) {
        $nom = mb_convert_encoding($produit['nom'], "ISO-8859-1", "UTF-8"); // Récupère le nom de l'produit bois
        $quantite = htmlspecialchars($produit['quantite']); // Quantité du produit 
        $prix = isset($produit['prix']) ? number_format($produit['prix'], 2, ',', ' ') . " EUR" : "-";

        // Affiche les colonnes
        $pdf->Cell(60, 10, mb_convert_encoding( "Produit à l'unité", "ISO-8859-1", "UTF-8"), 1);
        $pdf->Cell(60, 10, $nom, 1);
        $pdf->Cell(20, 10, $quantite, 1);
        $pdf->Cell(50, 10, $prix, 1);
        $pdf->Ln();
    }
} else {
    // Message si aucun produit trouvé
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(190, 10, mb_convert_encoding("Aucun produit pour cette commande", "ISO-8859-1", "UTF-8"), 1, 1, 'C');
}

foreach ($paniers as $panier) {
    if ($panier && isset($panier['prix'])) {
        $prixTotal = number_format($panier['prix'], 2, ',', ' ') . " EUR";

        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetX($pdf->GetPageWidth() - 80);
        $pdf->Cell(30, 10, "Prix Total :", 1, 0);
        $pdf->Cell(40, 10, $prixTotal, 1, 1, 'R');
    } else {
        $pdf->Ln(10);
        $pdf->SetX($pdf->GetPageWidth() - 100);
        $pdf->Cell(80, 10, mb_convert_encoding("Aucun prix total trouvé", "ISO-8859-1", "UTF-8"), 1, 1);
    }
}
