<?php
require '../../admin/config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentions Légales</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link rel="stylesheet" href="../../styles/mentions.css">
</head>

<body>
    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>
    
    <!-- Main -->
    <main>
        <div class="title-bar">
            <h1 class="baloo-2-bold">Mentions Légales</h1>
        </div>

        <div class="container">
            <section class="section">
                <h2 class="baloo-2-regular">Éditeur du site :</h2>
                <ul>
                    <li>Déco du Monde - société privée</li>
                    <li>Numéro de téléphone : 01 48 22 98 05</li>
                    <li>E-mail : decorient@gmail.com</li>
                    <li>Adresse : 77 Av. Lenine, 93380 Pierrefitte-Sur-Seine</li>
                    <li>TVA : FR73393972666</li>
                    <li>Numéro de SIRET : 393 972 666 000 12</li>
                    <li>RCS de Paritex : Bobigny B 393 972 666</li>
                    <li>Adresse du siège social : 115 rue Etienne Dolet, 93380, Pierrefitte-sur-Seine</li>
                </ul>
            </section>

            <section class="section">
                <h2 class="baloo-2-regular">Ce site est hébergé par :</h2>
                <ul>
                    <li>PrestaShop SA</li>
                    <li>RCS : Paris B 497 916 635</li>
                    <li>Siège social : 198 avenue de France - 75013 Paris</li>
                </ul>
            </section>

            <section class="section">
                <h2 class="baloo-2-regular">1. Termes et conditions régissant l'accès à ce site :</h2>
                <p>
                    L'accès des visiteurs et des utilisateurs à ce site présuppose le complet accord de ceux-ci aux termes
                    et conditions régissant ce site. Déco du monde se réserve le droit, à sa propre discrétion, de modifier
                    sans préavis les termes et conditions régissant ce site. En cas de non-respect des termes et conditions
                    régissant l'accès à ce site, Déco du monde se réserve le droit d'appliquer toutes les mesures
                    nécessaires, par la force de la loi. Ses termes et conditions s'appliquent immédiatement et ne sont pas
                    limités dans le temps, à tous les visiteurs.
                </p>
            </section>

            <section class="section">
                <h2 class="baloo-2-regular">2. Cookies :</h2>
                <p>
                    Un cookie est un petit fichier texte enregistré sur votre appareil lors de votre visite. Il ne s’agit ni
                    d’un programme, ni d’un virus. Il contient des données comme un identifiant ou des informations sur
                    votre navigation, sans accès à vos fichiers personnels ni aux cookies d’autres sites.
                    Le site Déco du Monde utilise des cookies pour analyser le trafic et améliorer la navigation. Ces
                    données ne sont pas liées à vos informations personnelles et ne sont jamais revendues à des tiers.
                    Vous pouvez refuser les cookies ou être averti avant leur installation via les paramètres de votre
                    navigateur.
                </p>
            </section>

            <section class="section">
                <h2 class="baloo-2-regular">3. Responsabilité et limitations :</h2>
                <p>
                    Déco du Monde, ainsi que toute personne ayant participé à la création et au fonctionnement de ce site,
                    ne pourra être tenue responsable de tout dommage, direct ou indirect, pouvant résulter de l'accès, de
                    l'utilisation ou de l'impossibilité d'accéder au site.

                    De même, Déco du Monde ne saurait être tenue responsable d’éventuels dommages ou virus susceptibles
                    d’affecter votre équipement informatique à la suite d’une navigation ou d’un téléchargement depuis le
                    site.
                </p>
            </section>

            <section class="section">
                <h2 class="baloo-2-regular">4. Propriété intellectuelle :</h2>
                <p>
                    L’accès au site Déco du Monde vous accorde un droit d’usage personnel, privé et non exclusif. Tous les
                    contenus présents sur le site (textes, photos, infographies, logos, marques, etc.) sont protégés par le
                    Code de la Propriété Intellectuelle et sont considérés comme des œuvres originales.

                    Toute reproduction, représentation ou exploitation, totale ou partielle, sans l’autorisation préalable
                    des auteurs ou ayants droit, est strictement interdite. Les éléments du site ne peuvent en aucun cas être
                    vendus ou utilisés à des fins commerciales sans autorisation.
                </p>
            </section>
        </div>
    </main>

    <?php require_once '../../squelette/footer.php'; ?>
</body>

</html>