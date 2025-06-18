<?php
require '../../admin/config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Consultez notre politique de confidentialité pour comprendre comment Déco du Monde protège vos données personnelles.">
    <meta name="author" content="Déco du Monde">
    <title>Politique de confidentialité - Déco du Monde</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link rel="stylesheet" href="../../styles/mentions.css">
</head>

<body>
    <?php include '../cookies/index.html'; ?>
    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>

    <main>
        <div class="title-bar">
            <h1 class="baloo-2-bold">Politique de confidentialité</h1>
        </div>

        <div class="container-intro">
            <div class="intro">
                <p>
                    Chez <strong>Déco du Monde</strong>, la confidentialité de vos données est une priorité.
                    Que vous naviguiez sur notre site ou passiez commande, nous veillons à protéger vos informations
                    avec le plus grand soin.
                </p>
                <p>
                    Cette <strong>Politique de Confidentialité</strong> détaille les données que nous collectons,
                    leur utilisation et vos droits, conformément au <abbr title="Règlement Général sur la Protection des Données">RGPD</abbr>.
                    Elle s'applique à tous nos services pour garantir une transparence totale.
                </p>
            </div>
        </div>

        <div class="container">
            <section class="section">
                <h2 class="baloo-2-regular">1. Responsable du traitement</h2>
                <ul>
                    <li>Nom : ERFAD FETHI</li>
                    <li>Adresse : 77 Avenue Lénine, 93380 Pierrefitte-sur-Seine</li>
                    <li>E-mail : decorient@gmail.com</li>
                </ul>
            </section>

            <section class="section">
                <h2 class="baloo-2-regular">2. Données collectées</h2>
                <p>Nous collectons certaines données lorsque :</p>
                <ul>
                    <li>Vous créez un compte</li>
                    <li>Vous passez une commande</li>
                    <li>Vous remplissez un formulaire (nom, prénom, e-mail, téléphone)</li>
                    <li>Vous naviguez sur le site (pages visitées, adresse IP, etc.)</li>
                </ul>
            </section>

            <section class="section">
                <h2 class="baloo-2-regular">3. Utilisation des données</h2>
                <p>Les données sont utilisées pour :</p>
                <ul>
                    <li>Répondre à vos demandes</li>
                    <li>Gérer vos commandes</li>
                    <li>Analyser la fréquentation du site</li>
                    <li>Améliorer votre expérience</li>
                </ul>
            </section>

            <section class="section">
                <h2 class="baloo-2-regular">4. Vos droits</h2>
                <p>Conformément au <abbr title="Règlement Général sur la Protection des Données">RGPD</abbr>, vous disposez des droits suivants :</p>
                <ul>
                    <li>Accès à vos données</li>
                    <li>Rectification ou suppression</li>
                    <li>Opposition au traitement</li>
                    <li>Retrait du consentement</li>
                </ul>
                <p>Pour exercer vos droits : <a href="mailto:decorient@gmail.com">decorient@gmail.com</a></p>
            </section>

            <section class="section">
                <h2 class="baloo-2-regular">5. Partage des données</h2>
                <p>Vos données ne sont ni vendues, ni louées. Elles sont uniquement accessibles à l'équipe de Déco du
                    Monde et aux prestataires nécessaires à nos services (hébergeur sécurisé, etc.).</p>
            </section>

            <section class="section">
                <h2 class="baloo-2-regular">6. Sécurité des données</h2>
                <p>Nous mettons en place des mesures techniques et organisationnelles afin d'assurer la sécurité de vos
                    données (chiffrement, hébergement sécurisé...).</p>
            </section>

            <section class="section">
                <h2 class="baloo-2-regular">7. Cookies</h2>
                <p>Déco du Monde utilise des cookies pour améliorer votre expérience et analyser le trafic du site. Ces
                    fichiers sont anonymes et ne permettent pas de vous identifier. Vous pouvez les refuser ou les gérer
                    via les paramètres de votre navigateur.</p>
            </section>
        </div>
    </main>

    <?php require_once '../../squelette/footer.php'; ?>
</body>

</html>