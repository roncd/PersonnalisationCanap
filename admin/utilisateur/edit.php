<?php
require '../config.php';
session_start();
require '../include/session_expiration.php';

if (!isset($_SESSION['id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['message'] = 'ID du membre manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}

// Récupérer les données actuelles de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE  id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$utilisateur) {
    $_SESSION['message'] = 'Membre introuvable.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et valider les données
    $mail = trim($_POST['mail']);
    $mdp = password_hash($_POST['mdp'], PASSWORD_BCRYPT);
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $civilite = trim($_POST['civilite']);
    $profil = trim($_POST['profil']);
    $tel = trim($_POST['tel']);

    if (empty($nom) || empty($prenom) || empty($mail) || empty($mdp)) {
        $_SESSION['message'] = 'Tous les champs requis doivent être remplis.';
        $_SESSION['message_type'] = 'error';
    } else {
        // Mettre à jour de l'utilisateur dans la base de données
        $stmt = $pdo->prepare("UPDATE utilisateur SET mail = ?, mdp = ?, nom = ?, prenom = ?, civilite = ?, profil = ?, tel = ? WHERE id = ?");
        $stmt->execute([$mail, $mdp, $nom, $prenom, $civilite, $profil, $tel, $id]);

        $_SESSION['message'] = 'Membre mis à jour avec succès!';
        $_SESSION['message_type'] = 'success';
        header("Location: visualiser.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifie un membre</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/admin/ajout.css">
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link rel="stylesheet" href="../../styles/message.css">
    
    <link rel="stylesheet" href="../../styles/buttons.css">
</head>

<body>

    <header>
        <?php require '../squelette/header.php'; ?>
    </header>
    <main>
        <div class="container">
            <h2>Modifie un membre de l'équipe</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form action="edit.php?id=<?php echo $utilisateur['id']; ?>" method="POST" enctype="multipart/form-data" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="civilite">Titre de civilité</label>
                            <select class="input-field" id="civilite" name="civilite">
                                <option value="<?php echo htmlspecialchars($utilisateur['civilite']); ?>"><?php echo htmlspecialchars($utilisateur['civilite']); ?></option>
                                <?php
                                $options = ["Mme." => "Madame", "M." => "Monsieur", "Pas précisé" => "Ne souhaite pas préciser"];

                                // Boucle pour afficher uniquement les options différentes
                                foreach ($options as $value => $label) {
                                    if ($value !== $utilisateur['civilite']) {
                                        echo "<option value=\"$value\">$label</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom <span class="required">*</span></label>
                            <input type="name" id="nom" name="nom" class="input-field" value="<?php echo htmlspecialchars($utilisateur['nom']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="prenom">Prénom <span class="required">*</span></label>
                            <input type="name" id="prenom" name="prenom" class="input-field" value="<?php echo htmlspecialchars($utilisateur['prenom']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Mail <span class="required">*</span></label>
                            <input type="email" id="email" name="mail" class="input-field" value="<?php echo htmlspecialchars($utilisateur['mail']); ?>" required>
                        </div>
                        <div class="form-group">
                        <label for="mdp">Mot de passe <span class="required">*</span></label>
                            <a href="../include/changer_mdp.php?id=<?php echo $utilisateur['id']; ?>"
                                class="input-field"
                                style=" text-decoration: none; color: black; text-align: center;">
                                Modifier le mot de passe
                            </a>
                        </div>
                    </div>
                    <div class="form-row">
                    <div class="form-group">
                            <label for="tel">Téléphone</label>
                            <input type="phone" id="tel" name="tel" class="input-field" value="<?php echo ($utilisateur['tel']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="profil">Profil</label>
                            <select class="input-field" id="profil" name="profil">
                                <option value="<?php echo htmlspecialchars($utilisateur['profil']); ?>"><?php echo htmlspecialchars($utilisateur['profil']); ?></option>
                                <?php
                                $options = ["Administrateur" => "Administrateur" , "Commercial" => "Commercial"];

                                // Boucle pour afficher uniquement les options différentes
                                foreach ($options as $value => $label) {
                                    if ($value !== $utilisateur['profil']) {
                                        echo "<option value=\"$value\">$label</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="button-section">
                        <div class="buttons">
                            <button type="button" id="btn-retour" class="btn-beige" onclick="history.go(-1)">Retour</button>
                            <input type="submit"  class="btn-noir" value="Mettre à jour"></input>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <footer>
        <?php require '../squelette/footer.php'; ?>
    </footer>
</body>

</html>