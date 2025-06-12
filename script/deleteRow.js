//DELETE ROW
document.addEventListener('DOMContentLoaded', function () {
    const deleteLinks = document.querySelectorAll('.delete-action');
    const popup = document.getElementById('supprimer-popup');
    const yesButton = document.getElementById('confirm-delete');
    const noButton = document.getElementById('cancel-delete');

    let currentDeleteUrl = '';

    deleteLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            currentDeleteUrl = this.getAttribute('href');
            popup.style.display = 'flex';
        });
    });

    yesButton.addEventListener('click', function () {
        window.location.href = currentDeleteUrl;
    });

    // Ajouter un gestionnaire pour le bouton "Non"
    noButton.onclick = () => {
        popup.style.display = 'none'; // Ferme le popup
        currentDeleteUrl = '';
    }

    // Fermer le popup si clic à l'extérieur
    window.addEventListener('click', (event) => {
        if (event.target === popup) {
            popup.style.display = 'none';
            currentDeleteUrl = '';
        }
    });
});


