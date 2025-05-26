//Pop up aide
document.addEventListener('DOMContentLoaded', () => {
  const openButton = document.querySelector('.btn-aide'); // Bouton pour ouvrir le popup
  const popup = document.getElementById('help-popup');
  const closeButton = popup.querySelector('.close-btn'); // Bouton "Merci !" pour fermer le popup

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

//Transition affichage des pages
document.querySelectorAll('.transition').forEach(element => {
  element.classList.add('show');
});

//Popup abandonner
document.addEventListener('DOMContentLoaded', () => {
  const btnAbandonner = document.querySelector('.btn-abandonner');
  const popup = document.getElementById('abandonner-popup');
  const btnNo = document.querySelector('.no-btn');
  const btnYes = document.querySelector('.yes-btn');

  if (btnAbandonner && popup && btnNo && btnYes) {
    btnAbandonner.addEventListener('click', () => {
      popup.style.display = 'flex';
      console.log('Bouton Abandonner cliqué');
    });

    btnNo.addEventListener('click', () => {
      console.log('Popup fermé via le bouton');
      popup.style.display = 'none';
    });

    window.addEventListener('click', (event) => {
      if (event.target === popup) {
        console.log('Clic à l\'extérieur du popup');
        popup.style.display = 'none';
      }
    });

    btnYes.addEventListener('click', () => {
      fetch('abandonner_commande.php', {
        method: 'POST'
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            sessionStorage.clear();
            localStorage.clear();
            window.location.href = '../pages';
          } else {
            alert("Erreur : " + data.message);
          }
        })
        .catch(error => {
          console.error('Erreur lors de l’abandon :', error);
        });
    });
  }
});