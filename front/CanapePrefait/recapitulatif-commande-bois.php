<?php
require '../../admin/config.php';
session_start();
require '../../admin/include/session_expiration.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
  $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
  header("Location: ../formulaire/Connexion.php");
  exit;
}

$id_client = $_SESSION['user_id'];

$id_commande_prefait = $_GET['id'] ?? null;
if (!$id_commande_prefait) {
  die("ID de la commande non fourni.");
}


$stmt = $pdo->prepare("SELECT * FROM commande_temporaire WHERE id_client = ? AND id_commande_prefait = ?");
$stmt->execute([$id_client, $id_commande_prefait]);
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

$commentaire = "";
$message = "";

// Préremplir le commentaire si déjà existant
if ($commande && isset($commande['commentaire'])) {
  $commentaire = $commande['commentaire'];
}

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

$tables = [
  'structure',
  'type_banquette',
  'mousse',
  'couleur_bois',
  'accoudoir_bois',
  'dossier_bois',
  'couleur_tissu_bois',
  'motif_bois',
  'decoration'
];

function fetchData($pdo, $table)
{
  $stmt = $pdo->prepare("SELECT * FROM $table");
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$prixCommande = $commande['prix'] ?? 0;

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

$stmt = $pdo->prepare("SELECT cpa.id_accoudoir_bois, cpa.nb_accoudoir, ab.nom, ab.img
                       FROM commande_prefait_accoudoir cpa
                       JOIN accoudoir_bois ab ON cpa.id_accoudoir_bois = ab.id
                       WHERE cpa.id_commande_prefait = ?");
$stmt->execute([$id_commande_prefait]);
$accoudoirs = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Traitement du formulaire pour ajouter ou modifier un commentaire
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['comment'])) {
  $commentaire_saisi = trim($_POST["comment"]);

  if ($commande) {
    $ancien_commentaire = $commande['commentaire'] ?? '';

    // Cas : commentaire vide et ancien commentaire vide → erreur
    if ($commentaire_saisi === '' && $ancien_commentaire === '') {
      $message = '<p class="message error">Le commentaire ne peut pas être vide.</p>';
    } else {
      // Mettre à jour en base même si vide (pour supprimer)
      $stmt = $pdo->prepare("UPDATE commande_temporaire SET commentaire = ? WHERE id = ?");
      $stmt->execute([$commentaire_saisi, $id]);

      if ($commentaire_saisi === '' && $ancien_commentaire !== '') {
        $message = '<p class="message success">Commentaire supprimé avec succès.</p>';
      } elseif ($ancien_commentaire === '') {
        $message = '<p class="message success">Commentaire ajouté avec succès !</p>';
      } elseif ($commentaire_saisi !== $ancien_commentaire) {
        $message = '<p class="message success">Commentaire modifié avec succès !</p>';
      } else {
        $message = '<p class="message"></p>';
      }

      $commentaire = $commentaire_saisi;
    }
  } else {
    $message = '<p class="message error">Aucune commande trouvée pour cet utilisateur.</p>';
  }
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../styles/popup.css">
  <link rel="stylesheet" href="../../styles/canapPrefait.css">
  <link rel="stylesheet" href="../../styles/message.css">
  <link rel="stylesheet" href="../../styles/buttons.css">
  <script type="module" src="../../script/popup-bois.js"></script>
  <script type="module" src="../../script/popup.js"></script>

  <title>Récapitulatif de la commande</title>
</head>

<body data-user-id="<?php echo $_SESSION['user_id']; ?>">
  <?php include '../cookies/index.html'; ?>
  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>

  <main>
    <div class="container-recap transition">
      <!-- Colonne de gauche -->
      <div class="left-column-recap">
        <div class="buttons h2 space">
            <button id="btn-aide" class="btn-beige">Besoin d'aide ?</button>
            <button type="button" data-url="../pages/dashboard.php" id="btn-abandonner" class="btn-noir">Abandonner</button>
          </div>

        <h2>Récapitulatif de la commande préfaite</h2>
        <section class="color-options">

          <h3>Étape 1.1 : Choisi ta structure</h3>
          <?php
          echo '<div class="option">
          <img src="../../admin/uploads/structure/' . htmlspecialchars($assocData['structure'][$commande['id_structure']]['img'] ?? '-') . '" 
              alt="' . htmlspecialchars($assocData['structure'][$commande['id_structure']]['nom'] ?? '-') . '">
          <p>' . htmlspecialchars($assocData['structure'][$commande['id_structure']]['nom'] ?? '-') . '</p>
        </div>';
          ?>

          <h3>Étape 1.2 : Choisi tes dimensions</h3>
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
          <img src="../../admin/uploads/banquette/' . htmlspecialchars($assocData['type_banquette'][$commande['id_banquette']]['img'] ?? '-') . '" 
              alt="' . htmlspecialchars($assocData['type_banquette'][$commande['id_banquette']]['nom'] ?? '-') . '">
          <p>' . htmlspecialchars($assocData['type_banquette'][$commande['id_banquette']]['nom'] ?? '-') . '</p>
        </div>';
          ?>

          <h3>Étape 3 : Choisi ta couleur de bois</h3>
          <?php
          echo '<div class="option">
          <img src="../../admin/uploads/couleur-banquette-bois/' . htmlspecialchars($assocData['couleur_bois'][$commande['id_couleur_bois']]['img'] ?? '-') . '" 
              alt="' . htmlspecialchars($assocData['couleur_bois'][$commande['id_couleur_bois']]['nom'] ?? '-') . '">
          <p>' . htmlspecialchars($assocData['couleur_bois'][$commande['id_couleur_bois']]['nom'] ?? '-') . '</p>
        </div>';
          ?>
          <h3>Étape 4 : Choisi ta decoration</h3>
          <?php
          echo '<div class="option">
          <img src="../../admin/uploads/decoration/' . htmlspecialchars($assocData['decoration'][$commande['id_decoration']]['img'] ?? '-') . '" 
              alt="' . htmlspecialchars($assocData['decoration'][$commande['id_decoration']]['nom'] ?? '-') . '">
          <p>' . htmlspecialchars($assocData['decoration'][$commande['id_decoration']]['nom'] ?? '-') . '</p>
        </div>';
          ?>
          <h3>Étape 5 : Choisi tes accoudoirs</h3>
          <?php
          foreach ($accoudoirs as $accoudoir) {
            // Affichage de l'accoudoir avec son image, nom, et quantité
            echo '<div class="option">
              <img src="../../admin/uploads/accoudoirs-bois/' . htmlspecialchars($accoudoir['img'] ?? 'N/A') . '"
                  alt="' . htmlspecialchars($accoudoir['nom'] ?? 'N/A') . '">
              <p>' . htmlspecialchars($accoudoir['nom'] ?? 'N/A') . '</p>
              <p>Quantité : ' . htmlspecialchars($accoudoir['nb_accoudoir']) . '</p>
            </div>';
          }
          ?>


          <h3>Étape 6 : Choisi ton dossier</h3>
          <?php
          echo '<div class="option">
          <img src="../../admin/uploads/dossier-bois/' . htmlspecialchars($assocData['dossier_bois'][$commande['id_dossier_bois']]['img'] ?? '-') . '" 
              alt="' . htmlspecialchars($assocData['dossier_bois'][$commande['id_dossier_bois']]['nom'] ?? '-') . '">
          <p>' . htmlspecialchars($assocData['dossier_bois'][$commande['id_dossier_bois']]['nom'] ?? '-') . '</p>
        </div>';
          ?>

          <h3>Étape 7.1 : Choisi ton tissu</h3>
          <?php
          echo '<div class="option">
          <img src="../../admin/uploads/couleur-tissu-bois/' . htmlspecialchars($assocData['couleur_tissu_bois'][$commande['id_couleur_tissu_bois']]['img'] ?? '-') . '" 
              alt="' . htmlspecialchars($assocData['couleur_tissu_bois'][$commande['id_couleur_tissu_bois']]['nom'] ?? '-') . '">
          <p>' . htmlspecialchars($assocData['couleur_tissu_bois'][$commande['id_couleur_tissu_bois']]['nom'] ?? '-') . '</p>
        </div>';
          ?>

          <h3>Étape 7.2 : Choisi ton motif de coussin</h3>
          <?php
          echo '<div class="option">
          <img src="../../admin/uploads/motif-bois/' . htmlspecialchars($assocData['motif_bois'][$commande['id_motif_bois']]['img'] ?? '-') . '" 
              alt="' . htmlspecialchars($assocData['motif_bois'][$commande['id_motif_bois']]['nom'] ?? '-') . '">
          <p>' . htmlspecialchars($assocData['motif_bois'][$commande['id_motif_bois']]['nom'] ?? '-') . '</p>
        </div>';
          ?>

          <h3>Étape 8 : Choisi ta mousse</h3>
          <?php
          echo '<div class="option">
          <img src="../../admin/uploads/mousse/' . htmlspecialchars($assocData['mousse'][$commande['id_mousse']]['img'] ?? '-') . '" 
              alt="' . htmlspecialchars($assocData['mousse'][$commande['id_mousse']]['nom'] ?? '-') . '">
          <p>' . htmlspecialchars($assocData['mousse'][$commande['id_mousse']]['nom'] ?? '-') . '</p>
        </div>';
          ?>


        </section>


        <div class="footer-processus">
          <p>Total : <span><?= number_format($prixCommande, 2, ',', ' ') ?> €</span></p>
          <div class="buttons">
            <button onclick="retourEtapePrecedente()" class="btn-beige">Retour</button>
            <button id="btn-generer" class="btn-noir">Générer un devis</button>
          </div>
        </div>
      </div>

      <script>
        function retourEtapePrecedente() {
          const id = new URLSearchParams(window.location.search).get('id');
          if (id) {
            window.location.href = `choix-mousse.php?id=${id}`;
          } else {
            alert("ID introuvable dans l'URL.");
          }
        }
      </script>

      <!-- Colonne de droite -->
      <div class="right-column-recap">
        <section class="main-display-recap">
          <img src="../../medias/canapekenitra.png" alt="Armoire">

          <!-- Section commentaire -->
          <section class="comment-section">
            <?php if (!empty($message)) { ?>
              <div id="feedback-message" class="feedback-message">
                <?= $message ?>
              </div>
            <?php } ?>

            <h3>Ajoute un commentaire à propos de ta commande :</h3>
            <form action="" method="POST">
              <textarea class="textarea-custom" id="comment" name="comment" rows="5" placeholder="Écris ton commentaire ici..."><?= htmlspecialchars($commentaire) ?></textarea>
              <button type="submit" class="btn-noir">
                <?= empty($commentaire) ? 'Ajouter' : 'Modifier' ?>
              </button>
            </form>
          </section>

      </div>
    </div>

    <!-- Popup besoin d'aide -->
    <div id="help-popup" class="popup">
      <div class="popup-content">
        <h2>Vous avez une question ?</h2>
        <p>Contactez-nous au numéro suivant et un vendeur vous assistera :
          <br><br>
          <strong>06 58 47 58 56</strong>
        </p>
        <br>
        <button class="btn-noir">Merci !</button>
      </div>
    </div>

    <!-- Popup abandonner -->
    <div id="abandonner-popup" class="popup">
      <div class="popup-content">
        <h2>Êtes vous sûr de vouloir abandonner ?</h2>
        <br>
        <button class="btn-beige">Oui...</button>
        <button class="btn-noir">Non !</button>
      </div>
    </div>

    <!-- Popup validation generation -->
    <div id="generer-popup" class="popup">
      <div class="popup-content">
        <h2>Êtes vous sûr de vouloir générer un devis ?</h2>
        <p>Vous ne pourrez plus effectuer de modifictions sur votre commande</p>
        <button id="btn-oui" class="btn-beige" name="envoyer" data-id="<?= htmlspecialchars($id) ?>">Oui</button>
        <button id="btn-close" class="btn-noir">Non</button>
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
        <button onclick="location.href='../pages/commandes.php'" class="btn-beige">Voir mes commandes</button>
        <button id="pdf-btn" class="btn-noir">Voir le devis</button>
      </div>
    </div>
    </div>

  </main>
  <?php require_once '../../squelette/footer.php'; ?>

  <script>
    setTimeout(() => {
      const msg = document.getElementById('feedback-message');
      if (msg) {
        msg.style.transition = "opacity 1s ease";
        msg.style.opacity = "0";

        // Optionnel : supprimer complètement du DOM après animation
        setTimeout(() => msg.remove(), 100);
      }
    }, 5000); // 20 secondes
  </script>

</body>

</html>