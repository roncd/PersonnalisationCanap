<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
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
            <button class="btn" type="button">Modifier les informations</button>
            </div>
            <div class="account-grid">
                <!-- Bloc identité -->
                <section class="info-card">
                    <h2>NOM PRÉNOM [ID]</h2>
                    <p>Titre de civilité : <br>
                        Date d'inscription : <br>
                        Profil : </p>
                </section>
                <!-- Bloc contact -->
                <section class="info-card">
                    <h2>CONTACT</h2>
                    <p>Adresse mail :<br>
                        Téléphone :</p>
                </section>
            </div>
        </div>
    </main>
    <footer>
        <?php require '../squelette/footer.php'; ?>
    </footer>
</body>

</html>