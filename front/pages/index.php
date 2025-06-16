<?php
require '../../admin/config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link rel="stylesheet" href="../../styles/styles.css">
    <link rel="stylesheet" href="../../styles/buttons.css">
    <script src="../../node_modules/@preline/carousel/index.js"></script>
</head>

<body>
    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>

    <!-- Main -->
    <main>
        <!-- Section avec image de fond et texte superposé -->
        <section class="hero-section">
            <div class="hero-container">
                <img src="../../medias/salon-marocain.jpg" alt="Salon marocain" class="hero-image">
                <div class="hero-content">
                    <h1 class="hero-title">
                        Personnalisez votre salon marocain
                    </h1>
                    <p class="hero-description">
                        Laissez-vous tenter et personnalisez votre salon de A à Z !<br>
                        Du canapé à la table, choisissez les configurations qui vous plaisent le plus.<br>
                        La couleur, le tissu, la forme... faites ce qui vous ressemble pour un prix raisonnable.
                    </p>
                    <a href="dashboard.php">
                        <button class="btn-noir">PERSONNALISER</button>
                    </a>
                </div>
            </div>
        </section>

        <!-- Section "Inspirez-vous de nos modèles" -->
        <div class="sections-wrapper">
            <div class="container pt-10 md:pt-24 mx-auto text-center">
                <h1 class="baloo-2-bold my-4 text-3xl md:text-5xl leading-tight mb-8">
                    Inspirez-vous de nos salons marocains
                </h1>
            </div>

            <!-- Slider -->
            <section class="carousel-section mb-16">
                <div data-hs-carousel='{
    "loadingClasses": "opacity-0",
    "dotsItemClasses": "hs-carousel-active:bg-blue-700 hs-carousel-active:border-blue-700 size-3 border border-gray-400 rounded-full cursor-pointer",
    "slidesQty": {
        "xs": 1,
        "sm": 2,
        "md": 2,
        "lg": 3
    },
    "isDraggable": false
}' class="relative">
                    <div class="hs-carousel w-full overflow-hidden bg-white rounded-lg">
                        <div class="relative min-h-72 -mx-1">
                            <div
                                class="hs-carousel-body absolute top-0 bottom-0 start-0 flex flex-nowrap transition-transform duration-700">

                                <!-- Premier slide -->
                                <div class="hs-carousel-slide flex-shrink-0 w-full lg:w-1/3 px-1">
                                    <div class="flex flex-col justify-center mx-6">
                                        <img class="rounded-[4px] transition duration-700"
                                            src="../../medias/model-salon.png" alt="Modèle de salon">
                                        <div class="text-center mt-4">
                                            <h3 class="baloo-2-bold text-lg">Salon Casablanca</h3>
                                            <p class="text-lg font-semibold mt-2">1800 €</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Deuxième slide -->
                                <div class="hs-carousel-slide flex-shrink-0 w-full lg:w-1/3 px-1">
                                    <div class="flex flex-col justify-center mx-6">
                                        <img class="rounded-[4px] transition duration-700"
                                            src="../../medias/model-salon.png" alt="Modèle de salon">
                                        <div class="text-center mt-4">
                                            <h3 class="baloo-2-bold text-lg">Salon Fès</h3>
                                            <p class="text-lg font-semibold mt-2">1600 €</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Troisième slide -->
                                <div class="hs-carousel-slide flex-shrink-0 w-full lg:w-1/3 px-1">
                                    <div class="flex flex-col justify-center mx-6">
                                        <img class="rounded-[4px] transition duration-700"
                                            src="../../medias/model-salon.png" alt="Modèle de salon">
                                        <div class="text-center mt-4">
                                            <h3 class="baloo-2-bold text-lg">Salon Tanger</h3>
                                            <p class="text-lg font-semibold mt-2">1400 €</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quatrième slide -->
                                <div class="hs-carousel-slide flex-shrink-0 w-full lg:w-1/3 px-1">
                                    <div class="flex flex-col justify-center mx-6">
                                        <img class="rounded-[4px] transition duration-700"
                                            src="../../medias/model-salon.png" alt="Modèle de salon">
                                        <div class="text-center mt-4">
                                            <h3 class="baloo-2-bold text-lg">Salon Rabat</h3>
                                            <p class="text-lg font-semibold mt-2">1500 €</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cinquième slide -->
                                <div class="hs-carousel-slide flex-shrink-0 w-full lg:w-1/3 px-1">
                                    <div class="flex flex-col justify-center mx-6">
                                        <img class="rounded-[4px] transition duration-700"
                                            src="../../medias/model-salon.png" alt="Modèle de salon">
                                        <div class="text-center mt-4">
                                            <h3 class="baloo-2-bold text-lg">Salon Marrakech</h3>
                                            <p class="text-lg font-semibold mt-2">1700 €</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Navigation buttons -->
                            <button type="button"
                                class="hs-carousel-prev hs-carousel-disabled:opacity-70 absolute start-0 top-[140px] bottom-[100px] inline-flex justify-center items-center w-[46px]">
                                <span class="text-2xl">‹</span>
                                <span class="sr-only">Previous</span>
                            </button>

                            <button type="button"
                                class="hs-carousel-next hs-carousel-disabled:opacity-70 absolute end-0 top-[140px] bottom-[100px] inline-flex justify-center items-center w-[46px]">
                                <span class="text-2xl">›</span>
                                <span class="sr-only">Next</span>
                            </button>

                        </div>
                    </div>
            </section>

            <!-- End Slider -->


            <!-- Section Qui sommes-nous -->
            <section class="about-section">
                <div class="about-container">
                    <div class="about-content">
                        <div class="about-text">
                            <h2 class="about-title">Qui sommes-nous ?</h2>
                            <p class="about-description">
                                Chez Déco du Monde, chaque salon marocain est pensé comme une œuvre unique,
                                façonnée selon vos envies, vos goûts et vos traditions. Du choix des tissus à la finition
                                des détails,
                                nous mettons notre passion et notre savoir-faire au service d'un mobilier qui vous
                                ressemble.
                            </p>
                            <p class="about-mission">
                                Notre mission : faire vivre l'artisanat marocain dans des intérieurs modernes et
                                chaleureux,
                                en alliant confort, élégance et culture.
                            </p>
                            <a href="apropos.php" class="about-button">
                                En savoir plus
                            </a>
                        </div>
                        <div class="about-image">
                            <img src="../../medias/salon-maroc.jpg" alt="Salon marocain" class="about-img">
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section Commencer un devis -->
            <section class="devis-section">
                <div class="devis-container">
                    <h2 class="devis-title">Commencer un devis :</h2>
                    <p class="devis-description">
                        Créez le salon marocain de vos rêves !<br>
                        En quelques clics, démarrez votre devis personnalisé et façonnez un intérieur qui vous
                        ressemble.
                    </p>
                    <a href="dashboard.php">
                        <button class="btn-noir">
                            Commencer mon devis
                        </button>
                    </a>
                </div>
            </section>
    </main>

    </script>

    <!-- Footer -->
    <footer>
        <?php require '../../squelette/footer.php'; ?>
    </footer>

</body>

</html>