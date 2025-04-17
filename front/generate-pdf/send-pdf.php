<?php
require('../../admin/config.php');
require_once 'getTypeCommande.php'; 
require_once '../../vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

//JSON
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID manquant.']);
    exit;
}

$idCommande = intval($data['id']);

// Récupération des infos client
$query = $pdo->prepare("SELECT mail, nom, prenom FROM client WHERE id IN (SELECT id_client FROM commande_detail WHERE id = ?)");
$query->execute([$idCommande]);
$client = $query->fetch(PDO::FETCH_ASSOC);

if (!$client || empty($client['mail'])) {
    echo json_encode(['success' => false, 'message' => 'Email client introuvable']);
    exit;
}

$emailClient = $client['mail'];

//Récupération du type
$type = getTypeCommande($idCommande); 
$pdfContent = '';
$nomFichier = "devis-$idCommande.pdf";

// Génération du PDF
if ($type === 'Bois') {
    ob_start(); 
    require 'generer-devis-bois-mail.php';
    if (!isset($pdf)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Erreur : variable $pdf non définie']);
        exit;
    }
    $pdfContent = $pdf->Output('', 'S');
    ob_end_clean();

} elseif ($type === 'Tissu') {
    ob_start();
    require 'generer-devis-tissu-mail.php';
    $pdfContent = $pdf->Output('', 'S');
    echo json_encode(['success' => false, 'message' => 'Erreur : variable $pdf non définie']);
    ob_end_clean();
} else {
    echo json_encode(['success' => false, 'message' => 'Type de commande inconnu']);
    exit;
}

// Envoi du mail avec le PDF en PJ
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';        
    $mail->SMTPAuth   = true;
    $mail->Username   = 'decodumonde.alternance@gmail.com';    
    $mail->Password   = 'dlop yctn sqlu xywd';      
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('decodumonde.alternance@gmail.com', mb_convert_encoding('Déco du Monde', "ISO-8859-1", "UTF-8"));    
    $mail->addAddress($client['mail'], $client['prenom'] . ' ' . $client['nom']);

    $mail->Subject = mb_convert_encoding('Devis n°' . $idCommande, "ISO-8859-1", "UTF-8");    
    $mail->Body    = "Bonjour " . $client['prenom'] . ",\n\nMerci d'avoir créé une commande sur notre site de personnalisation de canapé marocain. Veuillez trouver ci-joint votre devis.\n\nUne fois votre devis généré vous devez vous rendre au magasin Déco du Monde pour effectué un accompte, ensuite nous commenceron la construction du canapé.\n\nBien cordialement,\nL'équipe " . $entreprise['nom'];
    $mail->addStringAttachment($pdfContent, "devis-$idCommande.pdf");
    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Le mail a bien été envoyé.']);
} catch (Exception $e) {
    error_log("Erreur lors de l'envoi du mail : " . $mail->ErrorInfo);
    echo json_encode(['success' => false, 'message' => "Erreur SMTP : " . $mail->ErrorInfo]);
}