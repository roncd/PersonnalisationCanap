<?php
require '../config.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['message'] = 'ID du client manquant.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}

// Récupérer les données actuelles du client
$stmt = $pdo->prepare("SELECT * FROM client WHERE id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    $_SESSION['message'] = 'Client introuvable.';
    $_SESSION['message_type'] = 'error';
    header("Location: visualiser.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et valider les données
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $mail = trim($_POST['mail']);
    $tel = trim($_POST['tel']);
    $mdp = password_hash($_POST['mdp'], PASSWORD_BCRYPT);
    $adresse = trim($_POST['adresse']);
    $info = trim($_POST['info']);
    $codepostal = trim($_POST['codepostal']);
    $ville = trim($_POST['ville']);
    $date = trim($_POST['date']);
    $civilite = trim($_POST['civilite']);

    if (empty($nom) || empty($prenom) || empty($mail) || empty($tel) || empty($mdp) || empty($adresse) || empty($codepostal) || empty($ville) || empty($date)) {
        $_SESSION['message'] = 'Tous les champs requis doivent être remplis.';
        $_SESSION['message_type'] = 'error';
    } else {
        // Mettre à jour du client dans la base de données
        $stmt = $pdo->prepare("UPDATE client SET nom = ?, prenom = ?, mail = ?, tel = ?, mdp = ?, adresse = ?, info = ?, codepostal = ?, ville = ?, date_naissance = ?,  civilite = ? WHERE id = ?");
        if ($stmt->execute([$nom, $prenom, $mail, $tel, $mdp, $adresse, $info, $codepostal, $ville, $date, $civilite, $id])) {
            $_SESSION['message'] = 'Le client a été mis à jour avec succès !';
            $_SESSION['message_type'] = 'success';
            header("Location: visualiser.php");
            exit();
        } else {
            $_SESSION['message'] = 'Erreur lors de la mise à jour du client.';
            $_SESSION['message_type'] = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifie un client</title>
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
            <h2>Modifie un client</h2>
            <?php require '../include/message.php'; ?>
            <div class="form">
                <form action="edit.php?id=<?php echo $client['id']; ?>" method="POST" enctype="multipart/form-data" class="formulaire-creation-compte">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="civilite">Titre de civilité</label>
                            <select class="input-field" id="civilite" name="civilite">
                                <option value="<?php echo htmlspecialchars($client['civilite']); ?>"><?php echo htmlspecialchars($client['civilite']); ?></option>
                                <?php
                                $options = ["Mme." => "Madame", "M." => "Monsieur", "Pas précisé" => "Ne souhaite pas préciser"];

                                // Boucle pour afficher uniquement les options différentes
                                foreach ($options as $value => $label) {
                                    if ($value !== $client['civilite']) {
                                        echo "<option value=\"$value\">$label</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom</label>
                            <input type="name" id="nom" name="nom" class="input-field" value="<?php echo htmlspecialchars($client['nom']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="prenom">Prénom</label>
                            <input type="name" id="prenom" name="prenom" class="input-field" value="<?php echo htmlspecialchars($client['prenom']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Mail</label>
                            <input type="email" id="email" name="mail" class="input-field" value="<?php echo htmlspecialchars($client['mail']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="tel">Téléphone</label>
                            <input type="phone" id="tel" name="tel" class="input-field" value="<?php echo htmlspecialchars($client['tel']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mdp">Mot de passe</label>
                            <input type="password" id="mdp" class="input-field" name="mdp" value="<?php echo htmlspecialchars($client['mdp']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="adresse">Adresse</label>
                            <input type="text" id="adresse" class="input-field" name="adresse" value="<?php echo htmlspecialchars($client['adresse']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="info">Info suplémentaire</label>
                            <input type="text" id="info" class="input-field" name="info" value="<?php echo htmlspecialchars($client['info']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="codepostal">Code postal</label>
                            <input type="codepostal" id="codepostal" class="input-field" name="codepostal" value="<?php echo htmlspecialchars($client['codepostal']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ville">Ville</label>
                            <input type="ville" id="ville" class="input-field" name="ville" value="<?php echo htmlspecialchars($client['ville']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="date">Date de naissance</label>
                            <input type="date" id="date" class="input-field" name="date" value="<?php echo htmlspecialchars($client['date_naissance']); ?>" required>
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