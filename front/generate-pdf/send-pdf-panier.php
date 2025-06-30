<?php
require('../../admin/config.php');
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
$query = $pdo->prepare("SELECT mail, nom, prenom FROM client WHERE id IN (SELECT id_client FROM panier_final WHERE id = ?)");
$query->execute([$idCommande]);
$client = $query->fetch(PDO::FETCH_ASSOC);

$query = $pdo->prepare("SELECT date FROM panier_final WHERE id = ?");
$query->execute([$idCommande]);
$commande = $query->fetch(PDO::FETCH_ASSOC);

if (!$client || empty($client['mail'])) {
    echo json_encode(['success' => false, 'message' => 'Email client introuvable']);
    exit;
}

$emailClient = $client['mail'];
$pdfContent = '';
$nomFichier = "devis-$idCommande.pdf";


    ob_start(); 
    require 'generer-devis-panier-mail.php';
    if (!isset($pdf)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Erreur : variable $pdf non définie']);
        exit;
    }
    $pdfContent = $pdf->Output('', 'S');
    ob_end_clean();



// Envoi du mail avec le PDF en PJ

//Envoie au client
$mailClient = new PHPMailer(true);

try {
    $mailClient->isSMTP();
    $env = parse_ini_file(__DIR__ . '/../../.env');
    $mailClient->Host       = $env['SMTP_HOST'];
    $mailClient->SMTPAuth   = true;
    $mailClient->Username   = $env['SMTP_USER'];
    $mailClient->Password   = $env['SMTP_PASS'];    
    $mailClient->SMTPSecure = 'tls';
    $mailClient->Port       = 587;

    $mailClient->setFrom($env['SMTP_USER'], mb_convert_encoding('Déco du Monde', "ISO-8859-1", "UTF-8"));    
    $mailClient->addAddress($client['mail'], $client['prenom'] . ' ' . $client['nom']);

    $mailClient->Subject = mb_convert_encoding('Devis n°' . $idCommande, "ISO-8859-1", "UTF-8");    
    $mailClient->isHTML(true);
    $mailClient->Body = "
    <p>Bonjour " . htmlspecialchars($client['prenom']) . ",</p>
    
    <p>Merci d'avoir réservé un panier sur notre site de personnalisation de canapé marocain.</p>
    
    <p>Veuillez trouver ci-joint le devis correspondant à votre commande.</p>
    
    <h3>Étapes à suivre pour finaliser votre commande :</h3>
    <ol>
        <li>Rendez-vous en magasin (<strong>Déco du Monde</strong>) afin de valider le devis.</li>
        <li>Un acompte devra être réglé sur place pour confirmer la commande.</li>
    </ol>
    
    <p><strong>Moyens de paiement acceptés :</strong></p>
    <ul>
        <li>Espèces</li>
        <li>Carte bancaire</li>
        <li>Virement bancaire</li>
        <li>Paiement en plusieurs fois via <strong>Alma</strong> (2x, 3x ou 4x)</li>
    </ul>
    
    <p><strong>⚠️ La commande ne sera confirmée qu’après le paiement de l’acompte.</strong></p>
    
    <p>Nous restons à votre disposition pour toute question.</p>
    
    <p>Bien cordialement,<br>
    L’équipe " . htmlspecialchars($entreprise['nom']) . "</p>";
    $mailClient->addStringAttachment($pdfContent, "devis-$idCommande.pdf");
    $mailClient->send();
    echo json_encode(['success' => true, 'message' => 'Le mail au client a bien été envoyé.']);
} catch (Exception $e) {
    error_log("Erreur lors de l'envoi du mail au client : " . $mailClient->ErrorInfo);
    echo json_encode(['success' => false, 'message' => "Erreur SMTP : " . $mailClient->ErrorInfo]);
}

//Envoie à l'entreprise
$mailEntreprise = new PHPMailer(true);

try {
    $mailEntreprise->isSMTP();
    $env = parse_ini_file(__DIR__ . '/../../.env');
    $mailEntreprise->Host       = $env['SMTP_HOST'];
    $mailEntreprise->SMTPAuth   = true;
    $mailEntreprise->Username   = $env['SMTP_USER'];
    $mailEntreprise->Password   = $env['SMTP_PASS']; 
    $mailEntreprise->SMTPSecure = 'tls';
    $mailEntreprise->Port       = 587;

    $mailEntreprise->setFrom($env['SMTP_USER'], mb_convert_encoding('Déco du Monde', "ISO-8859-1", "UTF-8"));
    $mailEntreprise->addAddress($env['SMTP_USER'], mb_convert_encoding('Déco du Monde', "ISO-8859-1", "UTF-8"));

    $mailEntreprise->Subject = mb_convert_encoding('Nouvelle commande reçue - Devis n°' . $idCommande, "ISO-8859-1", "UTF-8");    
    $mailEntreprise->Body    = "Une nouvelle commande a été passée par " . $client['prenom'] . " " . $client['nom'] . ". Veuillez trouver ci-joint le devis correspondant.\n\nNuméro de commande : " . $idCommande . "\nDate de commande : " . $commande['date'] . "\nEmail client : " . $client['mail'] . "\n\nDéco du Monde";
    $mailEntreprise->addStringAttachment($pdfContent, "devis-$idCommande.pdf");

    $mailEntreprise->send();
    echo json_encode(['success' => true, 'message' => 'Le mail à l’entreprise a bien été envoyé.']);
} catch (Exception $e) {
    error_log("Erreur lors de l'envoi du mail à l’entreprise : " . $mailEntreprise->ErrorInfo);
    echo json_encode(['success' => false, 'message' => "Erreur SMTP : " . $mailEntreprise->ErrorInfo]);
}

