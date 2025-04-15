document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Retirer la classe active des onglets et du contenu
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Ajouter la classe active au clic
            tab.classList.add('active');
            document.getElementById(tab.dataset.tab).classList.add('active');
        });
    });
});


//Back-office
window.updateStatus = function (button) {
    const commandDiv = button.closest('.commande');
    const commandId = commandDiv.getAttribute('data-id');
    const currentStatut = commandDiv.getAttribute('data-statut'); // Récupérer le statut actuel

    // Déterminer le prochain statut dynamiquement
    let nextStatut = '';
    if (currentStatut === 'validation') {
        nextStatut = 'construction'; // Passer de Validation à Construction
    } else if (currentStatut === 'construction') {
        nextStatut = 'final'; // Passer de Construction à Final
    } else {
        console.error('Statut actuel non valide :', currentStatut);
        return; // Arrêter l'exécution si le statut est invalide
    }

    // Envoyer une requête pour mettre à jour le statut
    fetch('update_statut.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: commandId, statut: nextStatut }),
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Statut mis à jour avec succès :', nextStatut);
                location.reload(); // Recharger la page pour afficher les changements
            } else {
                console.error('Erreur côté serveur :', data.error);
            }
        })
        .catch(error => console.error('Erreur :', error));
};


window.removeCommand = function (button) {
    const commandDiv = button.closest('.commande'); // Récupère la commande liée
    const commandId = commandDiv.getAttribute('data-id'); // Récupère l'ID de la commande
    const popup = document.getElementById('supprimer-popup'); // Récupère le popup
    const yesButton = popup.querySelector('.yes-btn'); // Bouton "Oui" dans le popup
    const noButton = popup.querySelector('.no-btn'); // Bouton "Non" dans le popup

    // Afficher le popup
    popup.style.display = 'flex';

    // Ajouter un gestionnaire pour le bouton "Oui"
    yesButton.onclick = () => {
        console.log('Suppression confirmée de la commande.');
        // Envoyer une requête au serveur pour supprimer la commande
        fetch('delete_commande.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: commandId }),
        })
            .then(response => {
                return response.text(); // Utilisez text() pour voir le contenu brut
            })
            .then(data => {
                console.log('Données reçues :', data); // Vérifiez si c'est du JSON valide
                const parsedData = JSON.parse(data); // Puis analysez le JSON
                if (parsedData.success) {
                    console.log('Commande supprimée avec succès.');
                    commandDiv.remove();
                } else {
                    console.error('Erreur :', parsedData.error);
                }
            })
            .catch(error => {
                console.error('Erreur lors de la suppression :', error);
            });


        popup.style.display = 'none'; // Ferme le popup
    };

    // Ajouter un gestionnaire pour le bouton "Non"
    noButton.onclick = () => {
        console.log('Suppression annulée.');
        popup.style.display = 'none'; // Ferme le popup
    };

    // Fermer le popup si clic à l'extérieur
    window.addEventListener('click', (event) => {
        if (event.target === popup) {
            console.log('Clic à l\'extérieur du popup, fermeture.');
            popup.style.display = 'none';
        }
    });
};
