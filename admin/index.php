<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Connexion</title>
    <link rel="icon" type="image/x-icon" href="../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/buttons.css">
    <link rel="stylesheet" href="../styles/message.css">
    <link rel="stylesheet" href="../styles/admin/ajout.css">
    <script src="../script/togglePassword.js"></script>

</head>

<body>

    <main class="connexion">
        <div class="container">
            <h2>Connexion</h2>
            <?php
            require 'config.php';
            session_start();

            try {
                $bddlink = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                $bddlink->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Erreur de connexion à la base de données : " . $e->getMessage());
            }

            $error_message = "";
            $old_login = "";

            if (isset($_POST['connecter'])) {
                if (!empty($_POST['login']) && !empty($_POST['mdp'])) {
                    $login = trim($_POST['login']);
                    $mdp = trim($_POST['mdp']);
                    $old_login = htmlspecialchars($login); // on garde le champ email pour le réafficher

                    $requete = $bddlink->prepare('SELECT * FROM utilisateur WHERE mail = ?');
                    $requete->execute([$login]);

                    if ($requete->rowCount() > 0) {
                        $utilisateur = $requete->fetch();
                        if (password_verify($mdp, $utilisateur['mdp'])) {
                            $_SESSION['mail'] = $utilisateur['mail'];
                            $_SESSION['id'] = $utilisateur['id'];
                            header('Location: pages/index.php');
                            exit();
                        } else {
                            $error_message = "Le mot de passe est incorrect.";
                        }
                    } else {
                        $error_message = "L'adresse e-mail est incorrecte.";
                    }
                } else {
                    $error_message = "Veuillez compléter les champs vide.";
                }
            }

            if (!empty($error_message)) {
                echo "<div class='message error'>" . htmlspecialchars($error_message) . "</div>";
            }
            ?>


            <div class="form">
                <form action="" method="POST" class="formulaire-connexion">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="login">Adresse mail</label>
                            <input type="email" id="login" name="login" class="input-field" value="<?= $old_login ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mdp">Mot de passe</label>
                            <div class="input-section">
                                <input type="password" id="mdp" name="mdp" class="input-field" required>
                                <span class="toggle-password-text"
                                    style="cursor: pointer; color: #666; user-select: none; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-weight: 100;">
                                    Afficher
                                </span>
                            </div>
                            <div id="caps-lock-warning" style="display:none; color: grey; font-size: 0.95em; margin-top: 4px;">
                                ⚠️ Attention : Verr Maj est activé !
                            </div>
                            <div id="shift-warning" style="display:none; color: grey; font-size: 0.95em; margin-top: 4px;">
                                ⚠️ Attention : La touche Maj (Shift) est maintenue !
                            </div>
                        </div>
                    </div>
                    <div class="footer">
                        <p><span><a href="include/reset_pswd.php" class="link-connect">Mot de passe oublié ?</a></span></p>
                    </div>
                    <div class="button-section">
                        <p>Revenir sur <span><a href="../front/pages/index.php" class="link-connect">Deco du monde</a></span></p>
                        <div class="buttons">
                            <button type="submit" name="connecter" class="btn-noir">Se connecter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>


    <script>
        const passwordInput = document.getElementById('mdp');
        const capsLockWarning = document.getElementById('caps-lock-warning');
        const shiftWarning = document.getElementById('shift-warning');

        passwordInput.addEventListener('keydown', function(event) {
            if (event.getModifierState && event.getModifierState('CapsLock')) {
                capsLockWarning.style.display = 'block';
            } else {
                capsLockWarning.style.display = 'none';
            }

            if (event.shiftKey) {
                shiftWarning.style.display = 'block';
            } else {
                shiftWarning.style.display = 'none';
            }
        });

        passwordInput.addEventListener('keyup', function(event) {
            if (!(event.getModifierState && event.getModifierState('CapsLock'))) {
                capsLockWarning.style.display = 'none';
            }

            if (!event.shiftKey) {
                shiftWarning.style.display = 'none';
            }
        });
    </script>
</body>

</html>