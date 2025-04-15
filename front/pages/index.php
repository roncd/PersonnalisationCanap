<?php
require '../../admin/config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link rel="stylesheet" href="../../styles/styles.css">
    <link href="../../dist/output.css" rel="stylesheet">
    <script src="../../node_modules/@preline/carousel/index.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Baloo+2:wght@400..800&family=Be+Vietnam+Pro:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap');

        html {
            font-family: 'Baloo', sans-serif;
        }
    </style>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap');

        html {
            font-family: 'Be Vietnam Pro', sans-serif;
        }
    </style>
</head>

<body class="be-vietnam-pro-regular">
    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>
    <!--Main-->
    <main>
        <div class="container pt-24 md:pt-28 mx-auto flex flex-wrap flex-col md:flex-row items-center">
            <!--Landing page-->
            <div class="md:flex w-full mb-10">
                <!--Left Col-->
                <div class="w-full xl:w-3/5 p-12 overflow-hidden">

                    <h1 class="baloo-2-bold my-4  md:text-5xl leading-tight text-center md:text-left mb-8">
                        Personalise ton salon marocain
                    </h1>
                    <div>
                        <p>
                            Laissez vous tenter et personalisez votre salon de A à Z ! <br><br>
                            Du canapé à la table, choisissez les configurations qui vous plaise le plus.<br>
                            La couleur, le tissus, la forme vous pouvez en faire ce que vous voulez pour un prix resonable.<br>
                        </p>
                    </div>
                    <div class="w-full md:w-3/4 lg:w-2/3 xl:w-1/2 mt-10 rounded-lg text-center md:text-left">
                        <a href="dashboard.php">
                            <button
                                class="bg-black text-white be-vietnam-pro-bold py-3 px-7 rounded-[10px] shadow-lg shadow-gray-400 focus:ring transform transition hover:scale-105 duration-300 ease-in-out md:ml-auto md:mr-0"
                                type="button">
                                PERSONNALISER
                            </button>
                        </a>
                    </div>
                </div>

                <!--Right Col-->
                <div class="flex flex-col px-2 py-4 w-full xl:w-2/5 justify-center lg:items-start overflow-y-hidden">
                    <img class="rounded-[10px] w-16 md:w-48 lg:w-48" src="../../medias/canape.jpg" alt="Salon marocain" />
                </div>
            </div>


            <div class="container pt-10 md:pt-24 mx-auto flex flex-wrap flex-col md:flex-row items-center">
                <h1 class="baloo-2-bold my-4 text-3xl md:text-5xl leading-tight text-center md:text-left mb-8">
                    Inspire toi de nos salons marocains
                </h1>
            </div>
        </div>
        <!-- Slider  -->

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
                    <div class=" hs-carousel-body absolute top-0 bottom-0 start-0 flex flex-nowrap cursor-grab transition-transform duration-700 hs-carousel-dragging:transition-none hs-carousel-dragging:cursor-grabbing">

                        <!-- Premier slide -->
                        <div class="hs-carousel-slide flex-shrink-0 w-full lg:w-1/3 px-1">
                            <div class="flex justify-center mx-6">
                                <img class="rounded-[10px] transition duration-700" style="max-width: auto; height: auto;" src="../../medias/canape.jpg">
                            </div>
                        </div>

                        <!--  Deuxième slide -->
                        <div class="hs-carousel-slide flex-shrink-0 w-full lg:w-1/3 px-1">
                            <div class="flex justify-center mx-6">
                                <img class="rounded-[10px] transition duration-700" style="max-width: auto; height: auto;" src="../../medias/CanapéKénitra_VueDeAngle_SansTable-Photoroom.png">
                            </div>
                        </div>

                        <!-- Troisième slide -->
                        <div class="hs-carousel-slide flex-shrink-0 w-full lg:w-1/3 px-1">
                            <div class="flex justify-center mx-6">
                                <img class="rounded-[10px] transition duration-700" style="max-width: auto; height: auto;" src="../../medias/CanapéMeknès_VueDeAngle_-Photoroom.png">
                            </div>
                        </div>

                        <!-- Quatrième slide -->
                        <div class="hs-carousel-slide flex-shrink-0 w-full lg:w-1/3 px-1">
                            <div class="flex justify-center mx-6">
                                <img class="rounded-[10px] transition duration-700" style="max-width: auto; height: auto;" src="../../medias/canape.jpg">
                            </div>
                        </div>

                        <!-- Cinquième slide -->
                        <div class="hs-carousel-slide flex-shrink-0 w-full lg:w-1/3 px-1">
                            <div class="flex justify-center mx-6">
                                <img class="rounded-[10px] transition duration-700" style="max-width: auto; height: auto;" src="../../medias/CanapéKénitra_VueDeAngle_SansTable-Photoroom.png">
                            </div>
                        </div>

                        <!--  Sixième slide -->
                        <div class="hs-carousel-slide flex-shrink-0 w-full lg:w-1/3 px-1">
                            <div class="flex justify-center mx-6">
                                <img class="rounded-[10px] transition duration-700" style="max-width: auto; height: auto;" src="../../medias/CanapéMeknès_VueDeAngle_-Photoroom.png">
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <button type="button" class="hs-carousel-prev hs-carousel-disabled:opacity-70 hs-carousel-disabled:pointer-events-none absolute inset-y-0 start-0 inline-flex justify-center items-center w-[46px] h-full text-gray-800 hover:bg-gray-800/10 focus:outline-none focus:bg-gray-800/10 rounded-s-lg">
                <span class="text-2xl" aria-hidden="true">
                    <svg fill="#000000" xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 52 52" enable-background="new 0 0 52 52" xml:space="preserve" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" transform="matrix(-1.8369701987210297e-16,-1,1,-1.8369701987210297e-16,0,0)">
                        <path d="M43.7,38H8.3c-1,0-1.7-1.3-0.9-2.2l17.3-21.2c0.6-0.8,1.9-0.8,2.5,0l17.5,21.2C45.4,36.7,44.8,38,43.7,38z"></path>
                    </svg>
                </span>
                <span class="sr-only">Previous</span>
            </button>
            <button type="button" class="hs-carousel-next hs-carousel-disabled:opacity-70 hs-carousel-disabled:pointer-events-none absolute inset-y-0 end-0 inline-flex justify-center items-center w-[46px] h-full text-gray-800 hover:bg-gray-800/10 focus:outline-none focus:bg-gray-800/10 rounded-e-lg">
                <span class="sr-only">Next</span>
                <span class="text-2xl" aria-hidden="true">
                    <svg fill="#000000" xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 52 52" enable-background="new 0 0 52 52" xml:space="preserve" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" transform="matrix(6.123233995736766e-17,1,-1,6.123233995736766e-17,0,0)">
                        <path class="shrink-0 size-5" d="M43.7,38H8.3c-1,0-1.7-1.3-0.9-2.2l17.3-21.2c0.6-0.8,1.9-0.8,2.5,0l17.5,21.2C45.4,36.7,44.8,38,43.7,38z"></path>
                    </svg>
                </span>
            </button>


        </div>
        <!-- End Slider -->

        <div class="container mx-auto flex flex-wrap flex-col md:flex-row items-center">
            <div class="container pt-10 md:pt-24 mx-auto flex flex-wrap flex-col md:flex-row items-center">
                <h1 class="baloo-2-bold my-4 text-3xl md:text-5xl leading-tight text-center md:text-left mb-8">
                    En détails
                </h1>
            </div>
        </div>


        <!-- Slider  -->

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
                    <div class=" hs-carousel-body absolute top-0 bottom-0 start-0 flex flex-nowrap cursor-grab transition-transform duration-700 hs-carousel-dragging:transition-none hs-carousel-dragging:cursor-grabbing">

                        <!-- Premier slide -->
                        <div class="hs-carousel-slide flex-shrink-0 w-full lg:w-1/3 px-1">
                            <div class="flex justify-center mx-6">
                                <img class="rounded-[10px] transition duration-700" style="max-width: auto; height: auto;" src="../../medias/canape.jpg">
                            </div>
                        </div>

                        <!--  Deuxième slide -->
                        <div class="hs-carousel-slide flex-shrink-0 w-full lg:w-1/3 px-1">
                            <div class="flex justify-center mx-6">
                                <img class="rounded-[10px] transition duration-700" style="max-width: auto; height: auto;" src="../../medias/CanapéKénitra_VueDeAngle_SansTable-Photoroom.png">
                            </div>
                        </div>

                        <!-- Troisième slide -->
                        <div class="hs-carousel-slide flex-shrink-0 w-full lg:w-1/3 px-1">
                            <div class="flex justify-center mx-6">
                                <img class="rounded-[10px] transition duration-700" style="max-width: auto; height: auto;" src="../../medias/CanapéMeknès_VueDeAngle_-Photoroom.png">
                            </div>
                        </div>

                        <!-- Quatrième slide -->
                        <div class="hs-carousel-slide flex-shrink-0 w-full lg:w-1/3 px-1">
                            <div class="flex justify-center mx-6">
                                <img class="rounded-[10px] transition duration-700" style="max-width: auto; height: auto;" src="../../medias/canape.jpg">
                            </div>
                        </div>

                        <!-- Cinquième slide -->
                        <div class="hs-carousel-slide flex-shrink-0 w-full lg:w-1/3 px-1">
                            <div class="flex justify-center mx-6">
                                <img class="rounded-[10px] transition duration-700" style="max-width: auto; height: auto;" src="../../medias/CanapéKénitra_VueDeAngle_SansTable-Photoroom.png">
                            </div>
                        </div>

                        <!--  Sixième slide -->
                        <div class="hs-carousel-slide flex-shrink-0 w-full lg:w-1/3 px-1">
                            <div class="flex justify-center mx-6">
                                <img class="rounded-[10px] transition duration-700" style="max-width: auto; height: auto;" src="../../medias/CanapéMeknès_VueDeAngle_-Photoroom.png">
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <button type="button" class="hs-carousel-prev hs-carousel-disabled:opacity-70 hs-carousel-disabled:pointer-events-none absolute inset-y-0 start-0 inline-flex justify-center items-center w-[46px] h-full text-gray-800 hover:bg-gray-800/10 focus:outline-none focus:bg-gray-800/10 rounded-s-lg">
                <span class="text-2xl" aria-hidden="true">
                    <svg fill="#000000" xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 52 52" enable-background="new 0 0 52 52" xml:space="preserve" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" transform="matrix(-1.8369701987210297e-16,-1,1,-1.8369701987210297e-16,0,0)">
                        <path d="M43.7,38H8.3c-1,0-1.7-1.3-0.9-2.2l17.3-21.2c0.6-0.8,1.9-0.8,2.5,0l17.5,21.2C45.4,36.7,44.8,38,43.7,38z"></path>
                    </svg>
                </span>
                <span class="sr-only">Previous</span>
            </button>
            <button type="button" class="hs-carousel-next hs-carousel-disabled:opacity-70 hs-carousel-disabled:pointer-events-none absolute inset-y-0 end-0 inline-flex justify-center items-center w-[46px] h-full text-gray-800 hover:bg-gray-800/10 focus:outline-none focus:bg-gray-800/10 rounded-e-lg">
                <span class="sr-only">Next</span>
                <span class="text-2xl" aria-hidden="true">
                    <svg fill="#000000" xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 52 52" enable-background="new 0 0 52 52" xml:space="preserve" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" transform="matrix(6.123233995736766e-17,1,-1,6.123233995736766e-17,0,0)">
                        <path class="shrink-0 size-5" d="M43.7,38H8.3c-1,0-1.7-1.3-0.9-2.2l17.3-21.2c0.6-0.8,1.9-0.8,2.5,0l17.5,21.2C45.4,36.7,44.8,38,43.7,38z"></path>
                    </svg>
                </span>
            </button>
        </div>
        <!-- End Slider -->
    </main>
    <?php require_once '../../squelette/footer.php' ?>
</body>

</html>