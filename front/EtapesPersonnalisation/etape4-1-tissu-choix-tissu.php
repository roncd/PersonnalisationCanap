<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../formulaire/Connexion.php");
    exit;
}

// Récupérer les tissus disponibles depuis la base de données
$stmt = $pdo->query("SELECT * FROM couleur_tissu");
$couleur_tissu = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['couleur_tissu_id']) || empty($_POST['couleur_tissu_id'])) {
        echo "Erreur : Aucun tissu sélectionné.";
        exit;
    }

    $id_client = $_SESSION['user_id'];
    $id_couleur_tissu = $_POST['couleur_tissu_id'];

    // Vérifier si une commande temporaire existe déjà pour cet utilisateur
    $stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ?");
    $stmt->execute([$id_client]);
    $existing_order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_order) {
        $stmt = $pdo->prepare("UPDATE commande_temporaire SET id_couleur_tissu = ? WHERE id_client = ?");
        $stmt->execute([$id_couleur_tissu, $id_client]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO commande_temporaire (id_client, id_couleur_tissu) VALUES (?, ?)");
        $stmt->execute([$id_client, $id_couleur_tissu]);
    }

    // Rediriger vers l'étape suivante
    header("Location: etape4-2-tissu-choix-tissu-coussin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../../medias/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../styles/processus.css">
    <link rel="stylesheet" href="../../styles/popup.css">
    <script type="module" src="../../scrpit/popup.js"></script>
    <script type="module" src="../../scrpit/button.js"></script>
    <title>Étape 4 - Choisi ton tissu</title>
    <style>
        /* Transition pour les éléments de la page */
        .transition {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }

        .transition.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Appliquer les transitions aux images sélectionnées */
        .option img.selected {
            border: 3px solid #997765;
            /* Couleur marron */
            border-radius: 5px;
            box-sizing: border-box;
        }
    </style>
</head>

<body data-user-id="<?php echo $_SESSION['user_id']; ?>">

    <header>
        <?php require '../../squelette/header.php'; ?>
    </header>

    <main>
        <div class="fil-ariane-container" aria-label="fil-ariane">
            <ul class="fil-ariane">
                <li><a href="etape1-1-structure.php">Structure</a></li>
                <li><a href="etape1-2-dimension.php">Dimension</a></li>
                <li><a href="etape2-type-banquette.php">Banquette</a></li>
                <li><a href="etape3-tissu-modele-banquette.php">Modèle</a></li>
                <li><a href="etape4-1-tissu-choix-tissu.php" class="active">Tissu</a></li>
                <li><a href="etape5-tissu-choix-dossier.php">Dossier</a></li>
                <li><a href="etape6-2-tissu.php">Accoudoir</a></li>
                <li><a href="etape7-tissu-choix-mousse.php">Mousse</a></li>
            </ul>
        </div>

        <div class="container">
            <!-- Colonne de gauche -->
            <div class="left-column transition">
                <h2>Étape 4 - Choisi ton tissu</h2>
                <section class="color-options">
                    <?php foreach ($couleur_tissu as $tissu): ?>
                        <div class="option transition">
                            <img src="../../admin/uploads/couleur-tissu-tissu/<?php echo htmlspecialchars($tissu['img']); ?>"
                                alt="<?php echo htmlspecialchars($tissu['nom']); ?>"
                                data-tissu-id="<?php echo $tissu['id']; ?>"
                                data-tissu-prix="<?php echo number_format($tissu['prix'], 2, '.', ''); ?>">
                            <p><?php echo htmlspecialchars($tissu['nom']); ?></p>
                            <p><strong><?php echo number_format($tissu['prix'], 2, '.', ''); ?> €</strong></p>
                        </div>
                    <?php endforeach; ?>
                </section>
                <div class="footer">
                    <p>Total : <span>899 €</span></p>

                    <div class="buttons">
                        <button class="btn-retour transition" onclick="history.go(-1)">Retour</button>
                        <form method="POST" action="">
                            <input type="hidden" name="couleur_tissu_id" id="selected-couleur_tissu">
                            <button type="submit" class="btn-suivant transition">Suivant</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Colonne de droite -->
            <div class="right-column transition">
                <section class="main-display">
                    <div class="buttons transition">
                        <button class="btn-aide">Besoin d'aide ?</button>
                        <button class="btn-abandonner">Abandonner</button>
                    </div>
                    <img src="../../medias/process-main-image.png" alt="Armoire" class="transition">
                </section>
            </div>
        </div>

        <!-- Popup besoin d'aide -->
        <div id="help-popup" class="popup transition">
            <div class="popup-content">
                <h2>Vous avez une question ?</h2>
                <p>Contactez nous au numéro suivant et un vendeur vous assistera :
                    <br><br>
                    <strong>06 58 47 58 56</strong>
                </p>
                <br>
                <button class="close-btn">Merci !</button>
            </div>
        </div>

        <!-- Popup abandonner -->
        <div id="abandonner-popup" class="popup transition">
            <div class="popup-content">
                <h2>Êtes vous sûr de vouloir abandonner ?</h2>
                <br>
                <button class="yes-btn">Oui ...</button>
                <button class="no-btn">Non !</button>
            </div>
        </div>

        <!-- Pop-up de sélection d'option -->
        <div id="selection-popup" class="popup transition">
            <div class="popup-content">
                <h2>Veuillez choisir une option avant de continuer.</h2>
                <br>
                <button class="close-btn">OK</button>
            </div>
        </div>

        <!-- GESTION DES SELECTIONS -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {

                // Sélectionner les éléments nécessaires
                const options = document.querySelectorAll('.color-options .option img'); // Les images des options
                const selectedCouleurTissuInput = document.getElementById('selected-couleur_tissu');
                const mainImage = document.querySelector('.main-display img');
                const selectionPopup = document.getElementById('selection-popup'); // Pop-up de sélection
                let selected = false; // Marque si une option a été sélectionnée

                // Vérification si une sélection existe dans localStorage
                let savedCouleurTissuId = localStorage.getItem('selectedCouleurTissuId');

                if (savedCouleurTissuId) {
                    options.forEach(img => {
                        if (img.getAttribute('data-tissu-id') === savedCouleurTissuId) {
                            img.classList.add('selected');
                            mainImage.src = img.src;
                            mainImage.alt = img.alt;
                            selectedCouleurTissuInput.value = savedCouleurTissuId;
                            selected = true;
                        }
                    });
                }

                // Appliquer les transitions aux éléments
                document.querySelectorAll('.transition').forEach(element => {
                    element.classList.add('show');
                });

                options.forEach(img => {
                    img.addEventListener('click', () => {
                        options.forEach(opt => opt.classList.remove('selected'));
                        img.classList.add('selected');
                        selectedCouleurTissuInput.value = img.getAttribute('data-tissu-id');

                        // Mise à jour de l'image principale
                        mainImage.src = img.src;
                        mainImage.alt = img.alt;

                        selected = true;
                        saveSelection(img.getAttribute('data-tissu-id'));
                    });
                });

                suivantButton.addEventListener('click', (event) => {
                    if (!selected) {
                        event.preventDefault();
                        selectionPopup.style.display = 'flex';
                    }
                });

                document.querySelector('#selection-popup .close-btn').addEventListener('click', () => {
                    selectionPopup.style.display = 'none';
                });

                // Option supplémentaire : fermer le pop-up de sélection si clic à l'extérieur
                window.addEventListener('click', (event) => {
                    if (event.target === selectionPopup) {
                        selectionPopup.style.display = 'none';
                    }
                });

                function saveSelection(couleurTissuId) {
                    localStorage.setItem('selectedCouleurTissuId', couleurTissuId);
                }
            });
        </script>


        <!-- VARIATION DES PRIX  -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                let totalPrice = 0; // Total global

                // Identifier l'étape actuelle
                const currentStep = "4-1tissu";
                const userId = document.body.getAttribute('data-user-id');

                if (!userId) {
                    console.error("ID utilisateur non trouvé.");
                    return;
                }

                const sessionKey = `allSelectedOptions_${userId}`;
                let allSelectedOptions = JSON.parse(sessionStorage.getItem(sessionKey)) || [];

                // Fonction pour mettre à jour le total global
                function updateTotal() {
                    totalPrice = allSelectedOptions.reduce((sum, option) => {
                        const price = option.price || 0;
                        const quantity = option.quantity || 1;
                        return sum + (price * quantity);
                    }, 0);

                    const totalElement = document.querySelector(".footer p span");
                    if (totalElement) {
                        totalElement.textContent = `${totalPrice.toFixed(2)} €`;
                    }
                }

                // Gestion des clics sur les options
                document.querySelectorAll('.color-options .option img').forEach(option => {
                    const optionId = option.getAttribute('data-tissu-id');
                    const price = parseFloat(option.getAttribute('data-tissu-prix')) || 0;

                    if (!optionId || isNaN(price)) {
                        console.warn(`Attributs invalides : data-tissu-id=${optionId}, data-tissu-prix=${price}`);
                        return;
                    }

                    const uniqueId = `${currentStep}_${optionId}`;

                    if (allSelectedOptions.some(opt => opt.id === uniqueId)) {
                        option.parentElement.classList.add('selected');
                    }

                    option.addEventListener('click', () => {
                        document.querySelectorAll('.color-options .option img').forEach(opt => {
                            opt.parentElement.classList.remove('selected');
                        });

                        allSelectedOptions = allSelectedOptions.filter(opt => !opt.id.startsWith(`${currentStep}_`));

                        allSelectedOptions.push({
                            id: uniqueId,
                            price: price
                        });
                        option.parentElement.classList.add('selected');

                        sessionStorage.setItem(sessionKey, JSON.stringify(allSelectedOptions));
                        updateTotal();
                    });
                });

                updateTotal();
            });
        </script>

        <!-- GESTION DES SELECTIONS -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {

                // Sélectionner les éléments nécessaires
                const options = document.querySelectorAll('.color-options .option img'); // Les images des options
                const selectedCouleurTissuInput = document.getElementById('selected-couleur_tissu');
                const mainImage = document.querySelector('.main-display img');
                const selectionPopup = document.getElementById('selection-popup'); // Pop-up de sélection
                let selected = false; // Marque si une option a été sélectionnée

                // Vérification si une sélection existe dans localStorage
                let savedCouleurTissuId = localStorage.getItem('selectedCouleurTissuId');

                if (savedCouleurTissuId) {
                    options.forEach(img => {
                        if (img.getAttribute('data-tissu-id') === savedCouleurTissuId) {
                            img.classList.add('selected');
                            mainImage.src = img.src;
                            mainImage.alt = img.alt;
                            selectedCouleurTissuInput.value = savedCouleurTissuId;
                            selected = true;
                        }
                    });
                }

                // Appliquer les transitions aux éléments
                document.querySelectorAll('.transition').forEach(element => {
                    element.classList.add('show');
                });

                options.forEach(img => {
                    img.addEventListener('click', () => {
                        options.forEach(opt => opt.classList.remove('selected'));
                        img.classList.add('selected');
                        selectedCouleurTissuInput.value = img.getAttribute('data-tissu-id');

                        // Mise à jour de l'image principale
                        mainImage.src = img.src;
                        mainImage.alt = img.alt;

                        selected = true;
                        saveSelection(img.getAttribute('data-tissu-id'));
                    });
                });

                suivantButton.addEventListener('click', (event) => {
                    if (!selected) {
                        event.preventDefault();
                        selectionPopup.style.display = 'flex';
                    }
                });

                document.querySelector('#selection-popup .close-btn').addEventListener('click', () => {
                    selectionPopup.style.display = 'none';
                });

                // Option supplémentaire : fermer le pop-up de sélection si clic à l'extérieur
                window.addEventListener('click', (event) => {
                    if (event.target === selectionPopup) {
                        selectionPopup.style.display = 'none';
                    }
                });

                function saveSelection(couleurTissuId) {
                    localStorage.setItem('selectedCouleurTissuId', couleurTissuId);
                }
            });
        </script>


    </main>

    <?php require_once '../../squelette/footer.php' ?>

</body>

</html>