<?php
require('fpdf.php');
require('../../admin/config.php');

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="devis.pdf"');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $idCommande = isset($input['id']) ? (int) $input['id'] : 0;
} else {
    http_response_code(400);
    die("Requête invalide.");
}


error_log("Méthode HTTP : " . $_SERVER['REQUEST_METHOD']);
error_log("ID récupéré : " . $idCommande);
error_log("Début génération PDF...");


// Récupération des informations du client
$query_client = $pdo->prepare("SELECT nom, prenom, adresse, codepostal, ville, mail, tel FROM client WHERE id IN (SELECT id_client FROM commande_detail WHERE id = ?)");
$query_client->execute([$idCommande]);
$client = $query_client->fetch(PDO::FETCH_ASSOC);

// Récupération des détails de la commande
$query_details = $pdo->prepare("SELECT * FROM commande_detail WHERE id = ?");
$query_details->execute([$idCommande]);
$details = $query_details->fetchAll(PDO::FETCH_ASSOC);

// Tables sans prix
$tablesNoPrix = ['structure', 'type_banquette'];

// Fonction pour récupérer les données des tables sans prix
function fetchNoPrixData($pdo, $table)
{
    $stmt = $pdo->prepare("SELECT id, nom FROM $table");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupérer les données des tables sans prix
$assocData = []; // Initialisation pour stocker toutes les données
foreach ($tablesNoPrix as $tableNoPrix) {
    $data = fetchNoPrixData($pdo, $tableNoPrix);
    foreach ($data as $item) {
        $assocData[$tableNoPrix][$item['id']] = [
            'nom' => $item['nom']
        ];
    }
}

// Tables avec prix
$tables = ['mousse', 'accoudoir_tissu', 'dossier_tissu', 'couleur_tissu', 'motif_tissu', 'modele'];

// Fonction pour récupérer les données des tables avec prix
function fetchData($pdo, $table)
{
    $stmt = $pdo->prepare("SELECT id, nom, prix FROM $table");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupérer les données des tables avec prix
foreach ($tables as $table) {
    $data = fetchData($pdo, $table);
    foreach ($data as $item) {
        $assocData[$table][$item['id']] = [
            'nom' => $item['nom'],
            'prix' => $item['prix']
        ];
    }
}

// Récupération des longueurs et du nombre d'accoudoirs
$stmt = $pdo->prepare("SELECT longueurA, longueurB, longueurC, id_nb_accoudoir, commentaire, date FROM commande_detail WHERE id = ?");
$stmt->execute([$idCommande]);
$commande = $stmt->fetch(PDO::FETCH_ASSOC);


$query_structure = $pdo->prepare("SELECT img, nom FROM structure WHERE id = (SELECT id_structure FROM commande_detail WHERE id = ?)");
$query_structure->execute([$idCommande]);
$structure = $query_structure->fetch(PDO::FETCH_ASSOC);

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
$date = htmlspecialchars($commande['date'] ?? '-');
$pdf->Cell(40, 10, $date, 0, 1);
$pdf->Ln();

// Table des produits commandés
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(60, 10, "Produit", 1);
$pdf->Cell(60, 10, mb_convert_encoding("Référence", "ISO-8859-1", "UTF-8"), 1);
$pdf->Cell(20, 10, mb_convert_encoding("Quantité", "ISO-8859-1", "UTF-8"), 1);
$pdf->Cell(50, 10, "Prix", 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
// Tableau de correspondance pour les colonnes spéciales
$tableColumn = [
    'type_banquette' => 'id_banquette',
];

// Parcours des détails
foreach ($details as $detail) {
    foreach (array_merge($tables, $tablesNoPrix) as $table) {
        // Utilisation de la correspondance ou de l'ID par défaut
        $columnName = $tableColumn[$table] ?? 'id_' . $table;
        $id_element = $detail[$columnName] ?? null;

        if ($id_element && isset($assocData[$table][$id_element])) {
            $element = $assocData[$table][$id_element];
            $prix = isset($element['prix']) ? number_format($element['prix'], 2, ',', ' ') . " EUR" : "-";

            // Ajout des données au PDF
            $pdf->Cell(60, 10, mb_convert_encoding($table, "ISO-8859-1", "UTF-8"), 1);
            $pdf->Cell(60, 10, mb_convert_encoding($element['nom'], "ISO-8859-1", "UTF-8"), 1);
            if ($table === "accoudoir_tissu") {
                $quantite_accoudoir = htmlspecialchars($commande['id_nb_accoudoir'] ?? '-');
                $pdf->Cell(20, 10, $quantite_accoudoir, 1); // Quantité pour accoudoir_tissu
            } else {
                $pdf->Cell(20, 10, "-", 1);
            }
            $pdf->Cell(50, 10, $prix, 1);
            $pdf->Ln();
        }
    }

    // Vérification si les données existent avant d'afficher
    if (!empty($commande)) {
        $longueurA = htmlspecialchars($commande['longueurA'] ?? '-');
        $longueurB = isset($commande['longueurB']) && !empty(trim($commande['longueurB'])) ? htmlspecialchars($commande['longueurB']) : null;
        $longueurC = isset($commande['longueurC']) && !empty(trim($commande['longueurC'])) ? htmlspecialchars($commande['longueurC']) : null;
        $prix_dimensions = number_format($detail['prix_dimensions'] , 2, ',', ' ') . " EUR";
        $commentaire = isset($commande['commentaire']) && !empty(trim($commande['commentaire'])) ? htmlspecialchars($commande['commentaire']) : null;
        
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(65, 10, mb_convert_encoding("Dimensions du canapé :", "ISO-8859-1", "UTF-8"), 0, 0);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Ln(10);
        $pdf->Cell(45, 10, "Longueur A (en cm)", 1, 0);
        $pdf->Cell(15, 10, $longueurA, 1, 0, 'R');
        $pdf->Cell(40);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(60, 10, "Prix total des dimensions :", 1, 0);
        $pdf->Cell(30, 10, $prix_dimensions, 1, 0, 'R');
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 12);
        if ($longueurB !== null) {
            $pdf->Cell(45, 10, "Longueur B (en cm)",  1, 0);
            $pdf->Cell(15, 10, $longueurB, 1, 0, 'R');
            $pdf->Ln();
        }
        if ($longueurC !== null) {
            $pdf->Cell(45, 10, "Longueur C (en cm)",  1, 0);
            $pdf->Cell(15, 10, $longueurC, 1, 0, 'R');
            $pdf->Ln();
        }

        if ($commentaire !== null) {
            $pdf->SetFont('Arial', '', 10);
            $pdf->Ln(10);
            $pdf->Cell(40, 10, "Commentaire du client :");
            $pdf->Cell(30, 10, $commentaire);
        }
    } else {
        $pdf->Ln(10);
        $pdf->Cell(90, 10, mb_convert_encoding("Données de commande introuvables", "ISO-8859-1", "UTF-8"), 1);
        $pdf->Ln();
    }
}

if ($detail && isset($detail['prix'])) {
    $prixTotal = number_format($detail['prix'], 2, ',', ' ') . " EUR";

    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetX($pdf->GetPageWidth() - 80);
    $pdf->Cell(30, 10, "Prix Total :", 1, 0);
    $pdf->Cell(40, 10, $prixTotal, 1, 1, 'R');
} else {
    $pdf->Ln(10);
    $pdf->SetX($pdf->GetPageWidth() - 100);
    $pdf->Cell(90, 10, mb_convert_encoding("Aucun prix total trouvé", "ISO-8859-1", "UTF-8"), 1, 1, 'R');
}

$pdf->Ln(10);

$imageName = $structure['img'] ?? null;
$name = $structure['nom'] ?? null;
$imagePath = $imageName ? "../../admin/uploads/structure/" . $imageName : null;



if ($imagePath && file_exists($imagePath)) {
    $pdf->AddPage();
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 13);
    $pdf->Cell(65, 10, mb_convert_encoding("NUMÉRO DE COMMANDE :", "ISO-8859-1", "UTF-8"), 0, 0);
    $pdf->Cell(110, 10, $idCommande, 0, 1);
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, mb_convert_encoding("Structure associée à la commande : " . $name . "", "ISO-8859-1", "UTF-8"), 0, 1, 'C');
    $pdf->Image($imagePath, 10, 70, 190);
} else {
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(80, 10, mb_convert_encoding("Aucune image de strucutre trouvé", "ISO-8859-1", "UTF-8"), 1, 1);
    $pdf->Ln(10);
}
$pdf->Output();
