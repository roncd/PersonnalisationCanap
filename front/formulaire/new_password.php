<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/formulaire.css">
    <link rel="stylesheet" href="../styles/ajout.css">
    <style>
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

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
        }

        .connexion {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 550px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        h2 {
            font-family: 'Baloo 2', sans-serif;
            margin-bottom: 20px;
        }

        .form {
            width: 100%;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            text-align: left;
            margin-bottom: 15px;
        }

        .input-field {
            width: 95%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .buttons {
            margin-top: 10px;
        }

        .btn-connexion {
            background-color: rgb(0, 0, 0);
            color: white;
            padding: 10px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        .footer {
            margin-top: 15px;
            font-size: 14px;
        }

        .link-connect {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .link-connect:hover {
            text-decoration: underline;
        }
    </style>
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
            $stmt = $pdo->prepare("SELECT * FROM client WHERE reset_token = :token AND reset_expires > NOW()");
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
                    $stmt = $pdo->prepare("UPDATE client SET mdp = :mdp, reset_token = NULL, reset_expires = NULL WHERE id = :id");
                    $stmt->execute(['mdp' => $hashed_password, 'id' => $user['id']]);
                    echo "<div class='message success'>Votre mot de passe a été mis à jour.</div>";
                    header("refresh:3;url=Connexion.php");
                    exit;
                }
            }
            ?>

            <form method="POST" class="form">
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe :</label>
                    <input type="password" id="new_password" name="new_password" class="input-field" required>
                </div>
                <div class="buttons">
                    <button type="submit" class="btn-connexion">Mettre à jour</button>
                </div>
            </form>
            <div class="footer">
                <p>Revenir sur <span><a href="../pages/index.php" class="link-connect">Deco du monde</a></span></p>
            </div>
        </div>
    </main>
</body>

</html>