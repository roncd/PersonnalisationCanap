<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/formulaire.css">
    <link rel="stylesheet" href="../../styles/modif-pswd.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
</head>

<body>
    <main class="connexion">
        <div class="container">
            <h2>Entrez un nouveau mot de passe</h2>
            <?php
            require '../../admin/config.php';
            if (!isset($_GET['token'])) {
                die("<div class='message error'>Lien invalide.</div>");
            }

            $token = $_GET['token'];
            $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE reset_token = :token AND reset_expires > NOW()");
            $stmt->execute(['token' => $token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                die("<div class='message error'>Lien invalide ou expiré.</div>");
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $new_password = $_POST['new_password'];

                // Vérifier que le mot de passe est différent de l'ancien
                if (password_verify($new_password, $user['mdp'])) {
                    echo "<div class='message error'>Le nouveau mot de passe doit être différent de l'ancien.</div>";
                } else {
                    // Hasher le nouveau mot de passe
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE utilisateur SET mdp = :mdp, reset_token = NULL, reset_expires = NULL WHERE id = :id");
                    $stmt->execute(['mdp' => $hashed_password, 'id' => $user['id']]);
                    echo "<div class='message success'>Votre mot de passe a été mis à jour.</div>";
                    header("refresh:3;url=../index.php");
                    exit;
                }
            }
            ?>

            <form method="POST" class="form">
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe <span class="required">*</span></label>
                    <input type="password" id="new_password" name="new_password" class="input-field" required>
                </div>
                <div class="button-section">
                    <p>Revenir sur <span><a href="../index.php" class="link-connect">la page de connexion</a></span></p>
                    <div class="buttons">
                        <button type="submit" class="btn-noir">Mettre à jour</button>
                    </div>
                </div>
            </form>
        </div>
    </main>
</body>

</html>