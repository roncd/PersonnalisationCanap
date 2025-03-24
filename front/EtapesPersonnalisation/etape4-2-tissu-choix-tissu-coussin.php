<?php
require '../../admin/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../formulaire/Connexion.php");
    exit;
}

// Récupérer les tissus disponibles depuis la base de données
$stmt = $pdo->query("SELECT * FROM motif_tissu");
$motif_tissu = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['motif_tissu_id']) || empty($_POST['motif_tissu_id'])) {
        echo "Erreur : Aucun tissu sélectionné.";
        exit;
    }

    $id_client = $_SESSION['user_id'];
    $id_motif_tissu = $_POST['motif_tissu_id'];

    // Vérifier si une commande temporaire existe déjà pour cet utilisateur
    $stmt = $pdo->prepare("SELECT id FROM commande_temporaire WHERE id_client = ?");
    $stmt->execute([$id_client]);
    $existing_order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_order) {
        $stmt = $pdo->prepare("UPDATE commande_temporaire SET id_motif_tissu = ? WHERE id_client = ?");
        $stmt->execute([$id_motif_tissu, $id_client]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO commande_temporaire (id_client, id_motif_tissu) VALUES (?, ?)");
        $stmt->execute([$id_client, $id_motif_tissu]);
    }

    // Rediriger vers l'étape suivante
    header("Location: etape5-tissu-choix-dossier.php");
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
    <title>Étape 4 - Choisi ton tissu de coussin</title>
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

        .option.selected {
    border: 2px solid #4CAF50;
    box-shadow: 0 0 5px rgba(0, 255, 0, 0.8);
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
            <li><a href="etape4-1-tissu-choix-tissu.php" class="active">Tissu</a></li>
            <li><a href="etape5-tissu-choix-dossier.php">Dossier</a></li>
            <li><a href="etape6-2-tissu.php">Accoudoir</a></li>
            <li><a href="etape7-tissu-choix-mousse.php">Mousse</a></li>
        </ul>
    </div>

    <div class="container">
        <!-- Colonne de gauche -->
        <div class="left-column transition">
            <h2>Étape 4 - Choisi ton tissu de coussin</h2>
            <section class="color-options">
    <?php if (!empty($motif_tissu)): ?>
        <?php foreach ($motif_tissu as $tissu): ?>
            <div class="option transition">
                <img src="../../admin/uploads/motif-tissu/<?php echo htmlspecialchars($tissu['img']); ?>" 
                     alt="<?php echo htmlspecialchars($tissu['nom']); ?>" 
                     data-motif-id="<?php echo $tissu['id']; ?>" 
                     data-motif-prix="<?php echo number_format($tissu['prix'], 2, '.', ''); ?>"> 
                <p><?php echo htmlspecialchars($tissu['nom']); ?></p>
                <p><strong><?php echo number_format($tissu['prix'], 2, '.', ''); ?> €</strong></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun tissu de coussin disponible pour le moment.</p>
    <?php endif; ?>
</section>

            <div class="footer">
            <div id="totalPrice">Total: 0€</div>
                <div class="buttons">
                    <button class="btn-retour transition" onclick="history.go(-1)">Retour</button>
                    <form method="POST" action="">
                        <input type="hidden" name="motif_tissu_id" id="selected-motif_tissu">
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
            <p>Contactez-nous au numéro suivant et un vendeur vous assistera : 
                <br><br>
            <strong>06 58 47 58 56</strong></p>
            <br>
            <button class="close-btn">Merci !</button>
        </div>
    </div>

    <!-- Popup abandonner -->
    <div id="abandonner-popup" class="popup transition">
        <div class="popup-content">
            <h2>Êtes-vous sûr de vouloir abandonner ?</h2>
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

    // Identifier l'étape actuelle (par exemple, "4-2")
    const currentStep = "4-2"; // Changez cette valeur pour chaque étape (par exemple "4-3")

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
            totalElement.textContent = `Total: ${totalPrice.toFixed(2)}€`;
        } else {
            console.error("L'élément '#totalPrice' est introuvable !");
        }
    }

    // Charger les options spécifiques à cette étape depuis localStorage
    let selectedMotifOptions = JSON.parse(localStorage.getItem("selectedMotifOptions")) || [];
    console.log("Données récupérées depuis localStorage (Motifs spécifiques) :", selectedMotifOptions);

    // Vérifier si `selectedMotifOptions` est un tableau
    if (!Array.isArray(selectedMotifOptions)) {
        selectedMotifOptions = [];
        console.warn("selectedMotifOptions n'était pas un tableau. Réinitialisé à []");
    }

    // Gérer les sélections d'options pour cette étape
    document.querySelectorAll('.color-options .option img').forEach(option => {
        let optionId = option.getAttribute('data-motif-id');
        let price = parseFloat(option.getAttribute('data-motif-prix')) || 0;

        // Vérifiez si les attributs sont valides
        if (!optionId || isNaN(price)) {
            console.warn(`Attributs manquants ou invalides pour une option : data-motif-id=${optionId}, data-motif-prix=${price}`);
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
            document.querySelectorAll('.color-options .option img').forEach(opt => {
                opt.parentElement.classList.remove('selected'); // Retirer la classe CSS
            });

            // Supprimer les options de cette étape dans le stockage global
            allSelectedOptions = allSelectedOptions.filter(opt => !opt.id.startsWith(`${currentStep}_`));

            // Ajouter l'option actuellement sélectionnée
            allSelectedOptions.push({ id: uniqueId, price: price });
            selectedMotifOptions = [{ id: uniqueId, price: price }]; // Mise à jour spécifique à cette étape
            option.parentElement.classList.add('selected'); // Ajouter la classe CSS

            console.log(`Option sélectionnée : ID Unique = ${uniqueId}, Prix = ${price}`);

            // Sauvegarder les données globales dans localStorage
            localStorage.setItem("allSelectedOptions", JSON.stringify(allSelectedOptions));
            localStorage.setItem("selectedMotifOptions", JSON.stringify(selectedMotifOptions));

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
        const openButton = document.querySelector('.btn-aide'); // Bouton pour ouvrir le popup
        const popup = document.getElementById('help-popup');
        const closeButton = document.querySelector('.close-btn'); // Bouton "Merci !" pour fermer
        closeButton.addEventListener('click', () => {
            popup.style.display = 'none';
        });

        // Fermer le popup si clic à l'extérieur
        window.addEventListener('click', (event) => {
            if (event.target === popup) {
                popup.style.display = 'none';
            }
        });

        // Sélectionner les éléments nécessaires
        const options = document.querySelectorAll('.color-options .option img'); // Les images des options
        const selectedMotifTissuInput = document.getElementById('selected-motif_tissu');
        const mainImage = document.querySelector('.main-display img'); 
        const suivantButton = document.querySelector('.btn-suivant'); // Le bouton "Suivant"
        const selectionPopup = document.getElementById('selection-popup'); // Pop-up de sélection
        let selected = false; // Marque si une option a été sélectionnée

        // Vérification si une sélection existe dans localStorage
        let savedMotifTissuId = localStorage.getItem('selectedMotifTissuId');
        
        if (savedMotifTissuId) {
            options.forEach(img => {
                if (img.getAttribute('data-motif-id') === savedMotifTissuId) {
                    img.classList.add('selected');
                    mainImage.src = img.src;
                    mainImage.alt = img.alt;
                    selectedMotifTissuInput.value = savedMotifTissuId;
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
                selectedMotifTissuInput.value = img.getAttribute('data-motif-id');

                // Mise à jour de l'image principale
                mainImage.src = img.src;
                mainImage.alt = img.alt;

                selected = true;
                saveSelection(img.getAttribute('data-motif-id'));
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

        // Gestion du pop-up "Besoin d'aide"
        document.querySelector('.btn-aide').addEventListener('click', () => {
            popup.style.display = 'flex';
        });

        document.querySelector('.close-btn').addEventListener('click', () => {
            popup.style.display = 'none';
        });

        // Gestion du pop-up "Abandonner"
        document.querySelector('.btn-abandonner').addEventListener('click', () => {
            abandonnerPopup.style.display = 'flex';
        });

        document.querySelector('.no-btn').addEventListener('click', () => {
            abandonnerPopup.style.display = 'none';
        });

        document.querySelector('.yes-btn').addEventListener('click', () => {
            window.location.href = 'index.php'; // Redirection vers la page d'accueil
        });

        function saveSelection(motifTissuId) {
            localStorage.setItem('selectedMotifTissuId', motifTissuId);
        }
    });
</script>

</main>

<?php require_once '../../squelette/footer.php'?>

</body>
</html>

