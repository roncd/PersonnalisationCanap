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
$stmt = $pdo->prepare("SELECT id, longueurA, longueurB, longueurC FROM dimension");
$stmt->execute();
$data['dimension'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
$assocData['dimension'] = [];

foreach ($data['dimension'] as $dim) {
  $assocData['dimension'][$dim['id']] = [
    'longueurA' => $dim['longueurA'],
    'longueurB' => $dim['longueurB'],
    'longueurC' => $dim['longueurC']
];}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['comment'])) {
  // Vérifier si un commentaire a été saisi
  if (!empty($_POST["comment"])) {
      $commentaire = trim($_POST["comment"]); 

      if ($commande) {
        $id = $commande['id']; // Récupérer l'ID de la commande temporaire
          // Mettre à jour la commande temporaire avec le commentaire
          $stmt = $pdo->prepare("UPDATE commande_temporaire SET commentaire = ? WHERE commande_temporaire.id = ?");
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
  <title>Récapitulatif de la commande</title>
  <style>
    .footer p {
      margin-bottom: 20px; /* Augmente l'espace entre le texte et les boutons */
    }
    .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
    .success {
            background-color: #d4edda;
            color: #155724;
        }
    .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
  </style>
</head>
<body>

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
          <img src="../../admin/uploads/structure/'.htmlspecialchars($assocData['structure'][$commande['id_structure']]['img'] ?? 'N/A').'" 
              alt="'.htmlspecialchars($assocData['structure'][$commande['id_structure']]['nom'] ?? 'N/A').'">
          <p>'. htmlspecialchars($assocData['structure'][$commande['id_structure']]['nom'] ?? 'N/A') . '</p>
        </div>';
  ?>
 
  <h3>Étape 1 : Choisi tes dimensions</h3>
  <?php
  echo '
        <div class="dimension-container">
        <p class="input-field">Longueur banquette A (en cm): ' . htmlspecialchars($dim['longueurA']?? 'N/A') . '</p>
        </div> <div class="dimension-container">
        <p class="input-field">Longueur banquette B (en cm): ' . htmlspecialchars($dim['longueurB']?? 'N/A') . '</p>
        </div> <div class="dimension-container">
        <p class="input-field">Longueur banquette C (en cm): ' . htmlspecialchars($dim['longueurC']?? 'N/A') . '</p>   
        </div>';
  ?>

  <h3>Étape 2 : Choisi ton type de banquette</h3>
  <?php
  echo '<div class="option">
          <img src="../../admin/uploads/banquette/'.htmlspecialchars($assocData['type_banquette'][$commande['id_banquette']]['img'] ?? 'N/A').'" 
              alt="'.htmlspecialchars($assocData['type_banquette'][$commande['id_banquette']]['nom'] ?? 'N/A').'">
          <p>'. htmlspecialchars($assocData['type_banquette'][$commande['id_banquette']]['nom'] ?? 'N/A') . '</p>
        
        </div>';
  ?>

  <h3>Étape 3 : Choisi ton modèle</h3>
  <?php
  echo '<div class="option">
          <img src="../../admin/uploads/modele/'.htmlspecialchars($assocData['modele'][$commande['id_modele']]['img'] ?? 'N/A').'" 
              alt="'.htmlspecialchars($assocData['modele'][$commande['id_modele']]['nom'] ?? 'N/A').'">
          <p>'. htmlspecialchars($assocData['modele'][$commande['id_modele']]['nom'] ?? 'N/A') . '</p>
        </div>';
  ?>
  <h3>Étape 4 : Choisi ta couleur de tissu</h3>
  <?php
  echo '<div class="option">
          <img src="../../admin/uploads/couleur-tissu-tissu/'.htmlspecialchars($assocData['couleur_tissu'][$commande['id_couleur_tissu']]['img'] ?? 'N/A').'" 
              alt="'.htmlspecialchars($assocData['couleur_tissu'][$commande['id_couleur_tissu']]['nom'] ?? 'N/A').'">
          <p>'. htmlspecialchars($assocData['couleur_tissu'][$commande['id_couleur_tissu']]['nom'] ?? 'N/A') . '</p>
        </div>';
  ?>

  <h3>Étape 4 : Choisi ton motif de coussin</h3>
  <?php
  echo '<div class="option">
          <img src="../../admin/uploads/motif-tissu/'.htmlspecialchars($assocData['motif_tissu'][$commande['id_motif_tissu']]['img'] ?? 'N/A').'" 
              alt="'.htmlspecialchars($assocData['motif_tissu'][$commande['id_motif_tissu']]['nom'] ?? 'N/A').'">
          <p>'. htmlspecialchars($assocData['motif_tissu'][$commande['id_motif_tissu']]['nom'] ?? 'N/A') . '</p>
        </div>';
  ?>

  <h3>Étape 5 : Choisi ton dossier</h3>
   <?php
  echo '<div class="option">
          <img src="../../admin/uploads/dossier-tissu/'.htmlspecialchars($assocData['dossier_tissu'][$commande['id_dossier_tissu']]['img'] ?? 'N/A').'" 
              alt="'.htmlspecialchars($assocData['dossier_tissu'][$commande['id_dossier_tissu']]['nom'] ?? 'N/A').'">
          <p>'. htmlspecialchars($assocData['dossier_tissu'][$commande['id_dossier_tissu']]['nom'] ?? 'N/A') . '</p>
        </div>';
  ?>

  <h3>Étape 6 : Choisi tes accoudoirs</h3>
  <?php
  echo '<div class="option">
          <img src="../../admin/uploads/accoudoirs-tissu/'.htmlspecialchars($assocData['accoudoir_tissu'][$commande['id_accoudoir_tissu']]['img'] ?? 'N/A').'" 
              alt="'.htmlspecialchars($assocData['accoudoir_tissu'][$commande['id_accoudoir_tissu']]['nom'] ?? 'N/A').'">
          <p>'. htmlspecialchars($assocData['accoudoir_tissu'][$commande['id_accoudoir_tissu']]['nom'] ?? 'N/A') . '</p>
        </div>';
  ?>

  <h3>Étape 7 : Choisi ta mousse</h3>
  <?php
  echo '<div class="option">
          <img src="../../admin/uploads/mousse/'.htmlspecialchars($assocData['mousse'][$commande['id_mousse']]['img'] ?? 'N/A').'" 
              alt="'.htmlspecialchars($assocData['mousse'][$commande['id_mousse']]['nom'] ?? 'N/A').'">
          <p>'. htmlspecialchars($assocData['mousse'][$commande['id_mousse']]['nom'] ?? 'N/A') . '</p>
        </div>';
  ?>
</section>
      <div class="footer-processus">
          <p>Total : <span>899 €</span></p>
          <div class="buttons">
            <button class="btn-retour">Retour</button>
            <button class="btn-suivant">Générer un devis</button>
          </div>
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
    <button class="close-btn">Merci !</button>
  </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', () => {
  const openButton = document.querySelector('.btn-aide'); // Bouton pour ouvrir le popup
  const popup = document.getElementById('help-popup');
  const closeButton = document.querySelector('.close-btn'); // Bouton "Merci !" pour fermer le popup

  // Afficher le popup
  openButton.addEventListener('click', () => {
    console.log('Bouton Aide cliqué');
    popup.style.display = 'flex';
  });

  // Masquer le popup avec le bouton "Merci !"
  closeButton.addEventListener('click', () => {
    console.log('Bouton Merci cliqué');
    popup.style.display = 'none';
  });

  // Fermer le popup si clic à l'extérieur
  window.addEventListener('click', (event) => {
    if (event.target === popup) {
      console.log('Clic à l\'extérieur du popup');
      popup.style.display = 'none';
    }
  });
});
</script>

<!-- Popup abandonner -->
<div id="abandonner-popup" class="popup">
  <div class="popup-content">
    <h2>Êtes vous sûr de vouloir abandonner ?</h2>
      <br>
    <button class="yes-btn">Oui ...</button>
    <button class="no-btn">Non !</button>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const openButton = document.querySelector('.btn-abandonner'); // Bouton pour ouvrir le popup
  const popup = document.getElementById('abandonner-popup');
  const yesButton = document.querySelector('.yes-btn'); // Bouton "Oui ..." pour redirection
  const noButton = document.querySelector('.no-btn'); // Bouton "Non !" pour fermer le popup

  // Afficher le popup
  openButton.addEventListener('click', () => {
    console.log('Bouton Abandonner cliqué');
    popup.style.display = 'flex';
  });

  // Rediriger vers la page d'accueil avec le bouton "Oui ..."
  yesButton.addEventListener('click', () => {
    console.log('Redirection vers la page d\'accueil');
    window.location.href = '../pages/'; // Remplace '/' par l'URL de votre page d'accueil
  });

  // Masquer le popup avec le bouton "Non !"
  noButton.addEventListener('click', () => {
    console.log('Popup fermé via le bouton Non !');
    popup.style.display = 'none';
  });

  // Fermer le popup si clic à l'extérieur
  window.addEventListener('click', (event) => {
    if (event.target === popup) {
      console.log('Clic à l\'extérieur du popup');
      popup.style.display = 'none';
    }
  });
});
</script>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    // Sélection des boutons
    const retourButton = document.querySelector('.btn-retour');
    const suivantButton = document.querySelector('.btn-suivant');

    // Action du bouton "Retour" : rediriger vers la page précédente
    retourButton.addEventListener('click', () => {
      window.history.back(); // Navigue vers la page précédente
    });

    // Action du bouton "Suivant" : rediriger vers la page suivante
    suivantButton.addEventListener('click', () => {
      window.location.href = 'recapitulatif-commande.php'; // Remplacez par le lien de la page suivante
    });
  });
</script>

</main>

<?php require_once '../../squelette/footer.php'; ?>
</body>
</html>
