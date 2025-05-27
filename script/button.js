//Button retour
document.addEventListener('DOMContentLoaded', () => {
    // Sélection des boutons
    const retourButton = document.getElementById('btn-retour');

    // Action du bouton "Retour" : rediriger vers la page précédente
    retourButton.addEventListener('click', () => {
      window.history.back(); // Navigue vers la page précédente
    });

  });