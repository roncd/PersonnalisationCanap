<?php
session_start();

if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php"); // Redirection vers la page de connexion
    exit();
}
require '../config.php';

$id = $_SESSION['id'];

$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte</title>
    <link rel="icon" type="image/png" href="https://www.decorient.fr/medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../styles/admin/fiche-client.css">

</head>

<body>
    <header>
        <?php require '../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
            <h1>Mon compte</h1>
            <div class="bouton">
            <?php  echo " <a href='../utilisateur/edit.php?id={$user['id']}' class='btn'>Modifier les informations</a>"?>
            </div>
            <?php if ($user): ?>
                <div class="account-grid">
                    <!-- Bloc identité -->
                    <section class="info-card">
                        <h2>
                            <?= htmlspecialchars($user['nom']) . ' ' . htmlspecialchars($user['prenom']) . ' [' . htmlspecialchars($user['id']) . ']' ?>
                        </h2>
                        <p>
                            Titre de civilité :  <?= htmlspecialchars($user['civilite']) ?><br>
                            Date d'inscription :  <?= htmlspecialchars($user['date_creation']) ?><br>
                            Profil : <?= htmlspecialchars($user['profil']) ?>
                        </p>
                    </section>

                    <!-- Bloc contact -->
                    <section class="info-card">
                        <h2>CONTACT</h2>
                        <p>
                            Adresse mail : <?= htmlspecialchars($user['mail']) ?><br>
                            Téléphone : <?= ($user['tel']) ?>
                        </p>
                    </section>
                </div>
            <?php else: ?>
                <p>Utilisateur non trouvé.</p>
            <?php endif; ?>
        </div>
        </div>
    </main>
    <footer>
        <?php require '../squelette/footer.php'; ?>
    </footer>
</body>

</html>