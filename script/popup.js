  //Pop up aide
  document.addEventListener('DOMContentLoaded', () => {
    const openButton = document.querySelector('.btn-aide'); // Bouton pour ouvrir le popup
    const popup = document.getElementById('help-popup');
    const closeButton = document.querySelector('.close-btn'); // Bouton "Merci !" pour fermer le popup
  
    // Afficher le popup
    openButton.addEventListener('click', () => {
      console.log('Bouton Aide cliqué');
      popup.style.display = 'flex';
    });
  
    // Masquer le popup avec le bouton "Merci !"
    closeButton.addEventListener('click', () => {
      console.log('Popup fermé via le bouton');
      popup.style.display = 'none';
    });
  
    // Fermer le popup si clic à l'extérieur
    window.addEventListener('click', (event) => {
      if (event.target === popup) {
        console.log('Clic à l\'extérieur du popup');
        popup.style.display = 'none';
      }
    });
  });
  
  //Popup abandonner
  document.addEventListener('DOMContentLoaded', () => {
    const openButton = document.querySelector('.btn-abandonner'); // Bouton pour ouvrir le popup
    const popup = document.getElementById('abandonner-popup');
    const yesButton = document.querySelector('.yes-btn'); // Bouton "Oui ..." pour redirection
    const noButton = document.querySelector('.no-btn'); // Bouton "Non !" pour fermer le popup

    
    document.querySelectorAll('.transition').forEach(element => {
        element.classList.add('show');
      });
  
    // Afficher le popup
    openButton.addEventListener('click', () => {
      console.log('Bouton Abandonner cliqué');
      popup.style.display = 'flex';
    });
  
    // Rediriger vers la page d'accueil avec le bouton "Oui ..."
    yesButton.addEventListener('click', () => {
      console.log('Redirection vers la page d\'accueil');
      window.location.href = '../pages/';
    });
  
    // Masquer le popup avec le bouton "Non !"
    noButton.addEventListener('click', () => {
      console.log('Popup fermé via le bouton');
      popup.style.display = 'none';
    });
  
    // Fermer le popup si clic à l'extérieur
    window.addEventListener('click', (event) => {
      if (event.target === popup) {
        console.log('Clic à l\'extérieur du popup');
        popup.style.display = 'none';
      }
    });
  });