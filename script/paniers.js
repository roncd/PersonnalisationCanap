//Update statut pop up
window.updateStatus = function (button) {
    const commandDiv = button.closest('.commande');
    const commandId = commandDiv.getAttribute('data-id');
    const popup = document.getElementById('update-popup'); // Récupère le popup
    const currentStatut = commandDiv.getAttribute('data-statut'); // Récupère le statut actuel
    const yesButton = popup.querySelector('.btn-beige');
    const noButton = popup.querySelector('.btn-noir');

    // Afficher le popup
    popup.style.display = 'flex';

    // Ajouter un gestionnaire pour le bouton "Oui"
    yesButton.onclick = () => {
        // Déterminerle prochain statut dynamiquement
        let nextStatut = '';
        if (currentStatut === 'validation') {
            nextStatut = 'construction'; // Passe de Validation à Construction
        } else if (currentStatut === 'construction') {
            nextStatut = 'final'; // Passe de Construction à Final
        } else {
            console.error('Statut actuel non valide :', currentStatut);
            return; // Arrête l'exécution si le statut est invalide
        }
        // Envoyer une requête au serveur pour update statut de la commande
        fetch('../include/update_statut_panier.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: commandId, statut: nextStatut }),
        })
            .then(response => response.json())
            .then(data => {
                const messageContainer = document.getElementById('message-container');
                if (data.success) {
                    console.log('Statut mis à jour avec succès :', nextStatut);
                    sessionStorage.setItem('flashMessage', data.message);
                    sessionStorage.setItem('flashType', 'success');
                    location.reload();
                } else {
                    console.error('Erreur côté serveur :', data.error);
                    messageContainer.innerHTML = `<p class="message error">${data.message}</p>`;
                }
            })
            .catch(error => console.error('Erreur :', error));

        popup.style.display = 'none'; // Ferme le popup
    };

    // Ajouter un gestionnaire pour le bouton "Non"
    noButton.onclick = () => {
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

window.addEventListener('DOMContentLoaded', () => {
    const message = sessionStorage.getItem('flashMessage');
    const type = sessionStorage.getItem('flashType');

    if (message && type) {
        const messageContainer = document.getElementById('message-container');
        messageContainer.innerHTML = `<p class="message ${type}">${message}</p>`;
        sessionStorage.removeItem('flashMessage');
        sessionStorage.removeItem('flashType');
    }
});

//DELETE COMMANDE
window.removeCommand = function (button) {
    const commandDiv = button.closest('.commande'); // Récupère la commande liée
    const commandId = commandDiv.getAttribute('data-id'); // Récupère l'ID de la commande
    const popup = document.getElementById('supprimer-popup'); // Récupère le popup
    const yesButton = popup.querySelector('.btn-beige');
    const noButton = popup.querySelector('.btn-noir');

    // Afficher le popup
    popup.style.display = 'flex';

    // Ajouter un gestionnaire pour le bouton "Oui"
    yesButton.onclick = () => {
        // Envoyer une requête au serveur pour supprimer la commande
        fetch('../include/delete_panier.php', {
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
                try {
                    console.log('Données reçues :', data); // Vérifiez si c'est du JSON valide
                    const parsedData = JSON.parse(data); // Puis analysez le JSON
                    const messageContainer = document.getElementById('message-container');
                    if (parsedData.success) {
                        messageContainer.innerHTML = `<p class="message success">${parsedData.message}</p>`;
                        console.log('Commande supprimée avec succès.');
                        commandDiv.remove();
                    } else {
                        console.error('Erreur :', parsedData.error);
                        messageContainer.innerHTML = `<p class="message error">${parsedData.message}</p>`;
                    }
                } catch (err) {
                    console.error("Erreur lors du parsing JSON :", err);
                }
            })
            .catch(error => {
                console.error('Erreur lors de la suppression :', error);
            });


        popup.style.display = 'none'; // Ferme le popup
    };

    // Ajouter un gestionnaire pour le bouton "Non"
    noButton.onclick = () => {
        popup.style.display = 'none'; // Ferme le popup
    };

    // Fermer le popup si clic à l'extérieur
    window.addEventListener('click', (event) => {
        if (event.target === popup) {
            popup.style.display = 'none';
        }
    });
};
