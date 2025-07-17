//Pop up generer devis + envoi du devis par mail
document.getElementById('btn-oui').addEventListener('click', function () {
  let idCommande = this.getAttribute('data-id'); // Récupérer l'ID stocké    
  if (!idCommande) return;

    // Étape 1 : Transfert de commande tempo -> commande detail dans bdd
    fetch('/front/generate-pdf/transfer-tissu.php', {
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

          // Afficher le pop-up et stocker l’ID dans le bouton
          document.getElementById('pdf-popup').style.display = 'flex';
          document.getElementById('pdf-btn').setAttribute('data-id', newCommandeId);

          // Étape 2 : envoi du devis par mail avec le nouvel ID de commande detail
          fetch('/front/generate-pdf/send-pdf.php', {
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

// document.querySelector('.btn-suivant').addEventListener('click', function () {
//   let idCommande = this.getAttribute('data-id'); // Récupérer l'ID stocké    

//   fetch('/front/generate-pdf/transfer-tissu.php', {
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
document.getElementById('pdf-btn').addEventListener('click', function () {
  let newCommandeId = this.getAttribute('data-id'); // Récupérer l'ID stocké dans le bouton
  console.log("Nouvel ID de commande :", newCommandeId);

  if (newCommandeId) {
    fetch('/front/generate-pdf/generer-devis-tissu.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ id: newCommandeId })
    })
      .then(response => response.blob()) // Récupérer le PDF sous forme de blob
      .then(blob => {
        const url = window.URL.createObjectURL(blob);
        // Ouvrir un nouvel onglet avec l'URL Blob
        window.open(url, '_blank');
      })
      .catch(error => console.error('Erreur lors de la génération du PDF:', error));
  } else {
    console.error('Erreur : newCommandeId est indéfini');
  }
});


const popup = document.getElementById('pdf-popup');

// Fermer le popup si clic à l'extérieur
window.addEventListener('click', (event) => {
  if (event.target === popup) {
    popup.style.display = 'none';
  }
});

//Popup validation genreration
document.addEventListener('DOMContentLoaded', () => {
  const openButton = document.getElementById('btn-generer'); // Bouton pour ouvrir le popup
  const popup = document.getElementById('generer-popup');
  const yesButton = document.getElementById('btn-oui'); // Bouton "Oui..." pour redirection
  const noButton = document.getElementById('btn-close'); // Bouton "Non !" pour fermer le popup

  // Afficher le popup
  openButton.addEventListener('click', () => {
    popup.style.display = 'flex';
  });

  //Cache le pop up 
  yesButton.addEventListener('click', () => {
    popup.style.display = 'none';
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