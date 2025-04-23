<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../formulaire/Connexion.php");
    exit;
}

$id_client = $_SESSION['user_id'];

// Vérifier si une commande temporaire existe déjà pour cet utilisateur
$stmt = $pdo->prepare("SELECT * FROM commande_temporaire WHERE id_client = ?");
$stmt->execute([$id_client]);
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM client WHERE id = :id_client";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id_client', $commande['id_client'], PDO::PARAM_INT);
$stmt->execute();
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if ($client) {
  $assocMail['client'][$commande['id_client']] = $client;
} else {
  $assocMail['client'][$commande['id_client']] = ['mail' => '-'];
}

$tables = ['structure', 'type_banquette', 'mousse', 'accoudoir_tissu',
  'dossier_tissu', 'couleur_tissu', 'motif_tissu', 'modele'
];

function fetchData($pdo, $table) {
  $stmt = $pdo->prepare("SELECT * FROM $table");
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$data = [];
$assocData = [];

foreach ($tables as $table) {
  $data[$table] = fetchData($pdo, $table);
  // Convertir en tableau associatif clé=id, valeur=nom
  foreach ($data[$table] as $item) {
    $assocData[$table][$item['id']] = [
        'nom' => $item['nom'],
        'img' => $item['img'],
    ];
}
}
$id = $commande['id']; 

$stmt = $pdo->prepare("SELECT id, longueurA, longueurB, longueurC FROM commande_temporaire WHERE id = ?");
$stmt->execute([$id]); 
$data['commande_temporaire'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
$assocData['commande_temporaire'] = [];

foreach ($data['commande_temporaire'] as $dim) {
  $assocData['commande_temporaire'][$dim['id']] = [
    'longueurA' => $dim['longueurA'],
    'longueurB' => $dim['longueurB'],
    'longueurC' => $dim['longueurC']
  ];
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['comment'])) {
  // Vérifier si un commentaire a été saisi
  if (!empty($_POST["comment"])) {
      $commentaire = trim($_POST["comment"]); 

      if ($commande) {
          // Mettre à jour la commande temporaire avec le commentaire
          $stmt = $pdo->prepare("UPDATE commande_temporaire SET commentaire = ? WHERE id = ?");
          $stmt->execute([$commentaire, $id]);
          $message = '<p class="message success">Commentaire ajouté avec succès !</p>';
      } else {
        $message = '<p class="message error">Aucune commande trouvée pour cet utilisateur.</p>';
      }
  } else {
    $message = '<p class="message error">Le commentaire ne peut pas être vide.</p>';
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/processus.css">
  <link rel="stylesheet" href="../../styles/popup.css">
  <link rel="stylesheet" href="../../styles/message.css">
  <script type="module" src="../../scrpit/popup-tissu.js"></script>
  <script type="module" src="../../scrpit/button.js"></script>
  <title>Récapitulatif de la commande</title>
</head>
<body data-user-id="<?php echo $_SESSION['user_id']; ?>">

<header>
  <?php require '../../squelette/header.php'; ?>
</header>

<main>
  <div class="container">
    <!-- Colonne de gauche -->
    <div class="left-column">
      <h2>Récapitulatif de la commande</h2>
  
  <section class="color-options">      
  <h3>Étape 1 : Choisi ta structure</h3>
  <?php
  echo '<div class="option">
          <img src="../../admin/uploads/structure/'.htmlspecialchars($assocData['structure'][$commande['id_structure']]['img'] ?? '-').'" 
              alt="'.htmlspecialchars($assocData['structure'][$commande['id_structure']]['nom'] ?? '-').'">
          <p>'. htmlspecialchars($assocData['structure'][$commande['id_structure']]['nom'] ?? '-') . '</p>
        </div>';
  ?>
 
  <h3>Étape 1 : Choisi tes dimensions</h3>
  <?php
  $longueurB = isset($dim['longueurB']) && !empty(trim($dim['longueurB'])) ? htmlspecialchars($dim['longueurB']) : null;
  $longueurC = isset($dim['longueurC']) && !empty(trim($dim['longueurC'])) ? htmlspecialchars($dim['longueurC']) : null;

  echo '<div class="dimension-container">
          <p class="input-field">Longueur banquette A : ' . htmlspecialchars($dim['longueurA'] ?? '-') . ' cm</p>
        </div>';
  if ($longueurB !== null) {
      echo '<div class="dimension-container">
              <p class="input-field">Longueur banquette B : ' . $longueurB . ' cm</p>
            </div>';
  }
  if ($longueurC !== null) {
      echo '<div class="dimension-container">
              <p class="input-field">Longueur banquette C : ' . $longueurC . ' cm</p>
            </div>';
  }
  ?>

  <h3>Étape 2 : Choisi ton type de banquette</h3>
  <?php
  echo '<div class="option">
          <img src="../../admin/uploads/banquette/'.htmlspecialchars($assocData['type_banquette'][$commande['id_banquette']]['img'] ?? '-').'" 
              alt="'.htmlspecialchars($assocData['type_banquette'][$commande['id_banquette']]['nom'] ?? '-').'">
          <p>'. htmlspecialchars($assocData['type_banquette'][$commande['id_banquette']]['nom'] ?? '-') . '</p>
        
        </div>';
  ?>

  <h3>Étape 3 : Choisi ton modèle</h3>
  <?php
  echo '<div class="option">
          <img src="../../admin/uploads/modele/'.htmlspecialchars($assocData['modele'][$commande['id_modele']]['img'] ?? '-').'" 
              alt="'.htmlspecialchars($assocData['modele'][$commande['id_modele']]['nom'] ?? '-').'">
          <p>'. htmlspecialchars($assocData['modele'][$commande['id_modele']]['nom'] ?? '-') . '</p>
        </div>';
  ?>
  <h3>Étape 4 : Choisi ta couleur de tissu</h3>
  <?php
  echo '<div class="option">
          <img src="../../admin/uploads/couleur-tissu-tissu/'.htmlspecialchars($assocData['couleur_tissu'][$commande['id_couleur_tissu']]['img'] ?? '-').'" 
              alt="'.htmlspecialchars($assocData['couleur_tissu'][$commande['id_couleur_tissu']]['nom'] ?? '-').'">
          <p>'. htmlspecialchars($assocData['couleur_tissu'][$commande['id_couleur_tissu']]['nom'] ?? '-') . '</p>
        </div>';
  ?>

  <h3>Étape 4 : Choisi ton motif de coussin</h3>
  <?php
  echo '<div class="option">
          <img src="../../admin/uploads/motif-tissu/'.htmlspecialchars($assocData['motif_tissu'][$commande['id_motif_tissu']]['img'] ?? '-').'" 
              alt="'.htmlspecialchars($assocData['motif_tissu'][$commande['id_motif_tissu']]['nom'] ?? '-').'">
          <p>'. htmlspecialchars($assocData['motif_tissu'][$commande['id_motif_tissu']]['nom'] ?? '-') . '</p>
        </div>';
  ?>

  <h3>Étape 5 : Choisi ton dossier</h3>
   <?php
  echo '<div class="option">
          <img src="../../admin/uploads/dossier-tissu/'.htmlspecialchars($assocData['dossier_tissu'][$commande['id_dossier_tissu']]['img'] ?? '-').'" 
              alt="'.htmlspecialchars($assocData['dossier_tissu'][$commande['id_dossier_tissu']]['nom'] ?? '-').'">
          <p>'. htmlspecialchars($assocData['dossier_tissu'][$commande['id_dossier_tissu']]['nom'] ?? '-') . '</p>
        </div>';
  ?>
  
  <h3>Étape 6 : Choisi tes accoudoirs</h3>
<?php
// Récupération de la quantité depuis la colonne id_nb_accoudoir dans la commande
$quantite_accoudoir = htmlspecialchars($commande['id_nb_accoudoir'] ?? '-'); // Récupère la quantité si elle existe

echo '<div class="option">
        <img src="../../admin/uploads/accoudoirs-tissu/'.htmlspecialchars($assocData['accoudoir_tissu'][$commande['id_accoudoir_tissu']]['img'] ?? '-').'" 
            alt="'.htmlspecialchars($assocData['accoudoir_tissu'][$commande['id_accoudoir_tissu']]['nom'] ?? '-').'">
        <p>'. htmlspecialchars($assocData['accoudoir_tissu'][$commande['id_accoudoir_tissu']]['nom'] ?? '-') . '</p>
        <p>Quantité : ' . $quantite_accoudoir . '</p> <!-- Affichage de la quantité -->
      </div>';
?>


  <h3>Étape 7 : Choisi ta mousse</h3>
  <?php
  echo '<div class="option">
          <img src="../../admin/uploads/mousse/'.htmlspecialchars($assocData['mousse'][$commande['id_mousse']]['img'] ?? '-').'" 
              alt="'.htmlspecialchars($assocData['mousse'][$commande['id_mousse']]['nom'] ?? '-').'">
          <p>'. htmlspecialchars($assocData['mousse'][$commande['id_mousse']]['nom'] ?? '-') . '</p>
        </div>';
  ?>
</section>
      <div class="footer-processus">
          <p>Total : <span>899 €</span></p>
          <div class="buttons">
            <button class="btn-retour">Retour</button>
            <button class="btn-suivant"data-id="<?= htmlspecialchars($id) ?>">Générer un devis</button>
          </div>
        </div>
    </div>

      <!-- Popup devis -->
  <div id="pdf-popup" class="popup">
    <div class="popup-content">
      <h2>Commande finalisé !</h2>
      <p>Votre devis a été créé et envoyé à l'adresse suivante :
      </br><?php echo "<strong>" . htmlspecialchars($assocMail['client'][$commande['id_client']]['mail'] ?? '-') . "</strong>"; ?>
      </p>
        <br>
      <button class="close-btn">Fermer</button>
      <button class="pdf-btn">Voir le devis</button>
    </div>
  </div>

    <!-- Colonne de droite -->
    <div class="right-column">
      <section class="main-display-recap">
        <div class="buttons">
          <button class="btn-aide">Besoin d'aide ?</button>
          <button class="btn-abandonner">Abandonner</button>
        </div>
        <img src="../../medias/canapekenitra.png" alt="Armoire">

        
      <!-- Section commentaire -->
      <section class="comment-section">
      <?php if (!empty($message)) { echo $message; } ?>
      <h3>Ajoute un commentaire à propos de ta commande : </h3>
      <form action="" method="POST">
          <textarea class="textarea-custom" id="comment" name="comment" rows="5" placeholder="Écris ton commentaire ici..."></textarea>
          <button type="submit" class="btn-submit-com">Ajouter</button>
      </form>
      </section>
      </section>
    </div>
  </div>
  
<!-- Popup besoin d'aide -->
<div id="help-popup" class="popup">
  <div class="popup-content">
    <h2>Vous avez une question ?</h2>
    <p>Contactez nous au numéro suivant et un vendeur vous assistera : 
      <br><br>
    <strong>06 58 47 58 56</strong></p>
      <br>
    <button class="thank-btn">Merci !</button>
  </div>
</div>

<!-- Popup abandonner -->
<div id="abandonner-popup" class="popup">
  <div class="popup-content">
    <h2>Êtes vous sûr de vouloir abandonner ?</h2>
      <br>
    <button class="yes-btn">Oui ...</button>
    <button class="no-btn">Non !</button>
  </div>
</div>
</main>
<?php require_once '../../squelette/footer.php'; ?>


<script>
   document.addEventListener('DOMContentLoaded', () => {
    let totalPrice = 0; // Total global pour toutes les étapes

    // Charger l'ID utilisateur depuis une variable PHP intégrée dans le HTML
    const userId = document.body.getAttribute('data-user-id'); // Ex. <body data-user-id="<?php echo $_SESSION['user_id']; ?>">
    if (!userId) {
        console.error("ID utilisateur non trouvé. Vérifiez que 'data-user-id' est bien défini dans le HTML.");
        return;
    }
    console.log("ID utilisateur récupéré :", userId);

    // Charger toutes les options sélectionnées depuis sessionStorage (par utilisateur)
    const sessionKey = `allSelectedOptions_${userId}`;
    const selectedKey = `selectedOptions_${userId}`;
    let allSelectedOptions = JSON.parse(sessionStorage.getItem(sessionKey)) || [];
    let selectedOptions = JSON.parse(sessionStorage.getItem(selectedKey)) || {}; // Charger `selectedOptions` pour cet utilisateur
    console.log("Données globales récupérées depuis sessionStorage :", allSelectedOptions);

    // Vérifier si `allSelectedOptions` est un tableau
    if (!Array.isArray(allSelectedOptions)) {
        allSelectedOptions = [];
        console.warn("allSelectedOptions n'était pas un tableau. Réinitialisé à []");
    }

    function updateTotal() {
    // Vérifier le contenu de allSelectedOptions
    console.log("Contenu actuel de allSelectedOptions :", allSelectedOptions);

    // Calculer le total global
    totalPrice = allSelectedOptions.reduce((sum, option) => {
        const price = parseFloat(option.price || 0); // Vérifie que le prix est un nombre
        const quantity = parseInt(option.quantity || 1); // Par défaut, une unité
        return sum + (price * quantity);
    }, 0);

    console.log("Total calculé :", totalPrice);

    // Mettre à jour l'élément dans l'interface
    const totalElement = document.querySelector(".footer-processus p span");
    if (totalElement) {
        totalElement.textContent = `${totalPrice.toFixed(2)} €`;
    } else {
        console.error("L'élément '.footer-processus p span' est introuvable !");
    }
}

    // Sauvegarder les données mises à jour dans sessionStorage
    function saveData() {
        sessionStorage.setItem(sessionKey, JSON.stringify(allSelectedOptions));
        sessionStorage.setItem(selectedKey, JSON.stringify(selectedOptions));
    }

    // Initialiser le total dès le chargement de la page
    updateTotal();

    // Sauvegarder les données au chargement de la page (au cas où elles sont modifiées)
    saveData();
});

  </script>
</body>
</html>

