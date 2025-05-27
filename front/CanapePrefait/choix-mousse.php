<?php
require '../../admin/config.php';
session_start();   

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../formulaire/Connexion.php"); 
    exit;
}

// Récupérer les types de mousse depuis la base de données
$stmt = $pdo->query("SELECT * FROM mousse");
$mousse = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialiser le prix total à 0
$totalPrice = 0.00;

// Vérifie si une mousse a été sélectionnée précédemment pour cet utilisateur
$stmt = $pdo->prepare("
    SELECT m.prix 
    FROM commande_temporaire ct
    JOIN mousse m ON ct.id_mousse = m.id
    WHERE ct.id_client = ?
");
$stmt->execute([$_SESSION['user_id']]);
$selected = $stmt->fetch(PDO::FETCH_ASSOC);

if ($selected) {
    $totalPrice = (float) $selected['prix'];
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {    
   
    $id_client = $_SESSION['user_id'];
    $id_mousse = $_POST['mousse_id'] ?? null; // Sécurité

    if ($id_mousse !== null) {
        // Vérifier si une commande temporaire existe déjà pour cet utilisateur
        $stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ?");
        $stmt->execute([$id_client]);
        $existing_order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_order) {
            $stmt = $pdo->prepare("UPDATE commande_temporaire SET id_mousse = ? WHERE id_client = ?");
            $stmt->execute([$id_mousse, $id_client]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO commande_temporaire (id_client, id_mousse) VALUES (?, ?)");
            $stmt->execute([$id_client, $id_mousse]);
        }

        // Rediriger vers l'étape suivante
        header("Location: etape8-1-bois-tissu.php");
        exit;
    } else {
        // Optionnel : afficher un message d'erreur si aucune mousse n'a été sélectionnée
        $error = "Veuillez sélectionner une mousse.";
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
  <link rel="stylesheet" href="../../styles/canapPrefait.css">
  <title>Choisi ta mousse</title>
</head>

<body>


  <header>
    <?php require '../../squelette/header.php'; ?>
  </header>


  <main>

 
    <div class="container">
      <!-- Colonne de gauche -->
      <div class="left-column">
        <h2 class="h2">Choisi ta mousddfsfse</h2>
       
      <section class="color-options">
        <?php if (!empty($mousse)): ?>
          <?php foreach ($mousse as $mousse_bois): ?>
            <div class="option">
              <img src="../../admin/uploads/mousse/<?php echo htmlspecialchars($mousse_bois['img']); ?>" 
                   alt="<?php echo htmlspecialchars($mousse_bois['nom']); ?>" 
                   data-mousse-bois-id="<?php echo $mousse_bois['id']; ?>" 
                   data-mousse-bois-prix="<?php echo $mousse_bois['prix']; ?>"> 
              <p><?php echo htmlspecialchars($mousse_bois['nom']); ?></p>
              <p><strong><?php echo htmlspecialchars($mousse_bois['prix']); ?> €</strong></p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>Aucune mousse disponible pour le moment.</p>
        <?php endif; ?>          
      </section>


        <div class="footer">
          <p>Total : <span id="total-price"><?php echo number_format($totalPrice, 2, ',', ' '); ?> €</span></p>
          <div class="buttons">
            <button class="btn-retour " onclick="history.go(-1)">Retour</button>
            <form method="POST" action="">
              <input type="hidden" name="couleur_bois_id" id="selected-couleur_bois">
              <button type="submit" class="btn-suivant">Suivant</button>
            </form>
          </div>
        </div>
      </div>


      <!-- Colonne de droite -->
      <div class="right-column h2 ">
        <section class="main-display2">
          <img src="../../medias/meknes.png" alt="Armoire" class="">
        </section>
      </div>
    </div>


    <!-- Popup besoin d'aide -->
    <div id="help-popup" class="popup transition">
      <div class="popup-content">
        <h2>Vous avez une question ?</h2>
        <p>Contactez nous au numéro suivant et un vendeur vous assistera :
          <br><br>
          <strong>06 58 47 58 56</strong>
        </p>
        <br>
        <button class="close-btn">Merci !</button>
      </div>
    </div>


    <!-- Popup abandonner -->
    <div id="abandonner-popup" class="popup transition">
      <div class="popup-content">
        <h2>Êtes vous sûr de vouloir abandonner ?</h2>
        <br>
        <button class="yes-btn">Oui ...</button>
        <button class="no-btn">Non !</button>
      </div>
    </div>


    <div id="selection-popup" class="popup transition">
      <div class="popup-content">
        <h2>Veuillez choisir une option avant de continuer.</h2>
        <br>
        <button class="close-btn">OK</button>
      </div>
    </div>
  </main>


  <?php require_once '../../squelette/footer.php'; ?>


</body>

</html>