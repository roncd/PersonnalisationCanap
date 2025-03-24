<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../formulaire/Connexion.php");
    exit;
}

// Récupérer les types de dossier tissu depuis la base de données
$stmt = $pdo->query("SELECT * FROM dossier_tissu");
$dossier_tissu = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['dossier_tissu_id']) || empty($_POST['dossier_tissu_id'])) {
        echo "Erreur : Aucun dossier sélectionné.";
        exit;
    }

    $id_client = $_SESSION['user_id'];
    $id_dossier_tissu = $_POST['dossier_tissu_id'];

    // Vérifier si une commande temporaire existe déjà pour cet utilisateur
    $stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ?");
    $stmt->execute([$id_client]);
    $existing_order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_order) {
        $stmt = $pdo->prepare("UPDATE commande_temporaire SET id_dossier_tissu = ? WHERE id_client = ?");
        $stmt->execute([$id_dossier_tissu, $id_client]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO commande_temporaire (id_client, id_dossier_tissu) VALUES (?, ?)");
        $stmt->execute([$id_client, $id_dossier_tissu]);
    }

    // Rediriger vers l'étape suivante
    header("Location: etape6-2-tissu.php");
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
    <title>Étape 5 - Choisi ton dossier</title>
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
            border: 3px solid #997765; /* Couleur marron */
            border-radius: 5px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>

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
            <li><a href="etape4-1-tissu-choix-tissu.php">Tissu</a></li>
            <li><a href="etape5-tissu-choix-dossier.php" class="active">Dossier</a></li>
            <li><a href="etape6-2-tissu.php">Accoudoir</a></li>
            <li><a href="etape7-tissu-choix-mousse.php">Mousse</a></li>
        </ul>
    </div>

    <div class="container">
        <!-- Colonne de gauche -->
        <div class="left-column transition">
            <h2>Étape 5 - Choisi ton dossier</h2>
            <section class="color-2options">
                <?php if (!empty($dossier_tissu)): ?>
                    <?php foreach ($dossier_tissu as $tissu): ?>
                        <div class="option transition">
                            <img src="../../admin/uploads/dossier-tissu/<?php echo htmlspecialchars($tissu['img']); ?>" 
                                 alt="<?php echo htmlspecialchars($tissu['nom']); ?>" 
                                 data-dossier-id="<?php echo $tissu['id']; ?>" 
                                 data-dossier-prix="<?php echo $tissu['prix']; ?>"> 
                            <p><?php echo htmlspecialchars($tissu['nom']); ?></p>
                            <p><strong><?php echo htmlspecialchars($tissu['prix']); ?> €</strong></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucun dossier disponible pour le moment.</p>
                <?php endif; ?>               
            </section>

            <div class="footer">
            <div id="totalPrice">Total: <span> 0€</span></div>
                <div class="buttons">
                    <button class="btn-retour transition" onclick="history.go(-1)">Retour</button>
                                        <form method="POST" action="">
                        <input type="hidden" name="dossier_tissu_id" id="selected-dossier_tissu">
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
            <strong>06 58 47 58 56</strong></p>
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
    let totalPrice = 0; // Total global pour toutes les étapes

    // Identifier l'étape actuelle (par exemple, "5-dossiers")
    const currentStep = "5-dossiers"; // Changez cela pour chaque étape (par exemple "4-2", "5-dossiers", etc.)

    // Charger toutes les options sélectionnées (globalement) depuis localStorage
    let allSelectedOptions = JSON.parse(localStorage.getItem("allSelectedOptions")) || [];
    console.log("Données globales récupérées depuis localStorage :", allSelectedOptions);

    // Vérifier si `allSelectedOptions` est un tableau
    if (!Array.isArray(allSelectedOptions)) {
        allSelectedOptions = [];
        console.warn("allSelectedOptions n'était pas un tableau. Réinitialisé à []");
    }

    // Fonction pour mettre à jour le total global
    function updateTotal() {
        totalPrice = allSelectedOptions.reduce((sum, option) => sum + option.price, 0);
        console.log("Total global mis à jour :", totalPrice);

        // Mettre à jour le total dans l'interface
        const totalElement = document.getElementById("totalPrice");
        if (totalElement) {
            totalElement.textContent = `Total: ${totalPrice.toFixed(2)} €`;
        } else {
            console.error("L'élément '#totalPrice' est introuvable !");
        }
    }

    // Gérer les sélections d'options pour cette étape
    document.querySelectorAll('.color-2options .option img').forEach(option => {
        let optionId = option.getAttribute('data-dossier-id');
        let price = parseFloat(option.getAttribute('data-dossier-prix')) || 0;

        // Vérifiez si les attributs sont valides
        if (!optionId || isNaN(price)) {
            console.warn(`Attributs manquants ou invalides pour une option : data-dossier-id=${optionId}, data-dossier-prix=${price}`);
            return; // Ignorer cette option si les attributs ne sont pas valides
        }

        // Créer un identifiant unique basé sur l'étape actuelle
        let uniqueId = `${currentStep}_${optionId}`;

        console.log(`Option détectée : ID Unique = ${uniqueId}, Prix = ${price}`);

        // Vérifier si l'option est déjà sélectionnée (dans toutes les étapes)
        if (allSelectedOptions.some(opt => opt.id === uniqueId)) {
            option.parentElement.classList.add('selected');
        }

        // Gérer les clics sur les options
        option.addEventListener('click', () => {
            // Supprimer toutes les sélections existantes dans l'étape actuelle
            document.querySelectorAll('.color-2options .option img').forEach(opt => {
                opt.parentElement.classList.remove('selected'); // Retirer la classe CSS
            });

            // Supprimer les options de cette étape dans le stockage global
            allSelectedOptions = allSelectedOptions.filter(opt => !opt.id.startsWith(`${currentStep}_`));

            // Ajouter l'option actuellement sélectionnée
            allSelectedOptions.push({ id: uniqueId, price: price });
            option.parentElement.classList.add('selected'); // Ajouter la classe CSS

            console.log(`Option sélectionnée : ID Unique = ${uniqueId}, Prix = ${price}`);

            // Sauvegarder les données globales dans localStorage
            localStorage.setItem("allSelectedOptions", JSON.stringify(allSelectedOptions));

            // Mettre à jour le total
            updateTotal();
        });
    });

    // Initialiser le total dès le chargement de la page
    updateTotal();
});

    </script>
    
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const options = document.querySelectorAll('.color-2options .option img'); // Sélectionne toutes les images
        const mainImage = document.querySelector('.main-display img');
        const suivantButton = document.querySelector('.btn-suivant');
        const helpPopup = document.getElementById('help-popup'); // Popup besoin d'aide
        const abandonnerPopup = document.getElementById('abandonner-popup'); // Popup abandonner
        const selectionPopup = document.getElementById('selection-popup'); // Popup de sélection
        const selectedDossierTissuInput = document.getElementById('selected-dossier_tissu'); // Input caché
        let selected = false; // Variable pour savoir si une option est sélectionnée

        // Vérification si une sélection existe dans localStorage
        let savedDossierTissuId = localStorage.getItem('selectedDossierTissuId');
        
        if (savedDossierTissuId) {
            options.forEach(img => {
                if (img.getAttribute('data-dossier-id') === savedDossierTissuId) {
                    img.classList.add('selected');
                    mainImage.src = img.src;
                    mainImage.alt = img.alt;
                    selectedDossierTissuInput.value = savedDossierTissuId;
                    selected = true;
                }
            });
        }

        // Affichage des éléments avec la classe "transition"
        document.querySelectorAll('.transition').forEach(element => {
            element.classList.add('show');
        });

        // Gestion de la sélection des images
        options.forEach(img => {
            img.addEventListener('click', () => {
                // Retirer la classe "selected" de toutes les images
                options.forEach(opt => opt.classList.remove('selected'));

                // Ajouter la classe "selected" à l'image cliquée
                img.classList.add('selected');

                // Mettre à jour l'image principale
                mainImage.src = img.src;
                mainImage.alt = img.alt;

                // Mettre à jour l'input caché avec l'ID du tissu sélectionné
                selectedDossierTissuInput.value = img.getAttribute('data-dossier-id');
                selected = true; // Marquer comme sélectionné

                saveSelection(img.getAttribute('data-dossier-id'));
            });
        });

        // Action sur le bouton "Suivant"
        suivantButton.addEventListener('click', (event) => {
            if (!selected) {
                // Si aucune option n'est sélectionnée, afficher le popup
                event.preventDefault();
                selectionPopup.style.display = 'flex';
            }
        });

        // Fermeture du popup de sélection
        document.querySelector('#selection-popup .close-btn').addEventListener('click', () => {
            selectionPopup.style.display = 'none';
        });

        // Fermer le popup de sélection si clic à l'extérieur
        window.addEventListener('click', (event) => {
            if (event.target === selectionPopup) {
                selectionPopup.style.display = 'none';
            }
        });

        // Gestion du popup "Besoin d'aide"
        document.querySelector('.btn-aide').addEventListener('click', () => {
            helpPopup.style.display = 'flex';
        });

        document.querySelector('.close-btn').addEventListener('click', () => {
            helpPopup.style.display = 'none';
        });

        // Gestion du popup "Abandonner"
        document.querySelector('.btn-abandonner').addEventListener('click', () => {
            abandonnerPopup.style.display = 'flex';
        });

        document.querySelector('.no-btn').addEventListener('click', () => {
            abandonnerPopup.style.display = 'none';
        });

        document.querySelector('.yes-btn').addEventListener('click', () => {
            window.location.href = 'index.php'; // Redirection vers la page d'accueil
        });

        function saveSelection(dossierTissuId) {
            localStorage.setItem('selectedDossierTissuId', dossierTissuId);
        }
    });
</script>


</main>

<?php require_once '../../squelette/footer.php'?>

</body>
</html>
