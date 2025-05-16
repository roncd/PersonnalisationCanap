<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche Client</title>
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
            <h1>Fiche client</h1>

            <div class="client-grid">

                <!-- Bloc identité -->
                <section class="info-card block-identite">
                    <h2>NOM PRÉNOM [ID]</h2>
                    <p>Titre de civilité : <br>
                        Âge : X ans (date de naissance : )<br>
                        Date d'inscription : <br>
                        Langue : </p>
                </section>

                <!-- Bloc commandes -->
                <section class="info-card commandes-card block-commandes">
                    <h2>COMMANDES</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" style="height: 30px;"></td>
                            </tr>
                            <tr>
                                <td colspan="4" style="height: 30px;"></td>
                            </tr>
                            <tr>
                                <td colspan="4" style="height: 30px;"></td>
                            </tr>
                            <tr>
                                <td colspan="4" style="height: 30px;"></td>
                            </tr>
                            <tr>
                                <td colspan="4" style="height: 30px;"></td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <!-- Bloc contact -->
                <section class="info-card block-contact">
                    <h2>CONTACT</h2>
                    <p>Adresse mail :<br>
                        Téléphone :</p>
                </section>

                <!-- Bloc adresse -->
                <section class="info-card block-adresse">
                    <h2>ADRESSE</h2>
                    <p>Adresse :<br>
                        Code postale :<br>
                        Info sup :</p>
                </section>

            </div>
        </div>
    </main>
    <footer>
        <?php require '../squelette/footer.php'; ?>
    </footer>
</body>

</html>