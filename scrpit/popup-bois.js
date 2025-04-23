//Pop up generer devis + envoi du devis par mail
document.querySelectorAll('.btn-suivant').forEach(button => {
  button.addEventListener('click', function () {
    let idCommande = this.getAttribute('data-id'); // Récupérer l'ID stocké    
    if (!idCommande) return;

    // Étape 1 : Transfert de commande tempo -> commande detail dans bdd
    fetch('../generate-pdf/transfer-bois.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: idCommande })
    })
      .then(response => response.json())
      .then(data => {
        console.log(data); // Vérifie ce que renvoie PHP

        if (data.success && data.new_id) {
          let newCommandeId = data.new_id;
          console.log("Nouvel ID de commande :", newCommandeId);

          // Affiche le pop-up et stocke l’ID dans le bouton
          document.getElementById('pdf-popup').style.display = 'flex';
          document.querySelector('.pdf-btn').setAttribute('data-id', newCommandeId);

          // Étape 2 : envoi du devis par mail avec le nouvel ID de commande detail
          fetch('../generate-pdf/send-pdf.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: newCommandeId })
          })
            .then(response => response.text())
            .then(data => {
              console.log('Réponse brute du serveur :', data);
            })
        } else {
          console.error('Erreur : newCommandeId est invalide ou non défini.');
        }
      })
      .catch(error => console.error('Erreur:', error));
  });
});

// document.querySelector('.btn-suivant').addEventListener('click', function () {
//   let idCommande = this.getAttribute('data-id'); // Récupérer l'ID stocké    

//   fetch('../generate-pdf/transfer-bois.php', {
//     method: 'POST',
//     headers: { 'Content-Type': 'application/json' },
//     body: JSON.stringify({ id: idCommande })
//   })
//     .then(response => response.json())
//     .then(data => {
//       console.log(data); // Vérifie ce que renvoie PHP

//       if (data.success && data.new_id) {
//         let newCommandeId = data.new_id;
//         console.log("Nouvel ID de commande :", newCommandeId);

//         // Afficher le pop-up et stocker l’ID dans le bouton
//         document.getElementById('pdf-popup').style.display = 'flex';
//         document.querySelector('.pdf-btn').setAttribute('data-id', newCommandeId);

//       } else {
//         console.error('Erreur : newCommandeId est invalide ou non défini.');
//       }
//     })
//     .catch(error => console.error('Erreur:', error));
// });

//Bouton pour voir pdf sur navigateur (lien temporaire)
document.querySelector('.pdf-btn').addEventListener('click', function () {
  let newCommandeId = this.getAttribute('data-id');
  console.log("Nouvel ID de commande :", newCommandeId);

  if (newCommandeId) {
    fetch('../generate-pdf/generer-devis-bois.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ id: newCommandeId })
    })
      .then(response => response.blob())
      .then(blob => {
        const url = window.URL.createObjectURL(blob);
        // ouvre un nouvel onglet avec l'URL temporaire
        window.open(url, '_blank');
      })
      .catch(error => console.error('Erreur lors de la génération du PDF:', error));
  } else {
    console.error('Erreur : newCommandeId est indéfini');
  }
});

const closeBtn = document.querySelector('.close-btn');
const popup = document.getElementById('pdf-popup');

// Masquer le popup avec le bouton "Non !"
closeBtn.addEventListener('click', () => {
  popup.style.display = 'none';
});

// Fermer le popup si clic à l'extérieur
window.addEventListener('click', (event) => {
  if (event.target === popup) {
    popup.style.display = 'none';
  }
});


//Pop up aide
document.addEventListener('DOMContentLoaded', () => {
  const openButton = document.querySelector('.btn-aide'); // Bouton pour ouvrir le popup
  const popup = document.getElementById('help-popup');
  const closeButton = document.querySelector('.thank-btn'); // Bouton "Merci !" pour fermer le popup

  // Afficher le popup
  openButton.addEventListener('click', () => {
    popup.style.display = 'flex';
  });

  // Masquer le popup avec le bouton "Merci !"
  closeButton.addEventListener('click', () => {
    popup.style.display = 'none';
  });

  // Fermer le popup si clic à l'extérieur
  window.addEventListener('click', (event) => {
    if (event.target === popup) {
      popup.style.display = 'none';
    }
  });
});

//Pop up abandonner
document.addEventListener('DOMContentLoaded', () => {
  const openButton = document.querySelector('.btn-abandonner'); // Bouton pour ouvrir le popup
  const popup = document.getElementById('abandonner-popup');
  const yesButton = document.querySelector('.yes-btn'); // Bouton "Oui ..." pour redirection
  const noButton = document.querySelector('.no-btn'); // Bouton "Non !" pour fermer le popup

  // Afficher le popup
  openButton.addEventListener('click', () => {
    popup.style.display = 'flex';
  });

  // Rediriger vers la page d'accueil avec le bouton "Oui ..."
  yesButton.addEventListener('click', () => {
    window.location.href = '../pages/';
  });

  // Masquer le popup avec le bouton "Non !"
  noButton.addEventListener('click', () => {
    popup.style.display = 'none';
  });

  // Fermer le popup si clic à l'extérieur
  window.addEventListener('click', (event) => {
    if (event.target === popup) {
      popup.style.display = 'none';
    }
  });
});
