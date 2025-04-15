<?php
require '../../admin/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['adresse'];
    $password = $_POST['motdepasse'];

    // Ligne de débogage
    echo 'Email : ' . htmlspecialchars($email) . '<br>';
    echo 'Mot de passe : ' . htmlspecialchars($password) . '<br>';

    // Change 'email' to 'mail' in the SQL query
    $stmt = $pdo->prepare("SELECT * FROM client WHERE mail = :mail");
    $stmt->execute(['mail' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Ligne de débogage
        echo 'Hash stocké : ' . htmlspecialchars($user['mdp']) . '<br>';

        // Change 'mot_de_passe' to 'mdp'
        if (password_verify($password, $user['mdp'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['prenom'];
            if (!empty($_SESSION['redirect_to'])) {
                $redirect_to = $_SESSION['redirect_to'];
                unset($_SESSION['redirect_to']);
                header("Location: $redirect_to");
            } else {
                header("Location: ../pages/index.php");
            }
            exit;
        } else {
            echo 'La vérification du mot de passe a échoué.<br>';
            header("Location: Connexion.php?erreur=1");
            exit;
        }
    } else {
        echo 'Utilisateur non trouvé.<br>';
        header("Location: Connexion.php?erreur=1");
        exit;
    }
}
